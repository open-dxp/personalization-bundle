<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PersonalizationBundle\Tests\Unit\Targeting;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Document\DocumentTargetingConfigurator;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\EventListener\Frontend\TargetingElementListener;
use OpenDxp\Http\Request\Resolver\DocumentResolver;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use OpenDxp\Model\Document\Page;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

it('keeps the locale a route sets over the language of the document', function () {
    $document = $this->createStub(Page::class);
    $document
        ->method('getProperty')
        ->willReturnMap([['language', 'de']]);

    $request = Request::create('/de/product/it');
    $requestStack = new RequestStack();
    $requestStack->push($request);
    $documentResolver = new DocumentResolver($requestStack);
    $documentResolver->setDocument($request, $document);

    $request->setLocale('it');

    $contextResolver = $this->createStub(OpenDxpContextResolver::class);
    $contextResolver
        ->method('matchesOpenDxpContext')
        ->willReturn(true);

    $listener = new TargetingElementListener(
        $documentResolver,
        $this->createStub(DocumentTargetingConfigurator::class),
    );
    $listener->setOpenDxpContextResolver($contextResolver);

    $listener->onKernelController(new ControllerEvent(
        $this->createStub(HttpKernelInterface::class),
        static fn (): Response => new Response(),
        $request,
        HttpKernelInterface::MAIN_REQUEST,
    ));

    expect($documentResolver->getDocument($request))
        ->toBe($document)
        ->and($request->getLocale())
        ->toBe('it');
});
