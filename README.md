# Nanbando: sulu

Nanando-Plugin which provides requirements and config-presets. When this plugin ist activated it will detect your
sulu version and edition. Therefor you can omit the necessary-config for your sulu-application.

## Available Presets

This Plugin provides presets for all combinations between the editions "standard", "minimal" and phpcr_backends
"jackrabbit" and "doctrinedbal". Also the drafting changes in versions ^1.3 will be handled correctly.

## Automatic detection

This plugin is able to detect your application automatically. It will prepend the configuration for the `application`
in your nanbando.json file.

If the plugin is not able todo this (f.e. if you integrate sulu into another application) you can still use the
presets when you configure the `application`.


```json
{
    ...
    "application": {
        "name": "sulu",
        "version": "1.4.1",
        "options" {
            "edition": "standard|minimal|custom",
            "phpcr_backend": "doctrinedbal|jackrabbit"
        }
    }
    ...
}
```

## Installation

You can install this plugin by adding `nanbando/sulu` to the `require`-section of the nanbando.json file.

## Configuration

```json
{
    "name": "application",
    "imports": [
        "app/config/parameters.yml"
    ],
    "require": {
        "nanbando/sulu": "^0.1"
    }
}
```

If you use the standard edition you also have to import `app/config/phpcr.yml` which contains the configuration for
`phpcr_backend`.

## Documentation

See the official documentation on [nanbando.readthedocs.io/en/latest/plugins/index.html](https://nanbando.readthedocs.io/en/latest/plugins/index.html).
