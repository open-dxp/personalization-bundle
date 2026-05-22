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

namespace OpenDxp\Bundle\PersonalizationBundle\Debug\Traits;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @internal
 *
 * Simple integration into the profiler timeline by adding events to
 * the debug stopwatch. Usage:
 *
 *  - use this trait from a service
 *  - configure the service to use the debug stopwatch if available:
 *
 *         calls:
 *              - [setStopwatch, ['@?debug.stopwatch']]
 */
trait StopwatchTrait
{
    private ?Stopwatch $stopwatch = null;

    public function setStopwatch(?Stopwatch $stopwatch = null): void
    {
        $this->stopwatch = $stopwatch;
    }

    private function startStopwatch(string $name, string $category): void
    {
        if ($this->stopwatch) {
            $this->stopwatch->start($name, $category);
        }
    }

    private function stopStopwatch(string $name): void
    {
        if ($this->stopwatch) {
            $this->stopwatch->stop($name);
        }
    }
}
