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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\EventListener;

use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Targeting\TargetingDocumentInterface;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Event\DocumentEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Handles target groups configured on the document settings panel. If a document
 * has configured target groups, the assign_target_group will be manually called
 * for that target group before starting to match other conditions.
 */
class RenderletListener implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DocumentEvents::EDITABLE_RENDERLET_PRE_RENDER => 'configureElementTargeting',
        ];
    }

    public function configureElementTargeting(GenericEvent $event): void
    {
        $requestParams = $event->getArgument('requestParams');
        $element = $event->getArgument('element');
        if (!$element instanceof TargetingDocumentInterface) {
            return;
        }

        // set selected target group on element
        if ($requestParams['_ptg'] ?? false) {
            $targetGroup = TargetGroup::getById((int)$requestParams['_ptg']);
            if ($targetGroup) {
                $element->setUseTargetGroup($targetGroup->getId());
            }
        }
    }
}
