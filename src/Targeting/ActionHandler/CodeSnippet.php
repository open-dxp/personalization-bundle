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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\ActionHandler;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\Rule;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use OpenDxp\Http\Response\CodeInjector;
use Symfony\Component\HttpFoundation\Response;

class CodeSnippet implements ActionHandlerInterface, ResponseTransformingActionHandlerInterface
{
    private CodeInjector $codeInjector;

    public function __construct(CodeInjector $codeInjector)
    {
        $this->codeInjector = $codeInjector;
    }

    public function apply(VisitorInfo $visitorInfo, array $action, ?Rule $rule = null): void
    {
        $code = $action['code'] ?? '';
        $selector = $action['selector'] ?? '';
        $position = $action['position'] ?? '';

        if (empty($code) || empty($selector) || empty($position)) {
            return;
        }

        $visitorInfo->addAction([
            'type' => 'codesnippet',
            'scope' => VisitorInfo::ACTION_SCOPE_RESPONSE,
            'code' => $code,
            'selector' => $selector,
            'position' => $position,
        ]);
    }

    public function transformResponse(VisitorInfo $visitorInfo, Response $response, array $actions): void
    {
        foreach ($actions as $action) {
            $this->codeInjector->inject(
                $response,
                $action['code'],
                $action['selector'],
                $action['position']
            );
        }
    }
}
