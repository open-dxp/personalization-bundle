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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\EventListener\Frontend;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Service\TargetingEnableService;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Storage\CookieStorage;
use OpenDxp\Event\Cache\FullPage\PrepareResponseEvent;
use OpenDxp\Event\FullPageCacheEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Removes cookie storage cookies from cached response (only from the response object, not
 * from the client's browser).
 */
class FullPageCacheCookieCleanupListener implements EventSubscriberInterface
{
    public function __construct(private readonly TargetingEnableService $targetingEnableService)
    {
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FullPageCacheEvents::PREPARE_RESPONSE => 'onPrepareFullPageCacheResponse',
        ];
    }

    public function onPrepareFullPageCacheResponse(PrepareResponseEvent $event): void
    {
        if (!$this->targetingEnableService->isTargetingEnabled()) {
            return;
        }
        $response = $event->getResponse();
        $cookies = $response->headers->getCookies();

        $blocklist = [
            CookieStorage::COOKIE_NAME_VISITOR,
            CookieStorage::COOKIE_NAME_SESSION,
        ];

        foreach ($cookies as $cookie) {
            if (in_array($cookie->getName(), $blocklist)) {
                $response->headers->removeCookie(
                    $cookie->getName(),
                    $cookie->getPath(),
                    $cookie->getDomain()
                );
            }
        }
    }
}
