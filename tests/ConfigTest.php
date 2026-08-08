<?php

declare(strict_types=1);

namespace Switch\Config\Tests;

use PHPUnit\Framework\TestCase;
use Switch\Config\Config;

class ConfigTest extends TestCase
{
    public function testGetAndSetWithDotNotation(): void
    {
        $config = new Config();
        $config->set('database.mysql.host', '127.0.0.1');
        $config->set('database.mysql.port', 3306);

        $this->assertEquals('127.0.0.1', $config->get('database.mysql.host'));
        $this->assertEquals(3306, $config->get('database.mysql.port'));
        $this->assertEquals(['host' => '127.0.0.1', 'port' => 3306], $config->get('database.mysql'));
    }

    public function testDefaultValues(): void
    {
        $config = new Config(['app' => ['name' => 'Switch']]);
        $this->assertEquals('Switch', $config->get('app.name'));
        $this->assertEquals('production', $config->get('app.env', 'production'));
    }

    public function testArrayAccessInterface(): void
    {
        $config = new Config();
        $config['app.debug'] = true;

        $this->assertTrue(isset($config['app.debug']));
        $this->assertTrue($config['app.debug']);

        unset($config['app.debug']);
        $this->assertFalse(isset($config['app.debug']));
    }
}
