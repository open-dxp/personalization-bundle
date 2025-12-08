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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\Override;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Document\DocumentTargetingConfigurator;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\OverrideHandlerInterface;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

class DocumentTargetingOverrideHandler implements OverrideHandlerInterface
{
    private DocumentTargetingConfigurator $documentTargetingConfigurator;

    public function __construct(DocumentTargetingConfigurator $documentTargetingConfigurator)
    {
        $this->documentTargetingConfigurator = $documentTargetingConfigurator;
    }

    public function buildOverrideForm(FormBuilderInterface $form, Request $request): void
    {
        $form->add('documentTargetGroup', ChoiceType::class, [
            'label' => 'Document Target Group',
            'required' => false,
            'choice_loader' => new CallbackChoiceLoader(function () {
                return (new TargetGroup\Listing())->load();
            }),
            'choice_value' => function (?TargetGroup $targetGroup = null) {
                return $targetGroup ? $targetGroup->getId() : '';
            },
            'choice_label' => function (?TargetGroup $targetGroup = null) {
                return $targetGroup ? $targetGroup->getName() : '';
            },
        ]);
    }

    public function overrideFromRequest(array $overrides, Request $request): void
    {
        $targetGroup = $overrides['documentTargetGroup'] ?? null;
        if ($targetGroup && $targetGroup instanceof TargetGroup) {
            $this->documentTargetingConfigurator->setOverrideTargetGroup($targetGroup);
        }
    }
}
