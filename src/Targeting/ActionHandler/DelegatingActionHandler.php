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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\ActionHandler;

use InvalidArgumentException;
use OpenDxp\Bundle\PersonalizationBundle\Model\Tool\Targeting\Rule;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataLoaderInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProviderDependentInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Psr\Container\ContainerInterface;

class DelegatingActionHandler implements ActionHandlerInterface
{
    public function __construct(
        private readonly ContainerInterface $actionHandlers,
        private readonly DataLoaderInterface $dataLoader
    ) {
    }

    public function apply(VisitorInfo $visitorInfo, array $action, ?Rule $rule = null): void
    {
        /** @var string $type */
        $type = $action['type'] ?? null;

        if (empty($type)) {
            throw new InvalidArgumentException('Invalid action: type is not set');
        }

        $actionHandler = $this->getActionHandler($type);

        // load data providers if necessary
        if ($actionHandler instanceof DataProviderDependentInterface) {
            $this->dataLoader->loadDataFromProviders($visitorInfo, $actionHandler->getDataProviderKeys());
        }

        $actionHandler->apply($visitorInfo, $action, $rule);
    }

    public function hasActionHandler(string $type): bool
    {
        return $this->actionHandlers->has($type);
    }

    public function getActionHandler(string $type): ActionHandlerInterface
    {
        if (!$this->actionHandlers->has($type)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid condition: there is no action handler registered for type "%s"',
                $type
            ));
        }

        return $this->actionHandlers->get($type);
    }
}
