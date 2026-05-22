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

use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider\Device;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class Browser extends AbstractVariableCondition implements DataProviderDependentInterface
{
    public function __construct(private ?string $browser = null)
    {
    }

    public static function fromConfig(array $config): static
    {
        return new static($config['browser'] ?? null);
    }

    public function getDataProviderKeys(): array
    {
        return [Device::PROVIDER_KEY];
    }

    public function canMatch(): bool
    {
        return !empty($this->browser);
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        $device = $visitorInfo->get(Device::PROVIDER_KEY);

        if (!$device || true === ($device['is_bot'] ?? false)) {
            return false;
        }

        $client = $device['client'] ?? null;
        if (!$client) {
            return false;
        }

        $type = $client['type'] ?? null;
        $name = $client['name'] ?? null;

        if ($this->browser === 'ie') {
            $this->browser = 'Internet Explorer';
        }

        if ('browser' === $type && strtolower($name ?? '') === strtolower((string) $this->browser)) {
            $this->setMatchedVariables([
                'type' => $type,
                'name' => $name,
            ]);

            return true;
        }

        return false;
    }
}
