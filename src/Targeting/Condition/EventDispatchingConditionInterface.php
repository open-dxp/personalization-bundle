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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface EventDispatchingConditionInterface
{
    /**
     * Executed before condition is matched
     */
    public function preMatch(VisitorInfo $visitorInfo, EventDispatcherInterface $eventDispatcher): void;

    /**
     * Executed after condition is matched
     */
    public function postMatch(VisitorInfo $visitorInfo, EventDispatcherInterface $eventDispatcher): void;
}
