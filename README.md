# bjornjohansen/wplang

Composer plugin to download translation files for WordPress core, plugins and themes from wordpress.org.

## Installation

First run:

```
composer require bjornjohansen/wplang
```

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

## Credits

This package Started as a fork of Angry Creative’s [Composer Auto Language Updates](https://github.com/Angrycreative/composer-plugin-language-update), but has since been rewritten. It is not compatible with the original package at all, but this package would probably not have existed with the first. There are probably some code in this package that the original author will still recognize.
