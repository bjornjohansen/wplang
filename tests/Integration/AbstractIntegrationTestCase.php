<?php

namespace BJ\Wplang\Tests\Integration;

use PHPUnit\Framework\TestCase;
use BJ\Wplang\Tests\Bootstrap\ComposerTestHelper;

class AbstractIntegrationTestCase extends TestCase
{
    /**
     * @var ComposerTestHelper
     */
    protected static $composer;

    /**
     * @var string
     */
    protected static $case;

    public static function setUpBeforeClass(): void
    {
        static::$composer = new ComposerTestHelper(static::$case);
    }

    public function setUp(): void
    {
        self::$composer->runComposerCommand();
    }
}
