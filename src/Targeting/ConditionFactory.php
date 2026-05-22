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

use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\BuildConditionEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\TargetingEvents;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition\ConditionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConditionFactory implements ConditionFactoryInterface
{
    /**
     * @var string[]
     */
    private array $blocklistedKeys = ['type', 'operator', 'bracketLeft', 'bracketRight'];

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        /**
         * @var string[]
         */
        private array $conditions
    )
    {
    }

    public function build(array $config): ConditionInterface
    {
        /** @var string|null $type */
        $type = $config['type'] ?? null;

        if (empty($type)) {
            throw new \InvalidArgumentException('Invalid condition: Type is not set');
        }

        if (!isset($this->conditions[$type])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid condition: Condition with type "%s" is not registered',
                $type
            ));
        }

        $typeConfig = array_filter($config, fn($v, $k) => !in_array($k, $this->blocklistedKeys), ARRAY_FILTER_USE_BOTH);

        $event = new BuildConditionEvent($type, $this->conditions[$type], $typeConfig);
        $this->eventDispatcher->dispatch($event, TargetingEvents::BUILD_CONDITION);

        if ($event->hasCondition()) {
            return $event->getCondition();
        }

        return $this->buildInstance($type, $typeConfig);
    }

    protected function buildInstance(string $type, array $config): ConditionInterface
    {
        $class = $this->conditions[$type];

        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf(
                'Configured condition class "%s" for type "%s" does not exist',
                $class,
                $type
            ));
        }

        if (!is_subclass_of($class, ConditionInterface::class)) {
            throw new \RuntimeException(sprintf(
                'Configured condition class "%s" for type "%s" has not the ConditionInterface',
                $class,
                $type
            ));
        }

        return $class::fromConfig($config);
    }
}
