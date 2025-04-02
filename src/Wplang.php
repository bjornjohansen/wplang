<?php

namespace BJ\Wplang;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvent;
use Composer\Package\PackageInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\DependencyResolver\Operation\UpdateOperation;

class Wplang implements PluginInterface, EventSubscriberInterface
{

    /**
     * Array of the languages we are using.
     *
     * @var array
     */
    protected $languages = [];

    /**
     * Full path to the language files target directory.
     *
     * @var string
     */
    protected $wpLanguageDir = '';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * Subscribe to Composer events.
     *
     * @return array The events and callbacks.
     */
    public static function getSubscribedEvents()
    {
        return [
            'post-package-install' => [
                ['onPackageAction', 0],
            ],
            'post-package-update'  => [
                ['onPackageAction', 0],
            ],
        ];
    }

    /**
     * Composer plugin activation.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io       = $io;

        $extra = $this->composer->getPackage()->getExtra();

        if (!empty($extra['wordpress-languages'])) {
            $this->languages = $extra['wordpress-languages'];
        }

        if (!empty($extra['wordpress-language-dir'])) {
            $this->wpLanguageDir = dirname(__DIR__, 4) . '/' . $extra['wordpress-language-dir'];
        }
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // do nothing
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // do nothing
    }

    /**
     * Our callback for the post-package-install|update events.
     *
     * @param PackageEvent $event The package event object.
     */
    public function onPackageAction(PackageEvent $event)
    {
        if ($event->getOperation() instanceof UpdateOperation) {
            $package = $event->getOperation()->getTargetPackage();
        } else {
            $package = $event->getOperation()->getPackage();
        }
        $this->getTranslations($package);
    }

    /**
     * Get translations for a package, where applicable.
     *
     * @param PackageInterface $package
     */
    protected function getTranslations(PackageInterface $package)
    {
        try {
            [$provider, $name] = explode('/', $package->getName(), 2);

            $type = $package->getType();

            if ($type === 'wordpress-plugin') {
                $t = new Translatable('plugin', $name, $package->getVersion(), $this->languages, $this->wpLanguageDir);
            } elseif ($type === 'wordpress-theme') {
                $t = new Translatable('theme', $name, $package->getVersion(), $this->languages, $this->wpLanguageDir);
            } elseif (array_key_exists('wordpress/core-implementation', $package->getProvides())) {
                $t = new Translatable('core', $name, $package->getVersion(), $this->languages, $this->wpLanguageDir);
            } else {
                return;
            }

            $results = $t->fetch();

            if (empty($results)) {
                $this->io->write('      - ' . sprintf('No translations updated for %s', $package->getName()));

                return;
            }

            foreach ($results as $result) {
                $this->io->write('      - ' . sprintf('Updated translation to %1$s for %2$s', $result, $package->getName()));
            }
        } catch (\Exception $e) {
            $this->io->writeError('      - ' . $e->getMessage());
        }
    }

}
