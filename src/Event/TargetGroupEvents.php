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

namespace OpenDxp\Bundle\PersonalizationBundle\Event;

final class TargetGroupEvents
{
    /**
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Model\TargetGroupEvent")
     */
    const string POST_ADD = 'opendxp.targetgroup.postAdd';

    /**
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Model\TargetGroupEvent")
     */
    const string POST_UPDATE = 'opendxp.targetgroup.postUpdate';

    /**
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Model\TargetGroupEvent")
     */
    const string POST_DELETE = 'opendxp.targetgroup.postDelete';
}
