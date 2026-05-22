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

namespace OpenDxp\Model\DataObject\ClassDefinition\Data;

use OpenDxp\Bundle\PersonalizationBundle\Model\Tool;
use OpenDxp\Model;
use OpenDxp\Model\DataObject;
use OpenDxp\Model\DataObject\ClassDefinition\Service;

class TargetGroup extends Model\DataObject\ClassDefinition\Data\Select
{
    /**
     *
     *
     * @see ResourcePersistenceAwareInterface::getDataFromResource
     */
    #[\Override]
    public function getDataFromResource(
        mixed $data,
        ?Dataobject\Concrete $object = null,
        array $params = []
    ): null|string|int {
        if (!empty($data)) {
            try {
                $this->checkValidity($data, true, $params);
            } catch (\Exception) {
                $data = null;
            }
        }

        return $data;
    }

    /**
     *
     *
     * @see ResourcePersistenceAwareInterface::getDataForResource
     */
    #[\Override]
    public function getDataForResource(
        mixed $data,
        ?DataObject\Concrete $object = null,
        array $params = []
    ): null|string|int {
        $this->init();
        if (!empty($data)) {
            try {
                $this->checkValidity($data, true, $params);
            } catch (\Exception) {
                $data = null;
            }
        }

        return $data;
    }

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

    #[\Override]
    public function checkValidity(mixed $data, bool $omitMandatoryCheck = false, array $params = []): void
    {
        if (!$omitMandatoryCheck && $this->getMandatory() && empty($data)) {
            throw new Model\Element\ValidationException('Empty mandatory field [ '.$this->getName().' ]');
        }

        if (!empty($data)) {
            $targetGroup = Tool\Targeting\TargetGroup::getById((int)$data);

            if (!$targetGroup instanceof Tool\Targeting\TargetGroup) {
                throw new Model\Element\ValidationException('Invalid target group reference');
            }
        }
    }

    #[\Override]
    public static function __set_state(array $data): static
    {
        $obj = parent::__set_state($data);
        if (\OpenDxp::inAdmin()) {
            $obj->configureOptions();
        }

        return $obj;
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        if (Service::doRemoveDynamicOptions()) {
            $this->options = null;
        }

        return parent::jsonSerialize();
    }

    #[\Override]
    public function resolveBlockedVars(): array
    {
        $blockedVars = parent::resolveBlockedVars();
        $blockedVars[] = 'options';

        return $blockedVars;
    }

    #[\Override]
    public function getFieldType(): string
    {
        return 'targetGroup';
    }

    public function __wakeup(): void
    {
        $this->init();
    }

    /**
     * @return $this
     *
     * @internal
     *
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
