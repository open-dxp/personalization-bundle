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

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

interface ConditionMatcherInterface
{
    /**
     * Matches a visitor info against a list of condition configurations (as configured via UI)
     */
    public function match(VisitorInfo $visitorInfo, array $configs, bool $collectVariables = false): bool;

    /**
     * Returns collected variables from last match
     */
    public function getCollectedVariables(): array;
}
