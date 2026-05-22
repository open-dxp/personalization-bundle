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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\ConditionMatcher;

class ExpressionBuilder
{
    private array $parts = [];

    private array $values = [];

    private int $valueIndex = 1;

    public function getExpression(): string
    {
        return implode('', $this->parts);
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function addCondition(array $config, bool $result): void
    {
        if (!empty($this->parts)) {
            $this->parts[] = $this->normalizeOperator($config['operator']);
        }

        if ($config['bracketLeft']) {
            $this->parts[] = ' (';
        }

        $valueKey = $config['type'] . '_' . $this->valueIndex++;

        $this->values[$valueKey] = $result;
        $this->parts[] = $valueKey;

        if ($config['bracketRight']) {
            $this->parts[] = ') ';
        }
    }

    private function normalizeOperator(?string $operator = null): string
    {
        if (empty($operator)) {
            $operator = 'and';
        }

        $mapping = [
            'and_not' => 'and not',
        ];

        if (isset($mapping[$operator])) {
            $operator = $mapping[$operator];
        }

        $operator = sprintf(' %s ', $operator);

        return $operator;
    }
}
