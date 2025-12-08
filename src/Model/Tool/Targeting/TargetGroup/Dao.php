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

namespace OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Model;
use OpenDxp\Tool\Serialize;

/**
 * @internal
 *
 * @property TargetGroup $model
 */
class Dao extends Model\Dao\AbstractDao
{
    /**
     *
     * @throws Model\Exception\NotFoundException
     */
    public function getById(?int $id = null): void
    {
        if (null !== $id) {
            $this->model->setId($id);
        }

        $data = $this->db->fetchAssociative('SELECT * FROM targeting_target_groups WHERE id = ?', [$this->model->getId()]);

        if (!empty($data['id'])) {
            $data['actions'] = (isset($data['actions']) ? Serialize::unserialize($data['actions']) : []);

            $this->assignVariablesToModel($data);
        } else {
            throw new Model\Exception\NotFoundException('Target Group with id ' . $this->model->getId() . " doesn't exist");
        }
    }

    /**
     *
     * @throws Model\Exception\NotFoundException
     */
    public function getByName(?string $name = null): void
    {
        if (null !== $name) {
            $this->model->setName($name);
        }

        $data = $this->db->fetchAllAssociative('SELECT id FROM targeting_target_groups WHERE name = ?', [$this->model->getName()]);

        if (count($data) === 1) {
            $this->getById($data[0]['id']);
        } else {
            throw new Model\Exception\NotFoundException(sprintf(
                'Targeting group with name "%s" does not exist or is not unique.',
                $this->model->getName()
            ));
        }
    }

    public function save(): void
    {
        if (!$this->model->getId()) {
            $this->create();
        }

        $this->update();
    }

    public function delete(): void
    {
        $this->db->delete('targeting_target_groups', ['id' => $this->model->getId()]);
    }

    public function update(): void
    {
        $type = $this->model->getObjectVars();
        $data = [];

        foreach ($type as $key => $value) {
            if (in_array($key, $this->getValidTableColumns('targeting_target_groups'))) {
                if (is_array($value) || is_object($value)) {
                    $value = Serialize::serialize($value);
                }

                if (is_bool($value)) {
                    $value = (int)$value;
                }

                $data[$key] = $value;
            }
        }

        $this->db->update('targeting_target_groups', $data, ['id' => $this->model->getId()]);
    }

    public function create(): void
    {
        $this->db->insert('targeting_target_groups', []);
        $this->model->setId((int) $this->db->lastInsertId());
    }
}
