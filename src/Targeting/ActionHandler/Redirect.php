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
use OpenDxp\Model\Document;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Redirect implements ActionHandlerInterface
{
    public function apply(VisitorInfo $visitorInfo, array $action, ?Rule $rule = null): void
    {
        $url = $action['url'] ?? null;
        if (!$url) {
            return;
        }

        $request = $visitorInfo->getRequest();

        // only redirect GET requests
        if ($request->getMethod() !== 'GET') {
            return;
        }

        // don't redirect multiple times to avoid loops
        if (!empty($request->get('_ptr'))) {
            return;
        }

        if (is_numeric($url)) {
            $document = Document::getById($url);
            if (!$document) {
                return;
            }

            $url = $document->getRealFullPath();
        }

        if ($rule) {
            $url = $this->addUrlParam($url, '_ptr', $rule->getId());
        } else {
            $url = $this->addUrlParam($url, '_ptr', 0);
        }

        $code = $action['code'] ?? RedirectResponse::HTTP_FOUND;

        $visitorInfo->setResponse(new RedirectResponse($url, $code));
    }

    private function addUrlParam(string $url, string $param, int $value): string
    {
        // add _ptr parameter
        if (str_contains($url, '?')) {
            $url .= '&';
        } else {
            $url .= '?';
        }

        $url .= sprintf('%s=%d', $param, $value);

        return $url;
    }
}
