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

    private array $blocks = [
        self::BLOCK_BEFORE_SCRIPT_TAG,
        self::BLOCK_BEFORE_SCRIPT,
        self::BLOCK_AFTER_SCRIPT,
        self::BLOCK_AFTER_SCRIPT_TAG,
    ];

    public function __construct(private readonly EventDispatcherInterface $eventDispatcher, private readonly Environment $twig)
    {
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
