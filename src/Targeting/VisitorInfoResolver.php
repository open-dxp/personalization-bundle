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
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting;

use Doctrine\DBAL\Connection;
use Exception;
use OpenDxp\Bundle\PersonalizationBundle\Debug\Traits\StopwatchTrait;
use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingResolveVisitorInfoEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingRuleEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\TargetingEvents;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\Rule;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\ActionHandler\ActionHandlerInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Storage\TargetingStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class VisitorInfoResolver
{
    use StopwatchTrait;

    public const string ATTRIBUTE_VISITOR_INFO = '_visitor_info';

    public const string STORAGE_KEY_RULE_CONDITION_VARIABLES = 'vi:var';

    public const string STORAGE_KEY_MATCHED_SESSION_RULES = 'vi:sru'; // visitorInfo:sessionRules

    public const string STORAGE_KEY_MATCHED_VISITOR_RULES = 'vi:vru';

    /**
     * @var Rule[]|null
     */
    private ?array $targetingRules = null;

    private ?bool $targetingConfigured = null;

    public function __construct(
        private TargetingStorageInterface $targetingStorage,
        private VisitorInfoStorageInterface $visitorInfoStorage,
        private ConditionMatcherInterface $conditionMatcher,
        private ActionHandlerInterface $actionHandler,
        private Connection $db,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function resolve(Request $request): VisitorInfo
    {
        if ($this->visitorInfoStorage->hasVisitorInfo()) {
            return $this->visitorInfoStorage->getVisitorInfo();
        }

        $visitorInfo = VisitorInfo::fromRequest($request);

        if (!$this->isTargetingConfigured()) {
            return $visitorInfo;
        }

        $event = new TargetingResolveVisitorInfoEvent($visitorInfo);

        $this->eventDispatcher->dispatch($event, TargetingEvents::PRE_RESOLVE);

        $visitorInfo = $event->getVisitorInfo();

        $this->matchTargetingRuleConditions($visitorInfo);

        $this->eventDispatcher->dispatch(new TargetingEvent($visitorInfo), TargetingEvents::POST_RESOLVE);

        $this->visitorInfoStorage->setVisitorInfo($visitorInfo);

        return $visitorInfo;
    }

    public function isTargetingConfigured(): bool
    {
        if (null !== $this->targetingConfigured) {
            return $this->targetingConfigured;
        }

        try {
            $configuredRules = $this->db->fetchOne('SELECT id FROM targeting_target_groups UNION SELECT id FROM targeting_rules LIMIT 1');
        } catch (Exception) {
            return false;
        }

        $this->targetingConfigured = $configuredRules && (int)$configuredRules > 0;

        return $this->targetingConfigured;
    }

    private function matchTargetingRuleConditions(VisitorInfo $visitorInfo): void
    {
        $rules = $this->getTargetingRules();

        foreach ($rules as $rule) {
            if (Rule::SCOPE_SESSION === $rule->getScope()) {
                if ($this->ruleWasMatchedInSession($visitorInfo, $rule)) {
                    continue;
                }
            } elseif (Rule::SCOPE_VISITOR === $rule->getScope()) {
                if ($this->ruleWasMatchedForVisitor($visitorInfo, $rule)) {
                    continue;
                }
            }

            $this->matchTargetingRuleCondition($visitorInfo, $rule);
        }
    }

    private function matchTargetingRuleCondition(VisitorInfo $visitorInfo, Rule $rule): void
    {
        $scopeWithVariables = Rule::SCOPE_SESSION_WITH_VARIABLES === $rule->getScope();

        $this->startStopwatch('Targeting:match:' . $rule->getName(), 'targeting');

        $match = $this->conditionMatcher->match(
            $visitorInfo,
            $rule->getConditions(),
            $scopeWithVariables
        );

        $this->stopStopwatch('Targeting:match:' . $rule->getName());

        if (!$match) {
            return;
        }

        if ($scopeWithVariables) {
            $collectedVariables = $this->conditionMatcher->getCollectedVariables();

            // match only once with the same variables
            if ($this->ruleWasMatchedInSessionWithVariables($visitorInfo, $rule, $collectedVariables)) {
                return;
            }
        }

        if (Rule::SCOPE_SESSION === $rule->getScope()) {
            // record the rule as matched for the current session
            $this->markRuleAsMatchedInSession($visitorInfo, $rule);
        } elseif (Rule::SCOPE_VISITOR === $rule->getScope()) {
            // record the rule as matched for the visitor
            $this->markRuleAsMatchedForVisitor($visitorInfo, $rule);
        }

        // store info about matched rule
        $visitorInfo->addMatchingTargetingRule($rule);

        $this->eventDispatcher->dispatch(new TargetingRuleEvent($visitorInfo, $rule), TargetingEvents::PRE_RULE_ACTIONS);

        // execute rule actions
        $this->handleTargetingRuleActions($visitorInfo, $rule);

        $this->eventDispatcher->dispatch(new TargetingRuleEvent($visitorInfo, $rule), TargetingEvents::POST_RULE_ACTIONS);
    }

    private function handleTargetingRuleActions(VisitorInfo $visitorInfo, Rule $rule): void
    {
        foreach ($rule->getActions() as $action) {
            if (!is_array($action)) {
                continue;
            }

            $this->actionHandler->apply($visitorInfo, $action, $rule);
        }
    }

    /**
     * @return Rule[]
     */
    private function getTargetingRules(): array
    {
        if (null !== $this->targetingRules) {
            return $this->targetingRules;
        }

        $list = new Rule\Listing();
        $list->setCondition('active = 1');
        $list->setOrderKey('prio');
        $list->setOrder('ASC');

        $this->targetingRules = $list->load();

        return $this->targetingRules;
    }

    private function ruleWasMatchedInSession(VisitorInfo $visitorInfo, Rule $rule): bool
    {
        return $this->ruleWasMatched(
            $visitorInfo, $rule,
            TargetingStorageInterface::SCOPE_SESSION, self::STORAGE_KEY_MATCHED_SESSION_RULES
        );
    }

    private function markRuleAsMatchedInSession(VisitorInfo $visitorInfo, Rule $rule): void
    {
        $this->markRuleAsMatched(
            $visitorInfo, $rule,
            TargetingStorageInterface::SCOPE_SESSION, self::STORAGE_KEY_MATCHED_SESSION_RULES
        );
    }

    private function ruleWasMatchedForVisitor(VisitorInfo $visitorInfo, Rule $rule): bool
    {
        return $this->ruleWasMatched(
            $visitorInfo, $rule,
            TargetingStorageInterface::SCOPE_VISITOR, self::STORAGE_KEY_MATCHED_VISITOR_RULES
        );
    }

    private function markRuleAsMatchedForVisitor(VisitorInfo $visitorInfo, Rule $rule): void
    {
        $this->markRuleAsMatched(
            $visitorInfo, $rule,
            TargetingStorageInterface::SCOPE_VISITOR, self::STORAGE_KEY_MATCHED_VISITOR_RULES
        );
    }

    private function ruleWasMatched(VisitorInfo $visitorInfo, Rule $rule, string $scope, string $storageKey): bool
    {
        $matchedRules = $this->targetingStorage->get($visitorInfo, $scope, $storageKey, []);

        return in_array($rule->getId(), $matchedRules);
    }

    private function markRuleAsMatched(VisitorInfo $visitorInfo, Rule $rule, string $scope, string $storageKey): void
    {
        $matchedRules = $this->targetingStorage->get($visitorInfo, $scope, $storageKey, []);

        if (!in_array($rule->getId(), $matchedRules)) {
            $matchedRules[] = $rule->getId();
        }

        $this->targetingStorage->set($visitorInfo, $scope, $storageKey, $matchedRules);
    }

    private function ruleWasMatchedInSessionWithVariables(VisitorInfo $visitorInfo, Rule $rule, array $variables): bool
    {
        $hash = sha1(serialize($variables));

        $storedVariables = $this->targetingStorage->get(
            $visitorInfo,
            TargetingStorageInterface::SCOPE_SESSION,
            self::STORAGE_KEY_RULE_CONDITION_VARIABLES,
            []
        );

        // hash was already matched
        if (isset($storedVariables[$rule->getId()]) && $storedVariables[$rule->getId()] === $hash) {
            return true;
        }

        // store hash to storage
        $storedVariables[$rule->getId()] = $hash;

        $this->targetingStorage->set(
            $visitorInfo,
            TargetingStorageInterface::SCOPE_SESSION,
            self::STORAGE_KEY_RULE_CONDITION_VARIABLES,
            $storedVariables
        );

        return false;
    }
}
