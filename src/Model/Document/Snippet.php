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

/**
 * @method \OpenDxp\Bundle\PersonalizationBundle\Model\Document\Snippet\Dao getDao()
 */
class Snippet extends \OpenDxp\Model\Document\Snippet implements TargetingDocumentInterface
{
    use TargetDocumentTrait;

    protected string $type = 'snippet';
}
