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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class TargetGroup extends AbstractVariableCondition implements ConditionInterface
{
    public function __construct(private readonly ?int $targetGroupId = null)
    {
    }

    public static function fromConfig(array $config): self
    {
        return new self($config['targetGroup'] ?? null);
    }

    public function canMatch(): bool
    {
        return null !== $this->targetGroupId && $this->targetGroupId > 0;
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        foreach ($visitorInfo->getAssignedTargetGroups() as $targetGroup) {
            if ($targetGroup->getId() === $this->targetGroupId) {
                $this->setMatchedVariable('target_group_id', $targetGroup->getId());

                return true;
            }
        }

        return false;
    }
}
