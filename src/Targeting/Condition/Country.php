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

use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider\GeoIp;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class Country extends AbstractVariableCondition implements DataProviderDependentInterface
{
    public function __construct(private readonly ?string $country = null)
    {
    }

    public static function fromConfig(array $config): static
    {
        return new static($config['country'] ?? null);
    }

    public function getDataProviderKeys(): array
    {
        return [GeoIp::PROVIDER_KEY];
    }

    public function canMatch(): bool
    {
        return !empty($this->country);
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        $city = $visitorInfo->get(GeoIp::PROVIDER_KEY);

        if (!$city || ! isset($city['country'])) {
            return false;
        }

        if ($city['country']['iso_code'] === $this->country) {
            $this->setMatchedVariable('iso_code', $city['country']['iso_code']);

            return true;
        }

        return false;
    }
}
