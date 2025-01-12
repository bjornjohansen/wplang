<?php

namespace BJ\Wplang\Tests\Integration;

class WorgThemeTest extends AbstractIntegrationTestCase
{
    protected static $case = 'worg-theme';

    public function testValidComposerJsonConfigProcessesCorrectly(): void
    {
        // Assert that the output directory structure is created as expected.
        $wpContentLanguagesDir      = self::$composer->getDir() . '/wp-content/languages';
        $wpContentThemeLanguagesDir = $wpContentLanguagesDir . '/themes';
        $this->assertDirectoryExists($wpContentLanguagesDir);
        $this->assertDirectoryExists($wpContentThemeLanguagesDir);

        // Assert that the expected language files are created in the correct location.
        $expectedFiles = [
            'twentytwentyfive-nb_NO.mo',
            'twentytwentyfive-nb_NO.po',
        ];
        foreach ($expectedFiles as $file) {
            $this->assertFileExists($wpContentThemeLanguagesDir . '/' . $file);
        }

        $unexpectedFiles = [
            'twentytwentyfive-en_GB.mo',
            'twentytwentyfive-en_GB.po',
        ];
        foreach ($unexpectedFiles as $file) {
            $this->assertFileNotExists($wpContentThemeLanguagesDir . '/' . $file);
        }
    }
}
