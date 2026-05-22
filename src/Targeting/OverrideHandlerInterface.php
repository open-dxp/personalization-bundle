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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for override handlers which can influence the debug toolbar form and override
 * targeting data based on form results.
 */
interface OverrideHandlerInterface
{
    const REQUEST_ATTRIBUTE = 'opendxp_targeting_overrides';

    /**
     * Add fields to the targeting toolbar override form
     */
    public function buildOverrideForm(FormBuilderInterface $form, Request $request): void;

    /**
     * Override targeting data from the override data as gathered from the form
     */
    public function overrideFromRequest(array $overrides, Request $request): void;
}
