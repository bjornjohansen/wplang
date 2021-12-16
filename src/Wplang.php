<?php

namespace BJ\Wplang;

use Composer\Composer;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvent;
use Composer\Package\PackageInterface;

class Wplang implements PluginInterface, EventSubscriberInterface {

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
	 * Composer plugin activation.
	 */
	public function activate( Composer $composer, IOInterface $io ) {
		$this->composer = $composer;
		$this->io = $io;

		$extra = $this->composer->getPackage()->getExtra();

		if ( ! empty( $extra['wordpress-languages'] ) ) {
			$this->languages = $extra['wordpress-languages'];
		}

		if ( ! empty( $extra['wordpress-language-dir'] ) ) {
			$this->wpLanguageDir = dirname( __DIR__, 4 ) . '/' . $extra['wordpress-language-dir'];
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
	 * Subscribe to Composer events.
	 *
	 * @return array The events and callbacks.
	 */
	public static function getSubscribedEvents() {
		return [
			'post-package-install' => [
				[ 'onPackageAction', 0 ],
			],
			'post-package-update' => [
				[ 'onPackageAction', 0 ],
			],
		];
	}

	/**
	 * Our callback for the post-package-install|update events.
	 *
	 * @param  PackageEvent $event The package event object.
	 */
	public function onPackageAction( PackageEvent $event ) {
        if ($event->getOperation() instanceof UpdateOperation) {
            $package = $event->getOperation()->getTargetPackage();
        } else {
            $package = $event->getOperation()->getPackage();
        }
		$this->getTranslations( $package );
	}

	/**
	 * Get translations for a package, where applicable.
	 *
	 * @param PackageInterface $package
	 */
	protected function getTranslations( PackageInterface $package ) {

		try {

			$t = new \stdClass();

			list( $provider, $name ) = explode( '/', $package->getName(), 2 );

			switch ( $package->getType() ) {
				case 'wordpress-plugin':
					$t = new Translatable( 'plugin', $name, $package->getVersion(), $this->languages, $this->wpLanguageDir );
					break;
				case 'wordpress-theme':
					$t = new Translatable( 'theme', $name, $package->getVersion(), $this->languages, $this->wpLanguageDir );
					break;
				case 'package':
					if ( 'johnpbloch' === $provider && 'wordpress' === $name ) {
						$t = new Translatable( 'core', $name, $package->getVersion(), $this->languages, $this->wpLanguageDir );
					}
					break;
				case 'wordpress-core':
					if ( 'roots' === $provider && 'wordpress' === $name ) {
						$t = new Translatable( 'core', $name, $package->getVersion(), $this->languages, $this->wpLanguageDir );
					}
					break;

				default:
					break;
			}

			if ( is_a( $t, __NAMESPACE__ . '\Translatable' ) ) {

				$results = $t->fetch();

				if ( empty( $results ) ) {
					$this->io->write( '      - ' . sprintf( 'No translations updated for %s', $package->getName() ) );
				} else {
					foreach ( $results as $result ) {
						$this->io->write( '      - ' . sprintf( 'Updated translation to %1$s for %2$s', $result, $package->getName() ) );
					}
				}
			}
		} catch ( \Exception $e ) {
			$this->io->writeError( '      - ' . $e->getMessage() );
		}

	}

}

