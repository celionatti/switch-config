<?php

declare(strict_types=1);

namespace Switch\Config\Tests;

use PHPUnit\Framework\TestCase;
use Switch\Config\Env;

require_once __DIR__ . '/../src/helpers.php';

class EnvTest extends TestCase
{
    public function testEnvLoadParsesValues(): void
    {
        $tmpFile = sys_get_temp_dir() . '/.env_test_' . uniqid();
        $content = <<<ENV
APP_NAME="Switch App"
APP_DEBUG=true
APP_PORT=8000
DB_PASS=null
EMPTY_VAL=empty
# Comment line
ENV;
        file_put_contents($tmpFile, $content);

        try {
            Env::load($tmpFile);

            $this->assertEquals('Switch App', env('APP_NAME'));
            $this->assertTrue(env('APP_DEBUG'));
            $this->assertEquals('8000', env('APP_PORT'));
            $this->assertNull(env('DB_PASS'));
            $this->assertEquals('', env('EMPTY_VAL'));
            $this->assertEquals('fallback', env('NONEXISTENT_KEY', 'fallback'));
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}
