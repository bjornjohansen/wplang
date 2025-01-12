# bjornjohansen/wplang

Composer plugin to download translation files for WordPress core, plugins and themes from wordpress.org.

## Installation

First run:

```
composer require bjornjohansen/wplang
```

**Note**: If you running Composer v1, make sure to require a version less than `0.2.0`.

Then you’ll have to edit your `composer.json` file. You need to add the following section:
```
"extra": {
    "wordpress-languages": [ "en_GB", "nb_NO", "sv_SE" ],
    "wordpress-language-dir": "wp-content/languages"
}
```

You should propbably want to customize these values to suit your needs.

Finally run:
```
composer update
```

Now Composer will try to pull down translations for your packages from wordpress.org every time you install or update a package.

## CS and Testing

### Prerequisites

In order to get a more consistent code style and introduce testing the project provides some commands to test in an isolated environment.
To use the `Makefile`, ensure the following dependencies are installed on your system:

- **GNU Make**: Required to execute the Makefile commands. Install it via your system's package manager.
- **Docker (Docker Build/Docker Compose)**: See https://docs.docker.com/get-started/get-docker/ for installation instructions.

### Usage

The `Makefile` includes commands to simplify common development and deployment tasks. Run any of the following commands
by typing `make <command>` in your terminal:

- **make help** Output a help section with a brief description of the commands
- **make setup** Setup containers and development dependencies. This should run after `composer install` in a dev environment, but can be run manually if something is missing or has changed.
- **make composer** This runs composer in the PHP 7.2 docker container. Pass additional flags like with the `c=""` option, e.g., `make composer c="install --no-autoloader"`
- **make test** Run PHPUnit tests.
- **make checkcs** Check the code style against the defined `phpcs` rules.
- **make fixcs** Fix the issues found by `phpcs` with `phpcbf`.
- **make compat** Check if the code is compatible with the specified PHP version.

## Credits

This package Started as a fork of Angry Creative’s [Composer Auto Language Updates](https://github.com/Angrycreative/composer-plugin-language-update), but has since been rewritten. It is not compatible with the original package at all, but this package would probably not have existed with the first. There are probably some code in this package that the original author will still recognize.
