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

namespace OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Model;

/**
 * @internal
 *
 * @method Listing\Dao getDao()
 * @method TargetGroup[] load()
 * @method TargetGroup|false current()
 */
class Listing extends Model\Listing\AbstractListing
{
    /**
     * @param TargetGroup[] $targetGroups
     *
     * @return $this
     */
    public function setTargetGroups(array $targetGroups): static
    {
        return $this->setData($targetGroups);
    }

    /**
     * @return TargetGroup[]
     */
    public function getTargetGroups(): array
    {
        return $this->getData();
    }
}
