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

namespace OpenDxp\Bundle\PersonalizationBundle\Event;

final class TargetingEvents
{
    /**
     * Fired when the targeting code is rendered. Allows to add data to the targeting
     * code or to change the template completely.
     *
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingCodeEvent")
     */
    const string TARGETING_CODE = 'opendxp.targeting.targeting_code';

    /**
     * Fired when the VisitorInfo object was built for a request before
     * any matching and action handling is applied.
     *
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingEvent")
     */
    const string PRE_RESOLVE = 'opendxp.targeting.pre_resolve';

    /**
     * Fired after all targeting rules were matched and applied
     *
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingEvent")
     */
    const string POST_RESOLVE = 'opendxp.targeting.post_resolve';

    /**
     * Fired when a rule matches before any actions are applied
     *
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingRuleEvent")
     */
    const string PRE_RULE_ACTIONS = 'opendxp.targeting.pre_rule_actions';

    /**
     * Fired when a rule matches after all actions were applied
     *
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\TargetingRuleEvent")
     */
    const string POST_RULE_ACTIONS = 'opendxp.targeting.post_rule_actions';

    /**
     * Fired when a targeting condition is about to be built. Allows to
     * build the condition in a custom manner instead of relying on the
     * default factory.
     *
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\BuildConditionEvent")
     */
    const string BUILD_CONDITION = 'opendxp.targeting.build_condition';

    /**
     * Fired when a target group which is configured on document settings
     * is assigned to a visitor info.
     *
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\AssignDocumentTargetGroupEvent")
     */
    const string ASSIGN_DOCUMENT_TARGET_GROUP = 'opendxp.targeting.assign_document_target_group';

    /**
     * Fired after a condition was used which depends on the count of visited
     * pages. Will be used by VisitedPagesCountListener to update the page count
     * if there are conditions depending on it.
     *
     * @Event("Symfony\Component\EventDispatcher\GenericEvent")
     */
    const string VISITED_PAGES_COUNT_MATCH = 'opendxp.targeting.visited_pages_count_match';

    /**
     * Fired before the targeting debug toolbar is rendered
     *
     * @Event("OpenDxp\Bundle\PersonalizationBundle\Event\Targeting\RenderToolbarEvent")
     */
    const string RENDER_TOOLBAR = 'opendxp.targeting.render_toolbar';
}
