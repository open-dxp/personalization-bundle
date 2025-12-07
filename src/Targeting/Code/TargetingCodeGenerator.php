<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Code;

use OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingCodeEvent;
use OpenDxp\Bundle\PersonalizationBundle\Event\TargetingEvents;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class TargetingCodeGenerator
{
    const BLOCK_BEFORE_SCRIPT_TAG = 'beforeScriptTag';

    const BLOCK_BEFORE_SCRIPT = 'beforeScript';

    const BLOCK_AFTER_SCRIPT = 'afterScript';

    const BLOCK_AFTER_SCRIPT_TAG = 'afterScriptTag';

    private EventDispatcherInterface $eventDispatcher;

    private Environment $twig;

    private array $blocks = [
        self::BLOCK_BEFORE_SCRIPT_TAG,
        self::BLOCK_BEFORE_SCRIPT,
        self::BLOCK_AFTER_SCRIPT,
        self::BLOCK_AFTER_SCRIPT_TAG,
    ];

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Environment $templatingEngine
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $templatingEngine;
    }

    public function generateCode(VisitorInfo $visitorInfo): string
    {
        $data = [
            'inDebugMode' => \OpenDxp::inDebugMode(),
            'dataProviderKeys' => $visitorInfo->getFrontendDataProviders(),
        ];

        $event = new TargetingCodeEvent(
            '@OpenDxpPersonalization/Targeting/targetingCode.html.twig',
            $this->buildCodeBlocks(),
            $data
        );

        $this->eventDispatcher->dispatch($event, TargetingEvents::TARGETING_CODE);

        return $this->renderTemplate($event);
    }

    private function renderTemplate(TargetingCodeEvent $event): string
    {
        $data = $event->getData();
        $data['blocks'] = $event->getBlocks();

        $code = $this->twig->render(
            $event->getTemplate(),
            $data
        );

        $code = trim($code);

        return $code;
    }

    private function buildCodeBlocks(): array
    {
        $blocks = [];
        foreach ($this->blocks as $block) {
            $blocks[$block] = new CodeBlock();
        }

        return $blocks;
    }
}
