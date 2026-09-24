<?php

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\PersonalizationBundle\Tests\Unit\Targeting\EventListener;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Document\DocumentTargetingConfigurator;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\EventListener\Frontend\TargetingElementListener;
use OpenDxp\Http\Request\Resolver\DocumentResolver;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use OpenDxp\Model\Document;
use OpenDxp\Tests\Support\Test\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TargetingElementListenerTest extends TestCase
{
    public function testDocumentDoesNotChangeLocaleOnKernelController(): void
    {
        $document = $this->createMock(Document\Page::class);
        $document->method('getProperty')->with('language')->willReturn('de');

        $contextResolver = $this->createMock(OpenDxpContextResolver::class);
        $contextResolver->method('matchesOpenDxpContext')->willReturn(true);

        $request = Request::create('/de/product/it');

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $documentResolver = new DocumentResolver($requestStack);
        $documentResolver->setDocument($request, $document);

        // what Symfony's LocaleListener does for a route with _locale = it
        $request->setLocale('it');

        $listener = new TargetingElementListener(
            $documentResolver,
            $this->createMock(DocumentTargetingConfigurator::class)
        );
        $listener->setOpenDxpContextResolver($contextResolver);

        $listener->onKernelController(new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            static fn () => new Response(),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        ));

        $this->assertSame($document, $documentResolver->getDocument($request));
        $this->assertSame('it', $request->getLocale());
    }
}
