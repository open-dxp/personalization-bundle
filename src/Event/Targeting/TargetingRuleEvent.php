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

namespace OpenDxp\Bundle\PersonalizationBundle\Event\Targeting;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\Rule;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class TargetingRuleEvent extends TargetingEvent
{
    public function __construct(VisitorInfo $visitorInfo, private readonly Rule $rule)
    {
        parent::__construct($visitorInfo);
    }

    public function getRule(): Rule
    {
        return $this->rule;
    }
}
