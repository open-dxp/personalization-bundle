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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider\DataProviderInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

interface DataLoaderInterface
{
    /**
     * Loads data from given data providers while taking
     * data provider dependencies into account
     */
    public function loadDataFromProviders(VisitorInfo $visitorInfo, array|string $providerKeys): void;

    /**
     * Checks if a data provider is registered
     */
    public function hasDataProvider(string $type): bool;

    /**
     * Returns the data provider instance identified by name
     */
    public function getDataProvider(string $type): DataProviderInterface;
}
