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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class VisitorInfoStorage implements VisitorInfoStorageInterface
{
    private ?VisitorInfo $visitorInfo = null;

    public function getVisitorInfo(): VisitorInfo
    {
        return $this->visitorInfo;
    }

    public function setVisitorInfo(VisitorInfo $visitorInfo): void
    {
        $this->visitorInfo = $visitorInfo;
    }

    public function hasVisitorInfo(): bool
    {
        return null !== $this->visitorInfo;
    }
}
