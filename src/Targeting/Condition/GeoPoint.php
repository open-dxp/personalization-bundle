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

use Location\Coordinate;
use Location\Distance\Haversine;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider\GeoLocation;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\GeoLocation as GeoLocationModel;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class GeoPoint extends AbstractVariableCondition implements DataProviderDependentInterface
{
    public function __construct(
        private readonly ?float $latitude = null,
        private readonly ?float $longitude = null,
        private readonly ?int $radius = null
    ) {
    }

    public static function fromConfig(array $config): static
    {
        return new static(
            $config['latitude'] ? (float)$config['latitude'] : null,
            $config['longitude'] ? (float)$config['longitude'] : null,
            $config['radius'] ? (int)$config['radius'] : null
        );
    }

    public function getDataProviderKeys(): array
    {
        return [GeoLocation::PROVIDER_KEY];
    }

    public function canMatch(): bool
    {
        return !empty($this->latitude) && !empty($this->longitude) && !empty($this->radius);
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        /** @var GeoLocationModel|null $location */
        $location = $visitorInfo->get(GeoLocation::PROVIDER_KEY);

        if (!$location) {
            return false;
        }

        $distance = $this->calculateDistance(
            $this->latitude, $this->longitude,
            $location->getLatitude(), $location->getLongitude()
        );

        if ($distance < ($this->radius * 1000)) {
            $this->setMatchedVariables([
                'latitude' => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
            ]);

            return true;
        }

        return false;
    }

    private function calculateDistance(float $latA, float $longA, float $latB, float $longB): float
    {
        $coordA = new Coordinate($latA, $longA);
        $coordB = new Coordinate($latB, $longB);

        $calculator = new Haversine();
        $distance = $calculator->getDistance($coordA, $coordB);

        return $distance;
    }
}
