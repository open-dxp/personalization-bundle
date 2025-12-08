<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
