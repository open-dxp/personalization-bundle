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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\EventListener;

use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\TargetingEvents;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Service\VisitedPagesCounter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VisitedPagesCountListener implements EventSubscriberInterface
{
    private bool $recordPageCount = false;

    public function __construct(private readonly VisitedPagesCounter $visitedPagesCounter)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TargetingEvents::VISITED_PAGES_COUNT_MATCH => 'onVisitedPagesCountMatch', // triggered from conditions depending on page count
            TargetingEvents::POST_RESOLVE => 'onPostResolveVisitorInfo',
        ];
    }

    public function onVisitedPagesCountMatch(): void
    {
        // increment page count after matching proceeded
        $this->recordPageCount = true;
    }

    public function onPostResolveVisitorInfo(TargetingEvent $event): void
    {
        // TODO currently the pages count is only recorded if there's a condition depending on
        // the count. This is good for minimizing storage data and writes, but implies that the
        // page count is not recorded if there's no rule with a condition depending on the page
        // count. Alternatively this could be done blindly after resolving the visitor info, but
        // that would trigger a write/increment on every request without actually needing the data.
        if (!$this->recordPageCount) {
            return;
        }

        $this->visitedPagesCounter->increment($event->getVisitorInfo());
    }
}
