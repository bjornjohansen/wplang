<?php

namespace BJ\Wplang\Tests\Bootstrap;

class ComposerTestHelper
{
    private $dir;
    /**
     * @var string
     */
    private $case;

    public function __construct(string $case)
    {
        $this->case = $case;
        $this->setupComposerJson();
    }

    private function setupComposerJson(): void
    {
        // Load the base configuration file.
        $baseConfig = json_decode(file_get_contents(__DIR__ . '/../fixtures/composer.json'), true);

        // Load the case-specific overrides.
        $composerJsonOverrides = json_decode(file_get_contents(__DIR__ . "/../fixtures/composer-{$this->case}.json"), true);

        // Apply overrides to the base configuration.
        $finalConfig = array_replace_recursive($baseConfig, $composerJsonOverrides);

        // Save the final configuration to a temporary composer.json file.
        file_put_contents(
            $this->getDir() . '/composer.json',
            json_encode($finalConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function getDir(): string
    {
        if (isset($this->dir)) {
            return $this->dir;
        }

        $this->dir = sys_get_temp_dir() . '/' . $this->case;
        if (!is_dir($this->dir) && !mkdir($this->dir, 0777, true)) {
            throw new \RuntimeException("Unable to create temporary directory: {$this->dir}");
        }

        return $this->dir;
    }

    public function runComposerCommand(string $command = 'update'): void
    {
        $verbosity = '--quiet';

        if(in_array('--debug', $_SERVER['argv'], true)) {
            $verbosity = '-vvv';
        }

        exec("composer $command --no-dev {$verbosity} --working-dir=" . $this->getDir(), $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException("Composer command failed: " . implode("\n", $output));
        }
    }

    private static function rm(string $dirOrFile): void
    {
        if (is_file($dirOrFile)) {
            unlink($dirOrFile);

            return;
        }

        if (is_dir($dirOrFile)) {
            $entries = scandir($dirOrFile);
            foreach ($entries as $entry) {
                if ($entry !== "." && $entry !== "..") {
                    if (is_dir($dirOrFile . DIRECTORY_SEPARATOR . $entry)
                        && !is_link($dirOrFile . DIRECTORY_SEPARATOR . $entry)) {
                        self::rm($dirOrFile . DIRECTORY_SEPARATOR . $entry);
                    } else {
                        unlink($dirOrFile . DIRECTORY_SEPARATOR . $entry);
                    }
                }
            }

            rmdir($dirOrFile);
        }
    }
}
