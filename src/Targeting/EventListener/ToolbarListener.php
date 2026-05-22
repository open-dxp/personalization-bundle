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

use OpenDxp\Bundle\CoreBundle\EventListener\Traits\OpenDxpContextAwareTrait;
use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\RenderToolbarEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\TargetingEvents;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\OverrideHandler;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\TargetingDataCollector;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Service\TargetingEnableService;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\VisitorInfoStorageInterface;
use OpenDxp\Http\Request\Resolver\DocumentResolver;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use OpenDxp\Http\Response\CodeInjector;
use OpenDxp\Model\Document;
use OpenDxp\Tool\Authentication;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class ToolbarListener implements EventSubscriberInterface
{
    use OpenDxpContextAwareTrait;

    public function __construct(
        private VisitorInfoStorageInterface $visitorInfoStorage,
        private DocumentResolver $documentResolver,
        private TargetingDataCollector $targetingDataCollector,
        private OverrideHandler $overrideHandler,
        private EventDispatcherInterface $eventDispatcher,
        private Environment $twig,
        private CodeInjector $codeInjector,
        private TargetingEnableService $targetingEnableService
    ) {
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TargetingEvents::PRE_RESOLVE => ['onPreResolve', -10],
            KernelEvents::RESPONSE => ['onKernelResponse', -127],
        ];
    }

    public function onPreResolve(TargetingEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->requestCanDebug($request)) {
            return;
        }

        // handle overrides from request data
        $this->overrideHandler->handleRequest($request);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->targetingEnableService->isTargetingEnabled()) {
            return;
        }
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->requestCanDebug($request)) {
            return;
        }

        // only inject toolbar if there's a visitor info
        if (!$this->visitorInfoStorage->hasVisitorInfo()) {
            return;
        }

        $document = $this->documentResolver->getDocument($request);
        $visitorInfo = $this->visitorInfoStorage->getVisitorInfo();
        $data = $this->collectTemplateData($visitorInfo, $document);

        $overrideForm = $this->overrideHandler->getForm($request);
        $data['overrideForm'] = $overrideForm->createView();

        $this->injectToolbar(
            $event->getResponse(),
            $data
        );
    }

    private function requestCanDebug(Request $request): bool
    {
        if ($request->attributes->has('opendxp_targeting_debug')) {
            return (bool)$request->attributes->get('opendxp_targeting_debug');
        }

        if (!$this->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_DEFAULT)) {
            return false;
        }

        // only inject toolbar for logged in admin users
        $adminUser = Authentication::authenticateSession($request);
        if (!$adminUser) {
            return false;
        }

        $cookieValue = (bool)$request->cookies->get('opendxp_targeting_debug');
        if (!$cookieValue) {
            return false;
        }

        $request->attributes->set('opendxp_targeting_debug', true);

        return true;
    }

    private function collectTemplateData(VisitorInfo $visitorInfo, ?Document $document = null): array
    {
        $token = substr(hash('sha256', uniqid((string)mt_rand(), true)), 0, 6);

        $tdc = $this->targetingDataCollector;

        $data = [
            'token' => $token,
            'visitorInfo' => $tdc->collectVisitorInfo($visitorInfo),
            'targetGroups' => $tdc->collectTargetGroups($visitorInfo),
            'rules' => $tdc->collectMatchedRules($visitorInfo),
            'documentTargetGroup' => $tdc->collectDocumentTargetGroup($document),
            'documentTargetGroups' => $tdc->collectDocumentTargetGroupMapping(),
            'storage' => $tdc->collectStorage($visitorInfo),
        ];

        return $data;
    }

    private function injectToolbar(Response $response, array $data): void
    {
        $event = new RenderToolbarEvent('@OpenDxpPersonalization/Targeting/toolbar/toolbar.html.twig', $data);

        $this->eventDispatcher->dispatch($event, TargetingEvents::RENDER_TOOLBAR);

        $code = $this->twig->render(
            $event->getTemplate(),
            $event->getData()
        );

        $this->codeInjector->inject(
            $response,
            $code,
            CodeInjector::SELECTOR_BODY,
            CodeInjector::POSITION_END
        );
    }
}
