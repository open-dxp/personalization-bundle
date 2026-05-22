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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\EventListener\Frontend;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Document\DocumentTargetingConfigurator;
use OpenDxp\Event\DocumentEvents;
use OpenDxp\Event\Model\DocumentEvent;
use OpenDxp\Http\Request\Resolver\DocumentResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles block state for sub requests (saves parent state and restores it after request completes)
 *
 * @internal
 */
class TargetingDocumentRendererListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly DocumentTargetingConfigurator $targetingConfigurator,
        protected DocumentResolver $documentResolver
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DocumentEvents::RENDERER_PRE_RENDER => 'onPreRender',
            DocumentEvents::INCLUDERENDERER_PRE_RENDER => 'onPreRender',
        ];
    }

    public function onPreRender(DocumentEvent $event): void
    {
        $document = $event->getDocument();
        $this->targetingConfigurator->configureTargetGroup($document);
    }
}
