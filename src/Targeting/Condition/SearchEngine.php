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

namespace OpenDxp\Bundle\PersonalizationBundle\Targeting\Condition;

use InvalidArgumentException;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class SearchEngine extends AbstractVariableCondition implements ConditionInterface
{
    /**
     * @var string|null
     */
    private mixed $engine = null;

    private array $validEngines = ['google', 'bing', 'yahoo'];

    public function __construct(?string $engine = null)
    {
        if (!empty($engine)) {
            $validEngines = array_merge(['all'], $this->validEngines);

            if (!in_array($engine, $validEngines, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid engine: "%s"',
                    $engine
                ));
            }
        }

        $this->engine = $engine;
    }

    public static function fromConfig(array $config): static
    {
        return new static($config['searchengine'] ?? null);
    }

    public function canMatch(): bool
    {
        $validEngines = array_merge(['all'], $this->validEngines);

        return !empty($this->engine) && in_array($this->engine, $validEngines, true);
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        $request = $visitorInfo->getRequest();
        $referrer = $request->headers->get('Referrer');

        if (empty($referrer)) {
            return false;
        }

        $pattern = null;

        if ('all' === $this->engine) {
            $engines = array_map(fn (string $engine) => preg_quote($engine, '/'), $this->validEngines);

            $pattern = '/(' . implode('|', $engines) . ')/i';
        } else {
            $pattern = '/(' . preg_quote((string) $this->engine, '/') . ')/i';
        }

        if (preg_match($pattern, $referrer)) {
            $this->setMatchedVariable('referrer', $referrer);

            return true;
        }

        return false;
    }
}
