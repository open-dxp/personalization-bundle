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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\Util;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\OverrideHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

class OverrideAttributeResolver
{
    public static function setOverrideValue(Request $request, string $key, mixed $value): void
    {
        $overrides = $request->attributes->get(OverrideHandlerInterface::REQUEST_ATTRIBUTE, []);
        $overrides[$key] = $value;

        $request->attributes->set(OverrideHandlerInterface::REQUEST_ATTRIBUTE, $overrides);
    }

    public static function getOverrideValue(Request $request, string $key, mixed $default = null): mixed
    {
        $overrides = $request->attributes->get(OverrideHandlerInterface::REQUEST_ATTRIBUTE, []);

        return $overrides[$key] ?? $default;
    }
}
