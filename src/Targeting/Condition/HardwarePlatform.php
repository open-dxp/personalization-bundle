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

class HardwarePlatform extends AbstractVariableCondition implements DataProviderDependentInterface
{
    /**
     * Mapping from admin UI values to DeviceDetector results. If value
     * is an array, in_array is used to determine match.
     */
    protected static array $deviceMapping = [
        'smartphone' => 'mobile',
        'phablet' => 'mobile',
        'feature phone' => 'mobile',
    ];

    public function __construct(private readonly ?string $platform = null)
    {
    }

    public static function fromConfig(array $config): static
    {
        return new static($config['platform'] ?? null);
    }

    public function getDataProviderKeys(): array
    {
        return [Device::PROVIDER_KEY];
    }

    public function canMatch(): bool
    {
        return !empty($this->platform);
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        $device = $visitorInfo->get(Device::PROVIDER_KEY);

        if (!$device || true === ($device['is_bot'] ?? false)) {
            return false;
        }

        $deviceInfo = $device['device'] ?? null;
        if (!$deviceInfo) {
            return false;
        }

        $platform = $deviceInfo['type'] ?? null;
        if (!empty($platform) && isset(static::$deviceMapping[$platform])) {
            $platform = static::$deviceMapping[$platform];
        }

        if ($this->matchesPlatform($platform)) {
            $this->setMatchedVariable('platform', $platform);

            return true;
        }

        return false;
    }

    private function matchesPlatform(?string $platform = null): bool
    {
        if (empty($platform)) {
            return false;
        }

        if ('all' === $this->platform) {
            return true;
        }

        return $platform === $this->platform;
    }
}
