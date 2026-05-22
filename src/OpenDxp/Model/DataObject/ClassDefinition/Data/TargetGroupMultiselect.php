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

namespace OpenDxp\Model\DataObject\ClassDefinition\Data;

use OpenDxp;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool;
use OpenDxp\Model;
use OpenDxp\Model\DataObject\ClassDefinition\Service;
use Override;

class TargetGroupMultiselect extends Model\DataObject\ClassDefinition\Data\Multiselect
{
    /**
     * @internal
     */
    public function configureOptions(): void
    {
        $list = new Tool\Targeting\TargetGroup\Listing();
        $list->setOrder('asc');
        $list->setOrderKey('name');

        $targetGroups = $list->load();

        $options = [];
        foreach ($targetGroups as $targetGroup) {
            $options[] = [
                'value' => $targetGroup->getId(),
                'key'   => $targetGroup->getName(),
            ];
        }

        $this->setOptions($options);
    }

    #[Override]
    public static function __set_state(array $data): static
    {
        $obj = parent::__set_state($data);
        if (OpenDxp::inAdmin()) {
            $obj->configureOptions();
        }

        return $obj;
    }

    #[Override]
    public function jsonSerialize(): mixed
    {
        if (Service::doRemoveDynamicOptions()) {
            $this->options = null;
        }

        return parent::jsonSerialize();
    }

    #[Override]
    public function resolveBlockedVars(): array
    {
        $blockedVars = parent::resolveBlockedVars();
        $blockedVars[] = 'options';

        return $blockedVars;
    }

    #[Override]
    public function getFieldType(): string
    {
        return 'targetGroupMultiselect';
    }

    public function __wakeup(): void
    {
        $this->init();
    }

    /**
     * @return $this
     *
     * @internal
     */
    private function init(): static
    {
        $options = $this->getOptions();
        if (empty($options)) {
            $this->configureOptions();
        }

        return $this;
    }
}
