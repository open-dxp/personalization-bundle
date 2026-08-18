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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\Util\OverrideAttributeResolver;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\GeoLocation as GeoLocationModel;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Loads geolocation (only coordinates and optional altitude) from either
 * browser geolocation delivered as cookie or from geoip lookup as fallback.
 */
class GeoLocation implements DataProviderInterface
{
    const PROVIDER_KEY = 'geolocation';

    const COOKIE_NAME_GEOLOCATION = '_pc_tgl';

    public function __construct(
        private readonly GeoIp $geoIpDataProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function load(VisitorInfo $visitorInfo): void
    {
        $location = $this->loadLocation($visitorInfo);
        $location = $this->handleOverrides($visitorInfo->getRequest(), $location);

        $visitorInfo->set(
            self::PROVIDER_KEY,
            $location
        );
    }

    private function handleOverrides(Request $request, ?GeoLocationModel $location = null): ?GeoLocationModel
    {
        $overrides = OverrideAttributeResolver::getOverrideValue($request, 'location');
        if (empty($overrides)) {
            return $location;
        }

        $overrides = array_filter($overrides, fn ($key) => in_array($key, ['latitude', 'longitude', 'altitude']), ARRAY_FILTER_USE_KEY);

        $data = array_merge([
            'latitude' => $location ? $location->getLatitude() : null,
            'longitude' => $location ? $location->getLongitude() : null,
            'altitude' => $location ? $location->getAltitude() : null,
        ], $overrides);

        if (null !== $data['latitude'] && null !== $data['longitude']) {
            return GeoLocationModel::build(
                $data['latitude'],
                $data['longitude'],
                $data['altitude']
            );
        }

        return null;
    }

    private function loadLocation(VisitorInfo $visitorInfo): ?GeoLocationModel
    {
        $location = $this->loadGeolocationData($visitorInfo);
        if ($location) {
            return $location;
        }

        // no location found - try to load from GeoIP
        return $this->loadGeoIpData($visitorInfo);
    }

    private function loadGeolocationData(VisitorInfo $visitorInfo): ?GeoLocationModel
    {
        // inform frontend that geolocation is wanted - this will work after the first request
        $visitorInfo->addFrontendDataProvider(self::PROVIDER_KEY);

        $request = $visitorInfo->getRequest();

        if (!$request->cookies->has(self::COOKIE_NAME_GEOLOCATION)) {
            return null;
        }

        $cookie = $request->cookies->get(self::COOKIE_NAME_GEOLOCATION);
        if (empty($cookie)) {
            return null;
        }

        $json = json_decode($cookie, true, 2);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return null;
        }

        $floatFromJson = function (string $property) use ($json) {
            if (!isset($json[$property]) || empty($json[$property])) {
                return null;
            }

            if (!is_numeric($json[$property])) {
                return null;
            }

            return (float)$json[$property];
        };

        $latitude = $floatFromJson('lat');
        $longitude = $floatFromJson('long');
        $altitude = $floatFromJson('alt');

        if (null !== $latitude && null !== $longitude) {
            try {
                return new GeoLocationModel($latitude, $longitude, $altitude);
            } catch (Throwable $e) {
                $this->logger->error((string) $e);
            }
        }

        return null;
    }

    private function loadGeoIpData(VisitorInfo $visitorInfo): ?GeoLocationModel
    {
        $city = $this->geoIpDataProvider->loadData($visitorInfo);

        if (!$city || !$city['location']['latitude'] || !$city['location']['longitude']) {
            return null;
        }

        return new GeoLocationModel(
            (float)$city['location']['latitude'],
            (float)$city['location']['longitude']
        );
    }
}
