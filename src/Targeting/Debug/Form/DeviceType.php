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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('hardwarePlatform', ChoiceType::class, [
            'label' => 'Hardware Platform',
            'required' => false,
            'choices' => [
                'Desktop' => 'desktop',
                'Tablet' => 'tablet',
                'Mobile' => 'mobile',
            ],
        ]);

        $builder->add('operatingSystem', ChoiceType::class, [
            'label' => 'Operating System',
            'required' => false,
            'choices' => [
                'Windows' => 'windows',
                'Mac OS' => 'macos',
                'Linux' => 'linux',
                'Android' => 'android',
                'iOS' => 'ios',
            ],
        ]);

        $builder->add('browser', ChoiceType::class, [
            'label' => 'Browser',
            'required' => false,
            'choices' => [
                'Internet Explorer' => 'ie',
                'Firefox' => 'firefox',
                'Google Chrome' => 'chrome',
                'Safari' => 'safari',
                'Opera' => 'opera',
            ],
        ]);
    }
}
