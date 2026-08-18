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

namespace OpenDxp\Bundle\PersonalizationBundle\Handler\Document\ClearTargetingEditables;

use OpenDxp\Bundle\AdminBundle\Service\Element\ElementDraftService;
use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Targeting\TargetingDocumentInterface;
use OpenDxp\Model\Document;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @internal
 */
final class ClearTargetingEditablesHandler
{
    public function __construct(private readonly ElementDraftService $elementDraftService)
    {
    }

    public function __invoke(ClearTargetingEditablesPayload $payload): void
    {
        $document = Document\PageSnippet::getById($payload->id);

        if (!$document) {
            throw new NotFoundHttpException('Document not found');
        }

        if ($payload->targetGroupId && $document instanceof TargetingDocumentInterface) {
            $prefix = $document->getTargetGroupEditablePrefix($payload->targetGroupId);

            foreach ($document->getEditables() as $editable) {
                if (str_starts_with($editable->getName(), $prefix)) {
                    $document->removeEditable($editable->getName());
                }
            }
        }

        $this->elementDraftService->saveDocument($document, useForSave: true);
    }
}
