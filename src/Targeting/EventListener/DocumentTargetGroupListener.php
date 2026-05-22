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

use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\AssignDocumentTargetGroupEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\TargetingEvents;
use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Page;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\ActionHandler\ActionHandlerInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use OpenDxp\Bundle\StaticRoutesBundle\Model\Staticroute;
use OpenDxp\Http\Request\Resolver\DocumentResolver;
use OpenDxp\Model\Document;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Handles target groups configured on the document settings panel. If a document
 * has configured target groups, the assign_target_group will be manually called
 * for that target group before starting to match other conditions.
 */
class DocumentTargetGroupListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly DocumentResolver $documentResolver,
        private readonly ActionHandlerInterface $actionHandler,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TargetingEvents::PRE_RESOLVE => 'onVisitorInfoResolve',
        ];
    }

    public function onVisitorInfoResolve(TargetingEvent $event): void
    {
        $request = $event->getRequest();
        $document = $this->documentResolver->getDocument($request);

        if ($document) {
            $this->assignDocumentTargetGroups($document, $event->getVisitorInfo());
        }
    }

    private function assignDocumentTargetGroups(Document $document, VisitorInfo $visitorInfo): void
    {
        if (!$document instanceof Page) {
            return;
        }

        if (class_exists(Staticroute::class) && null !== Staticroute::getCurrentRoute()) {
            return;
        }

        // get target groups from document
        $targetGroups = $document->getTargetGroups();

        if (empty($targetGroups)) {
            return;
        }

        foreach ($targetGroups as $targetGroup) {
            $this->actionHandler->apply($visitorInfo, [
                'type' => 'assign_target_group',
                'targetGroup' => $targetGroup,
            ]);

            $this->eventDispatcher->dispatch(
                new AssignDocumentTargetGroupEvent($visitorInfo, $document, $targetGroup),
                TargetingEvents::ASSIGN_DOCUMENT_TARGET_GROUP
            );
        }
    }
}
