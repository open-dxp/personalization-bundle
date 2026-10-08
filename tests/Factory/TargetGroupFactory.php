<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PersonalizationBundle\Tests\Factory;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Test\Factory\AbstractSavingFactory;

/**
 * @extends AbstractSavingFactory<TargetGroup>
 */
final class TargetGroupFactory extends AbstractSavingFactory
{
    public static function class(): string
    {
        return TargetGroup::class;
    }

    protected function defaults(): array
    {
        return [
            'name' => self::faker()->unique()->word(),
        ];
    }
}
