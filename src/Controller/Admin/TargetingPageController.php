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

namespace OpenDxp\Bundle\PersonalizationBundle\Controller\Admin;

use OpenDxp\Bundle\AdminBundle\Attribute\SessionIdentityAware;
use OpenDxp\Bundle\AdminBundle\Controller\AdminAbstractController;
use OpenDxp\Bundle\PersonalizationBundle\Handler\Document\ClearTargetingEditables\ClearTargetingEditablesHandler;
use OpenDxp\Bundle\PersonalizationBundle\Handler\Document\ClearTargetingEditables\ClearTargetingEditablesPayload;
use OpenDxp\Security\CorePermission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @internal
 */
#[IsGranted(CorePermission::Documents->value)]
#[Route('/targeting/page')]
class TargetingPageController extends AdminAbstractController
{
    #[Route('/clear-targeting-editable-data', name: 'opendxp_bundle_personalization_clear_targeting_page_editable_data', methods: ['PUT'])]
    #[SessionIdentityAware]
    public function clearTargetingEditableDataAction(
        ClearTargetingEditablesPayload $payload,
        ClearTargetingEditablesHandler $handler,
    ): JsonResponse {
        $handler($payload);

        return $this->apiOk();
    }
}
