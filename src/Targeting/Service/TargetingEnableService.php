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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Service;

use OpenDxp\Http\RequestHelper;

class TargetingEnableService
{
    private RequestHelper $requestHelper;

    private bool $enabled;

    public function __construct(RequestHelper $requestHelper, bool $enabled)
    {
        $this->enabled = $enabled;
        $this->requestHelper = $requestHelper;
    }

    public function isTargetingEnabled(): bool
    {
        $request = $this->requestHelper->getCurrentRequest();

        if ($this->enabled || $request->cookies->getBoolean('opendxp_targeting_enabled')) {
            return true;
        }

        return false;
    }
}
