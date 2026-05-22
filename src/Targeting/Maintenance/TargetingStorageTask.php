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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Maintenance;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Storage\MaintenanceStorageInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Storage\TargetingStorageInterface;
use OpenDxp\Maintenance\TaskInterface;

class TargetingStorageTask implements TaskInterface
{
    public function __construct(private readonly TargetingStorageInterface $targetingStorage)
    {
    }

    public function execute(): void
    {
        if (!$this->targetingStorage instanceof MaintenanceStorageInterface) {
            return;
        }

        $this->targetingStorage->maintenance();
    }
}
