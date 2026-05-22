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

use OpenDxp\Bundle\PersonalizationBundle\Debug\Traits\StopwatchTrait;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\DataProvider\DataProviderInterface;
use OpenDxp\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;
use Psr\Container\ContainerInterface;

class DataLoader implements DataLoaderInterface
{
    use StopwatchTrait;

    public function __construct(private ContainerInterface $dataProviders)
    {
    }

    public function loadDataFromProviders(VisitorInfo $visitorInfo, array|string $providerKeys): void
    {
        if (!is_array($providerKeys)) {
            $providerKeys = [(string)$providerKeys];
        }

        foreach ($providerKeys as $providerKey) {
            $loadedProviders = $visitorInfo->get('_data_providers', []);

            // skip already loaded providers to avoid circular reference loops
            if (in_array($providerKey, $loadedProviders)) {
                continue;
            }

            $loadedProviders[] = $providerKey;
            $visitorInfo->set('_data_providers', $loadedProviders);

            $dataProvider = $this->dataProviders->get($providerKey);

            // load data from required providers
            if ($dataProvider instanceof DataProviderDependentInterface) {
                $this->loadDataFromProviders(
                    $visitorInfo,
                    $dataProvider->getDataProviderKeys()
                );
            }

            $this->startStopwatch('Targeting:load:' . $providerKey, 'targeting');

            $dataProvider->load($visitorInfo);

            $this->stopStopwatch('Targeting:load:' . $providerKey);
        }
    }

    public function hasDataProvider(string $type): bool
    {
        return $this->dataProviders->has($type);
    }

    public function getDataProvider(string $type): DataProviderInterface
    {
        if (!$this->dataProviders->has($type)) {
            throw new \InvalidArgumentException(sprintf(
                'There is no data provider registered for type "%s"',
                $type
            ));
        }

        return $this->dataProviders->get($type);
    }
}
