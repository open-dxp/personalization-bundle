# Installation

## Installation Process

Install bundle via composer:
```bash 
composer require open-dxp/personalization-bundle
```

Enable bundle in `config/bundles.php`:
```php
return [
    ...
    OpenDxp\Bundle\PersonalizationBundle\OpenDxpPersonalizationBundle::class => ['all' => true],
    ...
];
```

Install bundle via console:
```bash
bin/console opendxp:bundle:install OpenDxpPersonalizationBundle
```

#### Config
:::info
By default, targeting would be disabled. To enable it, you can either use the cookie `opendxp_targeting_enabled=1` or set the following config:
:::

```yaml
opendxp_personalization:
    targeting:
        enabled: true 
```

## Uninstallation

Before uninstalling the bundle, the `Target Group` references must be removed from DataObject classes, Custom services and Ecommerce Pricing Rules manually.
