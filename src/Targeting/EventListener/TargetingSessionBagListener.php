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

use OpenDxp;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Service\TargetingEnableService;
use OpenDxp\Config;
use OpenDxp\Event\Cache\FullPage\IgnoredSessionKeysEvent;
use OpenDxp\Event\Cache\FullPage\PrepareResponseEvent;
use OpenDxp\Event\FullPageCacheEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TargetingSessionBagListener implements EventSubscriberInterface
{
    const TARGETING_BAG_SESSION = 'opendxp_targeting_session';

    const TARGETING_BAG_VISITOR = 'opendxp_targeting_visitor';

    public function __construct(protected Config $config, private readonly TargetingEnableService $targetingEnableService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FullPageCacheEvents::IGNORED_SESSION_KEYS => 'configureIgnoredSessionKeys',
            FullPageCacheEvents::PREPARE_RESPONSE => 'prepareFullPageCacheResponse',
            // add session support by registering the session configurator and session storage
            KernelEvents::REQUEST => ['onKernelRequest', 127],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->targetingEnableService->isTargetingEnabled()) {
            return;
        }

        if (!$this->isEnabled()) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        if ($event->getRequest()->attributes->get('_stateless', false)) {
            return;
        }

        $session = $event->getRequest()->getSession();

        //do not register bags, if session is already started
        if ($session->isStarted()) {
            return;
        }

        $this->configure($session);
    }

    public function configure(SessionInterface $session): void
    {
        $sessionBag = new AttributeBag('_' . self::TARGETING_BAG_SESSION);
        $sessionBag->setName(self::TARGETING_BAG_SESSION);

        $visitorBag = new AttributeBag('_' . self::TARGETING_BAG_VISITOR);
        $visitorBag->setName(self::TARGETING_BAG_VISITOR);

        $session->registerBag($sessionBag);
        $session->registerBag($visitorBag);
    }

    public function configureIgnoredSessionKeys(IgnoredSessionKeysEvent $event): void
    {
        if (!$this->targetingEnableService->isTargetingEnabled()) {
            return;
        }

        if (!$this->isEnabled()) {
            return;
        }

        // configures full page cache to ignore session data in targeting storage
        $event->setKeys(array_merge($event->getKeys(), [
            '_' . self::TARGETING_BAG_SESSION,
            '_' . self::TARGETING_BAG_VISITOR,
        ]));
    }

    /**
     * Removes session cookie from cached response
     */
    public function prepareFullPageCacheResponse(PrepareResponseEvent $event): void
    {
        if (!$this->targetingEnableService->isTargetingEnabled()) {
            return;
        }

        if (!$this->isEnabled()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$request->hasSession()) {
            return;
        }

        $sessionName = $request->getSession()->getName();
        if (empty($sessionName)) {
            return;
        }

        $cookies = $response->headers->getCookies();

        foreach ($cookies as $cookie) {
            if ($cookie->getName() === $sessionName) {
                $response->headers->removeCookie(
                    $cookie->getName(),
                    $cookie->getPath(),
                    $cookie->getDomain()
                );
            }
        }
    }

    protected function isEnabled(): bool
    {
        return OpenDxp::getKernel()->getContainer()->getParameter('opendxp_personalization.targeting.session.enabled');
    }
}
