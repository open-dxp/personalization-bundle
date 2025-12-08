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

namespace OpenDxp\Bundle\PersonalizationBundle\DataCollector;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\TargetingDataCollector;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\VisitorInfoStorageInterface;
use OpenDxp\Http\Request\Resolver\DocumentResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
class OpenDxpTargetingDataCollector extends DataCollector implements ResetInterface
{
    public function __construct(
        private VisitorInfoStorageInterface $visitorInfoStorage,
        private DocumentResolver $documentResolver,
        private TargetingDataCollector $targetingDataCollector
    ) {
    }

    public function getName(): string
    {
        return 'opendxp_targeting';
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        $this->data = [];

        if (!$this->visitorInfoStorage->hasVisitorInfo()) {
            return;
        }

        $document = $this->documentResolver->getDocument($request);
        $visitorInfo = $this->visitorInfoStorage->getVisitorInfo();
        $tdc = $this->targetingDataCollector;

        $data = [
            'visitor_info' => $tdc->collectVisitorInfo($visitorInfo),
            'storage' => $tdc->collectStorage($visitorInfo),
            'rules' => $tdc->collectMatchedRules($visitorInfo),
            'target_groups' => $tdc->collectTargetGroups($visitorInfo),
            'document_target_group' => $tdc->collectDocumentTargetGroup($document),
            'document_target_groups' => $tdc->collectDocumentTargetGroupMapping(),
        ];

        $this->data = $this->cloneVar($data);
    }

    public function reset(): void
    {
        $this->data = [];
    }

    public function getVisitorInfo(): array|Data
    {
        return $this->data['visitor_info'];
    }

    public function getStorage(): array|Data
    {
        return $this->data['storage'];
    }

    public function getRules(): array|Data
    {
        return $this->data['rules'];
    }

    public function getTargetGroups(): array|Data
    {
        return $this->data['target_groups'];
    }

    public function getDocumentTargetGroup(): null|array|Data
    {
        return $this->data['document_target_group'];
    }

    public function getDocumentTargetGroups(): array|Data
    {
        return $this->data['document_target_groups'];
    }

    public function hasData(): bool
    {
        return !empty($this->data);
    }
}
