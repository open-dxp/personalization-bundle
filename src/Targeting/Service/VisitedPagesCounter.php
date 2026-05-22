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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Service;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Storage\TargetingStorageInterface;

/**
 * Makes sure a page visit is counted only once per request.
 */
class VisitedPagesCounter
{
    const STORAGE_KEY = 'pgc';

    private bool $incremented = false;

    public function __construct(private readonly TargetingStorageInterface $targetingStorage)
    {
    }

    public function getCount(VisitorInfo $visitorInfo, string $scope = TargetingStorageInterface::SCOPE_VISITOR): int
    {
        return $this->targetingStorage->get($visitorInfo, $scope, self::STORAGE_KEY, 0);
    }

    public function increment(VisitorInfo $visitorInfo, string $scope = TargetingStorageInterface::SCOPE_VISITOR, bool $force = false): void
    {
        if ($this->incremented && !$force) {
            return;
        }

        // TODO to make sure this works in concurrent request we probably need
        // to support some kind of transactional updates on the storage
        $count = $this->getCount($visitorInfo, $scope);
        $count++;

        $this->targetingStorage->set($visitorInfo, $scope, self::STORAGE_KEY, $count);

        $this->incremented = true;
    }
}
