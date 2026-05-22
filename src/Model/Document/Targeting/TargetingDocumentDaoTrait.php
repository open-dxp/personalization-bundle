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

namespace OpenDxp\Bundle\PersonalizationBundle\Model\Document\Targeting;

use OpenDxp\Model\Document\PageSnippet;

/**
 * @internal
 */
trait TargetingDocumentDaoTrait
{
    public function hasTargetGroupSpecificEditables(): bool
    {
        /** @var PageSnippet\Dao $this */
        $count = $this->db->fetchOne(
            'SELECT count(*) FROM documents_editables WHERE documentId = ? AND name LIKE ?',
            [
                $this->model->getId(),
                '%' . TargetingDocumentInterface::TARGET_GROUP_EDITABLE_PREFIX . '%' . TargetingDocumentInterface::TARGET_GROUP_EDITABLE_SUFFIX . '%',
            ]
        );

        return $count > 0;
    }

    public function getTargetGroupSpecificEditableNames(): array
    {
        /** @var PageSnippet\Dao $this */
        $names = $this->db->fetchFirstColumn(
            'SELECT name FROM documents_editables WHERE documentId = ? AND name LIKE ?',
            [
                $this->model->getId(),
                '%' . TargetingDocumentInterface::TARGET_GROUP_EDITABLE_PREFIX . '%' . TargetingDocumentInterface::TARGET_GROUP_EDITABLE_SUFFIX . '%',
            ]
        );

        return $names;
    }
}
