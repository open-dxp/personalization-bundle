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
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\PersonalizationBundle\Model\Document\Snippet;

use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Targeting\TargetingDocumentDaoInterface;
use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Targeting\TargetingDocumentDaoTrait;
use OpenDxp\Model;

/**
 * @internal
 *
 * @property \OpenDxp\Bundle\PersonalizationBundle\Model\Document\Snippet $model
 */
class Dao extends Model\Document\Snippet\Dao implements TargetingDocumentDaoInterface
{
    use TargetingDocumentDaoTrait;
}
