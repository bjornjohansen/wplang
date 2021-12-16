<?php
/**
 * @package BJ\WPlang
 */

namespace BJ\WPlang;

/**
 * Class Translatable
 *
 * @package BJ\WPlang
 */
class Translatable {

	/**
	 * The package type: 'plugin', 'theme' or 'core'.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The plugin/theme slug, E.g. 'query-monitor'. In case of core, this is 'wordpress'.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The package version.
	 *
	 * @var float|string
	 */
	protected $version;

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
	protected $wpLanguageDir;

	/**
	 * Array of translation packs available in our languages.
	 *
	 * @var array
	 */
	protected $translations = [];

	/**
	 * Constructor.
	 *
	 * @param string $type          The package type: 'plugin', 'theme' or 'core'.
	 * @param string $slug          The plugin/theme slug, E.g. 'query-monitor'. In case of core, this is 'wordpress'.
	 * @param string $version       The package version.
	 * @param array $languages     Array of the languages we are using.
	 * @param string $wpLanguageDir Full path to the language files target directory.
	 */
	public function __construct( $type, $slug, $version, $languages, $wpLanguageDir ) {

		$this->type          = $type;
		$this->slug          = $slug;
		$this->version       = $version;
		$this->languages     = $languages;
		$this->wpLanguageDir = $wpLanguageDir;

		try {
			$this->translations = $this->getAvailableTranslations();
		} catch ( \Exception $e ) {
			throw new \Exception( $e->getMessage() );
		}
	}

	/**
	 * Get a list of available translations in our languages from the API.
	 *
	 * @throws \Exception
	 * @return array
	 */
	protected function getAvailableTranslations() : array {

		switch ( $this->type ) {
			case 'plugin':
				$url = sprintf( 'https://api.wordpress.org/translations/plugins/1.0/?slug=%s&version=%s', $this->slug, $this->version );
				break;

			case 'theme':
				$url = sprintf( 'https://api.wordpress.org/translations/themes/1.0/?slug=%s&version=%s', $this->slug, $this->version );
				break;

			case 'core':
				$url = sprintf( 'https://api.wordpress.org/translations/core/1.0/?version=%s', $this->version );
				break;

			default:
				throw new \Exception( sprintf('Unknown package type "%s"', $this->type) );
		}

		$body = file_get_contents( $url );
		$res = json_decode( $body );

		if ( ! isset( $res->translations ) || empty( $res->translations ) ) {
			throw new \Exception( sprintf('No translations found for %s', $this->slug) );
		}

		$translations = [];

		foreach ( $res->translations as $translation ) {
			if ( in_array( $translation->language, $this->languages, true ) ) {
				$translations[ $translation->language ][] = $translation;
			}
		}

		return $translations;
	}

	/**
	 * Fetch the translations to our languages for our package.
	 *
	 * @return array
	 */
	public function fetch() : array {

		$results = [];

		foreach ( $this->translations as $language => $translation ) {
			$result = $this->installTranslation( $translation[0]->package );

			if ( $result ) {
				$results[] = $language;
			}
		}

		return $results;
	}


	/**
	 * Get the destination path for type type of object.
	 *
	 * This will also create the directory if if doesn't exist.
	 *
	 * @throws \Exception
	 * @return string path to the destination directory.
	 */
	public function getDestPath() : string {
		$destPath = $this->wpLanguageDir;

		switch ( $this->type ) {
			case 'plugin':
				$destPath .= '/plugins';
				break;

			case 'theme':
				$destPath .= '/themes';
				break;
		}

		if ( ! is_dir( $destPath ) ) {
			$result = mkdir( $destPath, 0775, true );
			if ( ! $result ) {
				throw new \Exception( 'Failed to create directory at: ' . $destPath );
			}
		}

		return $destPath;
	}

	/**
	 * Unpack the downloaded translation ZIP file in the destination directory.
	 *
	 * @param string $tmpZipFileName Path to the translation ZIP file.
	 * @return bool Whether the operation was successful or not.
	 *
	 * @throws \Exception
	 */
	public function unpackTranslation( $tmpZipFileName ) : bool {

		$result   = false;
		$destPath = $this->getDestPath();
		$zip      = new \ZipArchive();

		if ( true === $zip->open( $tmpZipFileName ) ) {
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				$ok = $zip->extractTo( $destPath, [ $zip->getNameIndex( $i ) ] );
				if ( false === $ok ) {
					throw new \Exception( 'There was an error moving the translation to the destination directory' );
				}
			}
			$zip->close();

			$result = true;

		} else {
			throw new \Exception( 'The was an error unzipping the translation files' );
		}

		return $result;
	}

	/**
	 * Download and extract the translation ZIP file in our destination directory.
	 *
	 * @param string $packageUrl The URL to the translation package ZIP file.
	 * @return bool Whether the operation was successful or not.
	 */
	public function installTranslation( $packageUrl ) : bool {
		$result = false;

		try {
			$tmpZipFileName = sys_get_temp_dir() . '/' . $this->type . '-' . $this->slug . '-' . $this->version . '-' . basename( $packageUrl );
			$result = copy( $packageUrl, $tmpZipFileName );

			if ( $result ) {
				$result = $this->unpackTranslation( $tmpZipFileName );
				unlink( $tmpZipFileName );
			}
		} catch ( \Exception $e ) {
		}

		return $result;
	}

}
