<?php

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

namespace OpenDxp\Bundle\PersonalizationBundle\Tests\Support\Helper;

use OpenDxp\Bundle\PersonalizationBundle\Installer;
use OpenDxp\Db;
use OpenDxp\Tests\Support\Helper\OpenDxp;

class Unit extends \Codeception\Module
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function _beforeSuite($settings = [])
    {

        /** @var OpenDxp $opendxpModule */
        $opendxpModule = $this->getModule('\\' . OpenDxp::class);

        //create migrations table in order to allow installation - needed for SettingsStoreAware Installer

        Db::get()->executeStatement('
            create table migration_versions
            (
                version varchar(1024) not null
                    primary key,
                executed_at datetime null,
                execution_time int null
            )
            collate=utf8_unicode_ci;
            ');

        $installer = $opendxpModule->getContainer()->get(Installer::class);
        $installer->install();
    }
}
