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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\Override;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\Form\LocationType;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\Util\OverrideAttributeResolver;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\OverrideHandlerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

class LocationOverrideHandler implements OverrideHandlerInterface
{
    public function buildOverrideForm(FormBuilderInterface $form, Request $request): void
    {
        $form->add('location', LocationType::class, [
            'label' => 'Location',
            'required' => false,
            'attr' => [
                'class' => '_ptgtb__override-form__collapse-section',
            ],
        ]);
    }

    public function overrideFromRequest(array $overrides, Request $request): void
    {
        $location = $overrides['location'] ?? [];
        if (empty($location)) {
            return;
        }

        OverrideAttributeResolver::setOverrideValue($request, 'location', $location);
    }
}
