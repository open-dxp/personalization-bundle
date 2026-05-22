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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider;

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Service\VisitedPagesCounter as VisitedPagesCounterService;

class VisitedPagesCounter implements DataProviderInterface
{
    const PROVIDER_KEY = 'visited_pages_counter';

    public function __construct(private readonly VisitedPagesCounterService $service)
    {
    }

    public function load(VisitorInfo $visitorInfo): void
    {
        $visitorInfo->set(self::PROVIDER_KEY, $this->service);
    }
}
