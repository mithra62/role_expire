<?php

namespace Mithra62\RoleExpire\Tests;

use PHPUnit\Framework\TestCase;

class McpTest extends TestCase
{
    public function testMcpFileExists()
    {
        $file_name = realpath(PATH_THIRD . '/role_expire/mcp.role_expire.php');
        $this->assertNotNull($file_name);
        require_once $file_name;
    }

    public function testMcpObjectExists()
    {
        $this->assertTrue(class_exists('\Role_expire_mcp'));
    }
}