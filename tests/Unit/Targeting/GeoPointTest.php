<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PersonalizationBundle\Tests\Unit\Targeting;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition\GeoPoint;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider\GeoLocation;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\GeoLocation as GeoLocationModel;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Symfony\Component\HttpFoundation\Request;

const SALZBURG = [47.83610443106286, 13.062701225280762];
const MUNICH = [48.17546460000001, 11.551796999999965];
const BERLIN = [52.5219184, 13.413214700000026];
const BANGKOK = [13.75005680956885, 100.49125671386719];

function visitorAt(array $point): VisitorInfo
{
    $visitor = new VisitorInfo(new Request());
    $visitor->set(GeoLocation::PROVIDER_KEY, new GeoLocationModel(...$point));

    return $visitor;
}

it('matches a visitor within the radius around Salzburg', function (array $visitor, int $radius, bool $matches) {
    expect((new GeoPoint(...SALZBURG, radius: $radius))->match(visitorAt($visitor)))->toBe($matches);
})->with([
    'Munich, 118 km, outside 110 km' => [MUNICH, 110, false],
    'Munich, 118 km, inside 120 km' => [MUNICH, 120, true],
    'Berlin, 521 km, outside 500 km' => [BERLIN, 500, false],
    'Berlin, 521 km, inside 600 km' => [BERLIN, 600, true],
    'Bangkok, 8689 km, outside 100 km' => [BANGKOK, 100, false],
    'Bangkok, 8689 km, outside 8000 km' => [BANGKOK, 8000, false],
    'Bangkok, 8689 km, inside 9000 km' => [BANGKOK, 9000, true],
]);

it('cannot match without latitude, longitude and radius', function (?float $latitude, ?float $longitude, ?int $radius) {
    expect((new GeoPoint($latitude, $longitude, $radius))->canMatch())->toBeFalse();
})->with([
    'no radius' => [1.2, 2.3, null],
    'no longitude' => [1.2, null, 4],
    'no latitude' => [null, 2.3, 4],
    'only a latitude' => [1.2, null, null],
    'only a longitude' => [null, 2.3, null],
    'only a radius' => [null, null, 4],
    'nothing' => [null, null, null],
]);
