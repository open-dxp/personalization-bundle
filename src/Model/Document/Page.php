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

namespace OpenDxp\Bundle\PersonalizationBundle\Model\Document;

use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Targeting\TargetingDocumentInterface;
use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Traits\TargetDocumentTrait;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;

/**
 * @method \OpenDxp\Bundle\PersonalizationBundle\Model\Document\Page\Dao getDao()
 */
class Page extends \OpenDxp\Model\Document\Page implements TargetingDocumentInterface
{
    use TargetDocumentTrait;

    protected string $type = 'page';

    /**
     * Comma separated IDs of target groups
     *
     * @internal
     *
     */
    protected string $targetGroupIds = '';

    /**
     * Set linked Target Groups as set in properties panel as list of IDs
     *
     */
    public function setTargetGroupIds(array|string $targetGroupIds): void
    {
        if (is_array($targetGroupIds)) {
            $targetGroupIds = implode(',', $targetGroupIds);
        }

        $targetGroupIds = trim($targetGroupIds, ' ,');

        if (!empty($targetGroupIds)) {
            $targetGroupIds = ',' . $targetGroupIds . ',';
        }

        $this->targetGroupIds = $targetGroupIds;
    }

    /**
     * Get serialized list of Target Group IDs
     *
     */
    public function getTargetGroupIds(): string
    {
        return $this->targetGroupIds;
    }

    /**
     * Set assigned target groups
     *
     * @param TargetGroup[]|int[] $targetGroups
     */
    public function setTargetGroups(array $targetGroups): void
    {
        $ids = array_map(function ($targetGroup) {
            if (is_numeric($targetGroup)) {
                return (int)$targetGroup;
            } elseif ($targetGroup instanceof TargetGroup) {
                return $targetGroup->getId();
            }
        }, $targetGroups);

        $ids = array_filter($ids, fn($id) => null !== $id && $id > 0);

        $this->setTargetGroupIds($ids);
    }

    /**
     * Return list of assigned target groups (via properties panel)
     *
     * @return TargetGroup[]
     */
    public function getTargetGroups(): array
    {
        $ids = explode(',', $this->targetGroupIds);
        $targetGroups = array_map(static function ($id) {
            $id = trim($id);
            if (!empty($id)) {
                $targetGroup = TargetGroup::getById((int)$id);
                if ($targetGroup instanceof TargetGroup) {
                    return $targetGroup;
                }
            }

            return null;
        }, $ids);

        return array_filter($targetGroups, static fn (?TargetGroup $targetGroup) => $targetGroup !== null);
    }
}
