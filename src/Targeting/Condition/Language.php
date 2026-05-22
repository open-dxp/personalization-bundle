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

use OpenDxp\Bundle\PersonalizationBundle\Targeting\Debug\Util\OverrideAttributeResolver;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Symfony\Component\HttpFoundation\Request;

class Language extends AbstractVariableCondition implements ConditionInterface
{
    public function __construct(private readonly ?string $language = null)
    {
    }

    public static function fromConfig(array $config): static
    {
        return new static($config['language'] ?? null);
    }

    public function canMatch(): bool
    {
        return !empty($this->language);
    }

    public function match(VisitorInfo $visitorInfo): bool
    {
        $request = $visitorInfo->getRequest();

        $language = $this->loadLanguage($request);
        if (empty($language)) {
            return false;
        }

        if ($language === $this->language) {
            $this->setMatchedVariable('language', $language);

            return true;
        }

        // only check the language without territory if configured
        if (!str_contains((string) $this->language, '_') && str_contains($language, '_')) {
            $normalizedLanguage = explode('_', $language)[0];

            if ($normalizedLanguage === $this->language) {
                $this->setMatchedVariable('language', $language);

                return true;
            }
        }

        return false;
    }

    protected function loadLanguage(Request $request): ?string
    {
        // handle override
        $language = OverrideAttributeResolver::getOverrideValue($request, 'language');
        if (!empty($language)) {
            return $language;
        }

        return $request->getPreferredLanguage();
    }
}
