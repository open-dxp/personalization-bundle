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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class Url extends AbstractVariableCondition implements ConditionInterface
{
    public function __construct(private readonly ?string $pattern = null)
    {
    }

    public static function fromConfig(array $config): static
    {
        return new static($config['url'] ?? null);
    }

    public function canMatch(): bool
    {
        return !empty($this->pattern);
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        $request = $visitorInfo->getRequest();

        $uri = $request->getUri();
        $result = preg_match($this->pattern, $uri);

        if ($result) {
            $this->setMatchedVariable('uri', $uri);

            return true;
        }

        return false;
    }
}
