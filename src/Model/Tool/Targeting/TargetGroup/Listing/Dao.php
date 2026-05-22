<?php

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

namespace OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup\Listing;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Model;

/**
 * @internal
 *
 * @property TargetGroup\Listing $model
 */
class Dao extends Model\Listing\Dao\AbstractDao
{
    /**
     * @return TargetGroup[]
     */
    public function load(): array
    {
        $ids = $this->db->fetchFirstColumn('SELECT id FROM targeting_target_groups' . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $targetGroups = [];
        foreach ($ids as $id) {
            $targetGroups[] = TargetGroup::getById($id);
        }

        $this->model->setTargetGroups($targetGroups);

        return $targetGroups;
    }

    public function getTotalCount(): int
    {
        try {
            return (int) $this->db->fetchOne('SELECT COUNT(*) FROM targeting_target_groups ' . $this->getCondition(), $this->model->getConditionVariables());
        } catch (\Exception) {
            return 0;
        }
    }
}
