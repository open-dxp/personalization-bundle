<?php

declare(strict_types=1);

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.ch)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition\ConditionInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition\EventDispatchingConditionInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition\VariableConditionInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\ConditionMatcher\ExpressionBuilder;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ConditionMatcher implements ConditionMatcherInterface
{
    private array $collectedVariables = [];

    public function __construct(private readonly ConditionFactoryInterface $conditionFactory, private readonly DataLoaderInterface $dataLoader, private readonly EventDispatcherInterface $eventDispatcher, private readonly ExpressionLanguage $expressionLanguage, private readonly LoggerInterface $logger)
    {
    }

    public function match(VisitorInfo $visitorInfo, array $conditions, bool $collectVariables = false): bool
    {
        // reset internal state
        $this->collectedVariables = [];

        $count = count($conditions);
        if (0 === $count) {
            // no conditions -> rule matches
            return true;
        } elseif (1 === $count) {
            // no need to build up expression if there's only one condition
            return $this->matchCondition($visitorInfo, $conditions[0], $collectVariables);
        }

        $expressionBuilder = new ExpressionBuilder();

        foreach ($conditions as $conditionConfig) {
            $conditionResult = $this->matchCondition($visitorInfo, $conditionConfig, $collectVariables);

            $expressionBuilder->addCondition($conditionConfig, $conditionResult);
        }

        $expression = $expressionBuilder->getExpression();
        $values = $expressionBuilder->getValues();
        $result = $this->expressionLanguage->evaluate($expression, $values);

        return (bool)$result;
    }

    public function getCollectedVariables(): array
    {
        return $this->collectedVariables;
    }

    private function matchCondition(VisitorInfo $visitorInfo, array $config, bool $collectVariables = false): bool
    {
        try {
            $condition = $this->conditionFactory->build($config);
        } catch (\Throwable $e) {
            $this->logger->error((string) $e);

            return false;
        }

        // check prerequisites - e.g. a condition without a value
        // (= all values match) does not need to fetch provider data
        // as location or browser
        if (!$condition->canMatch()) {
            return false;
        }

        if ($condition instanceof DataProviderDependentInterface) {
            $this->dataLoader->loadDataFromProviders($visitorInfo, $condition->getDataProviderKeys());
        }

        if ($condition instanceof EventDispatchingConditionInterface) {
            $condition->preMatch($visitorInfo, $this->eventDispatcher);
        }

        try {
            $result = $condition->match($visitorInfo);
        } catch (\Throwable $e) {
            $this->logger->error((string) $e);

            return false;
        }

        if ($collectVariables) {
            $this->collectConditionVariables($config, $condition);
        }

        if ($condition instanceof EventDispatchingConditionInterface) {
            $condition->postMatch($visitorInfo, $this->eventDispatcher);
        }

        return $result;
    }

    private function collectConditionVariables(array $config, ConditionInterface $condition): void
    {
        $data = [
            'type' => $config['type'],
        ];

        if ($condition instanceof VariableConditionInterface) {
            $variables = $condition->getMatchedVariables();

            if (!empty($variables)) {
                $data['data'] = $variables;
            }
        }

        $this->collectedVariables[] = $data;
    }
}
