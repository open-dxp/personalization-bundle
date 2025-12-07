<?php
declare(strict_types=1);

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

namespace OpenDxp\Bundle\PersonalizationBundle;

use OpenDxp\Bundle\AdminBundle\OpenDxpAdminBundle;
use OpenDxp\Bundle\PersonalizationBundle\DependencyInjection\Compiler\DebugStopwatchPass;
use OpenDxp\Bundle\PersonalizationBundle\DependencyInjection\Compiler\TargetingOverrideHandlersPass;
use OpenDxp\Bundle\PersonalizationBundle\DependencyInjection\OpenDxpPersonalizationExtension;
use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\OpenDxpBundleAdminClassicInterface;
use OpenDxp\Extension\Bundle\Traits\BundleAdminClassicTrait;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use OpenDxp\HttpKernel\Bundle\DependentBundleInterface;
use OpenDxp\HttpKernel\BundleCollection\BundleCollection;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class OpenDxpPersonalizationBundle extends AbstractOpenDxpBundle implements OpenDxpBundleAdminClassicInterface, DependentBundleInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    // @TODO Enable when bundle is moved to own repo

    /*public function getComposerPackageName(): string
    {
       return 'pimcore/personalization-bundle';
    }*/

    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new OpenDxpPersonalizationExtension();
        }

        return $this->extension;
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/opendxppersonalization/css/icons.css',
            '/bundles/opendxppersonalization/css/targeting.css',
        ];
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/opendxppersonalization/js/startup.js',
            '/bundles/opendxppersonalization/js/settings/condition/abstract.js',
            '/bundles/opendxppersonalization/js/settings/conditions.js',
            '/bundles/opendxppersonalization/js/settings/action/abstract.js',
            '/bundles/opendxppersonalization/js/settings/actions.js',
            '/bundles/opendxppersonalization/js/settings/rules/panel.js',
            '/bundles/opendxppersonalization/js/settings/rules/item.js',
            '/bundles/opendxppersonalization/js/settings/targetGroups/panel.js',
            '/bundles/opendxppersonalization/js/settings/targetGroups/item.js',
            '/bundles/opendxppersonalization/js/settings/targetingtoolbar.js',
            '/bundles/opendxppersonalization/js/targeting.js',
            '/bundles/opendxppersonalization/js/document/areatoolbar.js',
            '/bundles/opendxppersonalization/js/object/classes/data/targetGroup.js',
            '/bundles/opendxppersonalization/js/object/classes/data/targetGroupMultiselect.js',
            '/bundles/opendxppersonalization/js/object/tags/targetGroup.js',
            '/bundles/opendxppersonalization/js/object/tags/targetGroupMultiselect.js',
        ];
    }

    public function getInstaller(): Installer
    {
        return $this->container->get(Installer::class);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TargetingOverrideHandlersPass());
        $container->addCompilerPass(new DebugStopwatchPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public static function registerDependentBundles(BundleCollection $collection): void
    {
        $collection->addBundle(new OpenDxpAdminBundle(), 60);
    }
}
