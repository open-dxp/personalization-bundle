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

abstract class AbstractVariableCondition implements ConditionInterface, VariableConditionInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $variables = [];

    public function getMatchedVariables(): array
    {
        return $this->variables;
    }

    final protected function setMatchedVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    final protected function setMatchedVariable(string $key, mixed $value): void
    {
        $this->variables[$key] = $value;
    }
}
