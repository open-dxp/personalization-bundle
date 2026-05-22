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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug;

use OpenDxp\Bundle\PersonalizationBundle\Debug\Traits\StopwatchTrait;
use OpenDxp\Bundle\PersonalizationBundle\Model\Document\Targeting\TargetingDocumentInterface;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider\TargetingStorage;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider\VisitedPagesCounter;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Document\DocumentTargetingConfigurator;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Storage\TargetingStorageInterface;
use OpenDxp\Model\Document;

class TargetingDataCollector
{
    use StopwatchTrait;

    private array $filteredVisitorInfoDataObjecKeys = [
        TargetingStorage::PROVIDER_KEY,
        VisitedPagesCounter::PROVIDER_KEY,
    ];

    public function __construct(private TargetingStorageInterface $targetingStorage, private DocumentTargetingConfigurator $targetingConfigurator)
    {
    }

    public function collectVisitorInfo(VisitorInfo $visitorInfo): array
    {
        return [
            'visitorId' => $visitorInfo->getVisitorId(),
            'sessionId' => $visitorInfo->getSessionId(),
            'actions' => $visitorInfo->getActions(),
            'data' => $this->filterVisitorInfoData($visitorInfo->getData()),
        ];
    }

    public function getFilteredVisitorInfoDataObjecKeys(): array
    {
        return $this->filteredVisitorInfoDataObjecKeys;
    }

    public function setFilteredVisitorInfoDataObjecKeys(array $filteredVisitorInfoDataObjecKeys): void
    {
        $this->filteredVisitorInfoDataObjecKeys = $filteredVisitorInfoDataObjecKeys;
    }

    protected function filterVisitorInfoData(array $data): array
    {
        // only show a string reference naming the class instead of serializing objects in the list
        foreach ($this->filteredVisitorInfoDataObjecKeys as $key) {
            if (isset($data[$key]) && is_object($data[$key])) {
                $data[$key] = sprintf(
                    'object(%s)',
                    (new \ReflectionObject($data[$key]))->getShortName()
                );
            }
        }

        return $data;
    }

    public function collectStorage(VisitorInfo $visitorInfo): array
    {
        $storage = [];

        foreach (TargetingStorageInterface::VALID_SCOPES as $scope) {
            $created = $this->targetingStorage->getCreatedAt($visitorInfo, $scope);
            $updated = $this->targetingStorage->getCreatedAt($visitorInfo, $scope);

            $storage[$scope] = array_merge([
                'created' => $created ? $created->format('c') : null,
                'updated' => $updated ? $updated->format('c') : null,
            ], $this->targetingStorage->all($visitorInfo, $scope));
        }

        return $storage;
    }

    public function collectMatchedRules(VisitorInfo $visitorInfo): array
    {
        $rules = [];

        foreach ($visitorInfo->getMatchingTargetingRules() as $rule) {
            $duration = null;
            if (null !== $this->stopwatch) {
                try {
                    $event = $this->stopwatch->getEvent(sprintf('Targeting:match:%s', $rule->getName()));
                    $duration = $event->getDuration();
                } catch (\Throwable) {
                    // noop
                }
            }

            $rules[] = [
                'id' => $rule->getId(),
                'name' => $rule->getName(),
                'duration' => $duration,
                'conditions' => $rule->getConditions(),
                'actions' => $rule->getActions(),
            ];
        }

        return $rules;
    }

    public function collectTargetGroups(VisitorInfo $visitorInfo): array
    {
        $targetGroups = [];

        foreach ($visitorInfo->getTargetGroupAssignments() as $assignment) {
            $targetGroups[] = [
                'id' => $assignment->getTargetGroup()->getId(),
                'name' => $assignment->getTargetGroup()->getName(),
                'threshold' => $assignment->getTargetGroup()->getThreshold(),
                'count' => $assignment->getCount(),
            ];
        }

        return $targetGroups;
    }

    public function collectDocumentTargetGroup(?Document $document = null): ?array
    {
        if (!$document instanceof TargetingDocumentInterface) {
            return null;
        }

        $targetGroupId = $document->getUseTargetGroup();
        if (!$targetGroupId) {
            return null;
        }

        $targetGroup = TargetGroup::getById($targetGroupId);
        if ($targetGroup) {
            return [
                'id' => $targetGroup->getId(),
                'name' => $targetGroup->getName(),
            ];
        }

        return null;
    }

    public function collectDocumentTargetGroupMapping(): array
    {
        $resolvedMapping = $this->targetingConfigurator->getResolvedTargetGroupMapping();
        $mapping = [];

        /** @var TargetGroup $targetGroup */
        foreach ($resolvedMapping as $documentId => $targetGroup) {
            $document = Document::getById($documentId);

            $mapping[] = [
                'document' => [
                    'id' => $document->getId(),
                    'path' => $document->getRealFullPath(),
                ],
                'targetGroup' => [
                    'id' => $targetGroup->getId(),
                    'name' => $targetGroup->getName(),
                ],
            ];
        }

        return $mapping;
    }
}
