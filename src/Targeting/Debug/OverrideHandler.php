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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\OverrideHandlerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class OverrideHandler
{
    /**
     * @param OverrideHandlerInterface[] $overrideHandlers
     */
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly array $overrideHandlers
    ) {
    }

    public function getForm(Request $request): FormInterface
    {
        if ($request->attributes->has('opendxp_targeting_override_form')) {
            /** @var FormInterface $form */
            $form = $request->attributes->get('opendxp_targeting_override_form');

            return $form;
        }

        $form = $this->buildForm($request);

        $request->attributes->set('opendxp_targeting_override_form', $form);

        return $form;
    }

    protected function buildForm(Request $request): FormInterface
    {
        $formBuilder = $this->formFactory->createNamedBuilder('_ptg_overrides', FormType::class, null, [
            'csrf_protection' => false,
        ]);

        $formBuilder->setMethod('GET');

        foreach ($this->overrideHandlers as $handler) {
            $handler->buildOverrideForm($formBuilder, $request);
        }

        return $formBuilder->getForm();
    }

    public function handleRequest(Request $request): void
    {
        $form = $this->getForm($request);

        $this->handleForm($form, $request);
    }

    public function handleForm(FormInterface $form, Request $request): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!empty($data)) {
                foreach ($this->overrideHandlers as $handler) {
                    $handler->overrideFromRequest($data, $request);
                }
            }
        }
    }
}
