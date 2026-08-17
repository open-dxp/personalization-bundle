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

use  OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Bundle\PersonalizationBundle\Security\PersonalizationPermission;
use OpenDxp\Cache\Core\CoreCacheHandler;
use OpenDxp\Controller\Traits\JsonHelperTrait;
use OpenDxp\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @internal
 */
#[Route('/targeting')]
class TargetingController extends UserAwareController
{
    // RULES
    use JsonHelperTrait;

    private function correctName(string $name): string
    {
        return preg_replace('/[#?*:\\\\<>|"%&@=;+]/', '-', $name);
    }

    #[Route('/rule/list', name: 'opendxp_bundle_personalization_targeting_rulelist', methods: ['GET'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function ruleListAction(Request $request): JsonResponse
    {
        $targets = [];

        $list = new Targeting\Rule\Listing();
        $list->setOrderKey('prio');
        $list->setOrder('ASC');

        foreach ($list->load() as $target) {
            $targets[] = [
                'id' => $target->getId(),
                'text' => htmlspecialchars($target->getName()),
                'active' => $target->getActive(),
                'qtip' => 'ID: ' . $target->getId(),
            ];
        }

        return $this->jsonResponse($targets);
    }

    #[Route('/rule/add', name: 'opendxp_bundle_personalization_targeting_ruleadd', methods: ['POST'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function ruleAddAction(Request $request): JsonResponse
    {
        $target = new Targeting\Rule();
        $target->setName($this->correctName($request->get('name')));
        $target->save();

        return $this->jsonResponse(['success' => true, 'id' => $target->getId()]);
    }

    #[Route('/rule/delete', name: 'opendxp_bundle_personalization_targeting_ruledelete', methods: ['DELETE'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function ruleDeleteAction(Request $request): JsonResponse
    {
        $success = false;

        $target = Targeting\Rule::getById((int) $request->get('id'));
        if ($target) {
            $target->delete();
            $success = true;
        }

        return $this->jsonResponse(['success' => $success]);
    }

    #[Route('/rule/get', name: 'opendxp_bundle_personalization_targeting_ruleget', methods: ['GET'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function ruleGetAction(Request $request): JsonResponse
    {
        $target = Targeting\Rule::getById((int) $request->get('id'));
        if (!$target) {
            throw $this->createNotFoundException();
        }
        $target = $target->getObjectVars();

        return $this->jsonResponse($target);
    }

    #[Route('/rule/save', name: 'opendxp_bundle_personalization_targeting_rulesave', methods: ['PUT'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function ruleSaveAction(Request $request): JsonResponse
    {
        $data = $this->decodeJson($request->get('data'));

        $target = Targeting\Rule::getById((int) $request->get('id'));
        if (!$target) {
            throw $this->createNotFoundException();
        }
        $target->setValues($data['settings']);
        $target->setName($this->correctName($target->getName()));
        $target->setConditions($data['conditions']);
        $target->setActions($data['actions']);
        $target->save();

        return $this->jsonResponse(['success' => true]);
    }

    #[Route('/rule/order', name: 'opendxp_bundle_personalization_targeting_ruleorder', methods: ['POST'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function ruleOrderAction(Request $request): JsonResponse
    {
        $return = [
            'success' => false,
            'message' => '',
        ];

        $rules = $this->decodeJson($request->get('rules'));

        /** @var Targeting\Rule[] $changedRules */
        $changedRules = [];
        foreach ($rules as $id => $prio) {
            $rule = Targeting\Rule::getById((int)$id);
            $prio = (int)$prio;

            if ($rule) {
                if ($rule->getPrio() !== $prio) {
                    $rule->setPrio((int)$prio);
                    $changedRules[] = $rule;
                }
            } else {
                $return['message'] = sprintf('Rule %d was not found', (int)$id);

                return $this->jsonResponse($return, 400);
            }
        }

        // save only changed rules
        foreach ($changedRules as $changedRule) {
            $changedRule->save();
        }

        $return['success'] = true;

        return $this->jsonResponse($return);
    }

    // TARGET GROUPS

    #[Route('/target-group/list', name: 'opendxp_bundle_personalization_targeting_targetgrouplist', methods: ['GET'])]
    public function targetGroupListAction(Request $request): JsonResponse
    {
        $targetGroups = [];

        $list = new TargetGroup\Listing();

        if ($request->get('add-default')) {
            $targetGroups[] = [
                'id' => 0,
                'text' => 'default',
                'active' => true,
                'qtip' => 0,
            ];
        }

        foreach ($list->load() as $targetGroup) {
            $targetGroups[] = [
                'id' => $targetGroup->getId(),
                'text' => htmlspecialchars($targetGroup->getName()),
                'active' => $targetGroup->getActive(),
                'qtip' => $targetGroup->getId(),
            ];
        }

        return $this->jsonResponse($targetGroups);
    }

    #[Route('/target-group/add', name: 'opendxp_bundle_personalization_targeting_targetgroupadd', methods: ['POST'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function targetGroupAddAction(Request $request, CoreCacheHandler $cache): JsonResponse
    {
        $targetGroup = new TargetGroup();
        $targetGroup->setName($this->correctName($request->get('name')));
        $targetGroup->save();

        $cache->clearTag('target_groups');

        return $this->jsonResponse(['success' => true, 'id' => $targetGroup->getId()]);
    }

    #[Route('/target-group/delete', name: 'opendxp_bundle_personalization_targeting_targetgroupdelete', methods: ['DELETE'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function targetGroupDeleteAction(Request $request, CoreCacheHandler $cache): JsonResponse
    {
        $success = false;

        $targetGroup = TargetGroup::getById((int) $request->get('id'));
        if ($targetGroup) {
            $targetGroup->delete();
            $success = true;
        }

        $cache->clearTag('target_groups');

        return $this->jsonResponse(['success' => $success]);
    }

    #[Route('/target-group/get', name: 'opendxp_bundle_personalization_targeting_targetgroupget', methods: ['GET'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function targetGroupGetAction(Request $request): JsonResponse
    {
        $targetGroup = TargetGroup::getById((int) $request->get('id'));
        if (!$targetGroup) {
            throw $this->createNotFoundException();
        }
        $targetGroup = $targetGroup->getObjectVars();

        return $this->jsonResponse($targetGroup);
    }

    #[Route('/target-group/save', name: 'opendxp_bundle_personalization_targeting_targetgroupsave', methods: ['PUT'])]
    #[IsGranted(PersonalizationPermission::Targeting->value)]
    public function targetGroupSaveAction(Request $request, CoreCacheHandler $cache): JsonResponse
    {
        $data = $this->decodeJson($request->get('data'));

        $targetGroup = TargetGroup::getById((int) $request->get('id'));
        if (!$targetGroup) {
            throw $this->createNotFoundException();
        }
        $targetGroup->setValues($data['settings']);
        $targetGroup->setName($this->correctName($targetGroup->getName()));
        $targetGroup->save();

        $cache->clearTag('target_groups');

        return $this->jsonResponse(['success' => true]);
    }
}
