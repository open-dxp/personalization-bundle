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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition;

interface VariableConditionInterface
{
    /**
     * Returns variables which are evaluated in the "Session with Variables"
     * rule scope. This is expected to return the variables which were fetched
     * in the last evaluation run. Each condition is a dedicated instance and
     * can return the variables which were resolved during matching.
     *
     * It's important to store/return these variables in a deterministic way (e.g. same
     * array key order) as the hash of their serialized contents is compared against
     * a stored hash to determine if the rule actions need to be evaluated.
     *
     */
    public function getMatchedVariables(): array;
}
