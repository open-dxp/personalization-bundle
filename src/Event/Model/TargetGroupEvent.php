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

namespace OpenDxp\Bundle\PersonalizationBundle\Event\Model;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use Symfony\Contracts\EventDispatcher\Event;

class TargetGroupEvent extends Event
{
    /**
     * TargetGroupEvent constructor.
     */
    public function __construct(
        protected TargetGroup $targetGroup,
        protected array $arguments = []
    ) {
    }

    public function getTargetGroup(): TargetGroup
    {
        return $this->targetGroup;
    }

    public function setTargetGroup(TargetGroup $targetGroup): void
    {
        $this->targetGroup = $targetGroup;
    }

    public function getElement(): TargetGroup
    {
        return $this->getTargetGroup();
    }
}
