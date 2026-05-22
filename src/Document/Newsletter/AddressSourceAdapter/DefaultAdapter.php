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

namespace OpenDxp\Bundle\PersonalizationBundle\Document\Newsletter\AddressSourceAdapter;

use OpenDxp\Bundle\NewsletterBundle\Document\Newsletter\AddressSourceAdapter\DefaultAdapter as BaseDefaultAdapter;
use OpenDxp\Model\DataObject\ClassDefinition;
use OpenDxp\Model\DataObject\Listing;
use Override;

/**
 * @internal
 */
final class DefaultAdapter extends BaseDefaultAdapter
{
    /**
     * @var int[]
     */
    protected mixed $targetGroups = [];

    public function __construct(array $params)
    {
        $this->targetGroups = $params['target_groups'] ?? [];

        parent::__construct($params);
    }

    #[Override]
    protected function getListing(): Listing
    {
        if (empty($this->list)) {
            $objectList = '\\OpenDxp\\Model\\DataObject\\' . ucfirst((string) $this->class) . '\\Listing';
            $this->list = new $objectList();

            $conditions = ['(newsletterActive = 1 AND newsletterConfirmed = 1)'];
            if ($this->condition) {
                $conditions[] = '(' . $this->condition . ')';
            }

            if ($this->targetGroups) {
                $class = ClassDefinition::getByName($this->class);

                if ($class) {
                    $conditions = $this->addTargetGroupConditions($class, $conditions);
                }
            }

            $this->list->setCondition(implode(' AND ', $conditions));
            $this->list->setOrderKey('email');
            $this->list->setOrder('ASC');

            $this->elementsTotal = $this->list->getTotalCount();
        }

        return $this->list;
    }

    /**
     * Handle target group filters
     */
    protected function addTargetGroupConditions(ClassDefinition $class, array $conditions): array
    {
        if (!$class->getFieldDefinition('targetGroup')) {
            return $conditions;
        }

        $fieldDefinition = $class->getFieldDefinition('targetGroup');
        if ($fieldDefinition instanceof ClassDefinition\Data\TargetGroup) {
            $targetGroups = [];
            foreach ($this->targetGroups as $value) {
                if (!empty($value)) {
                    $targetGroups[] = $this->list->quote($value);
                }
            }

            $conditions[] = 'targetGroup IN (' . implode(',', $targetGroups) . ')';
        } elseif ($fieldDefinition instanceof ClassDefinition\Data\TargetGroupMultiselect) {
            $targetGroupsCondition = [];
            foreach ($this->targetGroups as $value) {
                $targetGroupsCondition[] = 'targetGroup LIKE ' . $this->list->quote('%,' . $value . ',%');
            }

            $conditions[] = '(' . implode(' OR ', $targetGroupsCondition) . ')';
        }

        return $conditions;
    }
}
