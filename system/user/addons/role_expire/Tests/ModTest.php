<?php

namespace Mithra62\Grid\Pagination\Tests;

use PHPUnit\Framework\TestCase;
use role_expire;

class ModTest extends TestCase
{
    public function testModuleFileExists()
    {
        $file_name = realpath(PATH_THIRD . '/role_expire/mod.role_expire.php');
        $this->assertNotNull($file_name);
        require_once $file_name;
    }

    public function testModuleObjectExists()
    {
        $this->assertTrue(class_exists('\Role_expire'));
    }

    public function testModInstance()
    {
        $this->assertInstanceOf('ExpressionEngine\Service\Addon\Module', new Role_expire);
    }
}