<?php

namespace BJ\Wplang\Tests\Integration;

class WorgPluginTest extends AbstractIntegrationTestCase
{
    protected static $case = 'worg-plugin';

    public function testValidComposerJsonConfigProcessesCorrectly()
    {
        // Run the Composer command and capture output/exit code.
        self::$composer->runComposerCommand();

        // Assert that the output directory structure is created as expected.
        $wpContentLanguagesDir = self::$composer->getDir() . '/wp-content/languages';
        $wpContentLanguagesPluginDir = self::$composer->getDir() . '/wp-content/languages/plugins';
        $this->assertDirectoryExists($wpContentLanguagesDir);
        $this->assertDirectoryExists($wpContentLanguagesPluginDir);

        // Assert that the expected language files are created in the correct location.
        $expectedFiles = [
            'classic-editor-en_GB.mo',
            'classic-editor-en_GB.po',
        ];
        foreach ($expectedFiles as $file) {
            $this->assertFileExists($wpContentLanguagesPluginDir . '/' . $file);
        }
    }
}
