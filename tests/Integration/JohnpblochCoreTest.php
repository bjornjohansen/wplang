<?php

namespace BJ\Wplang\Tests\Integration;

class JohnpblochCoreTest extends AbstractIntegrationTestCase
{
    protected static $case = 'johnpbloch-core';

    public function testValidComposerJsonConfigProcessesCorrectly(): void
    {
        // Run the Composer command and capture output/exit code.
        self::$composer->runComposerCommand();

        // Assert that the output directory structure is created as expected.
        $wpContentLanguagesDir      = self::$composer->getDir() . '/wp-content/languages';
        $this->assertDirectoryExists($wpContentLanguagesDir);

        // Assert that the expected language files are created in the correct location.
        $expectedFiles = [
            'nb_NO.mo',
            'nb_NO.po',
            'admin-nb_NO.mo',
            'admin-nb_NO.po',
            'admin-network-nb_NO.mo',
            'admin-network-nb_NO.po',
            'continents-cities-nb_NO.mo',
            'continents-cities-nb_NO.po',
        ];
        foreach ($expectedFiles as $file) {
            $this->assertFileExists($wpContentLanguagesDir . '/' . $file);
        }
    }
}
