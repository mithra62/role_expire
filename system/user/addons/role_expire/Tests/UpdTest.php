<?php

namespace Mithra62\RoleExpire\Tests;

use PHPUnit\Framework\TestCase;
use ExpressionEngine\Service\Addon\Installer;
use Role_expire_upd;

class UpdTest extends TestCase
{
    public function testUpdFileExists()
    {
        $file_name = realpath(PATH_THIRD.'/role_expire/upd.role_expire.php');
        $this->assertNotNull($file_name);
        require_once $file_name;
    }

    public function testUpdObjectExists(): void
    {
        $this->assertTrue(class_exists('\Role_expire_upd'));
    }

    /**
     * @return Role_expire_upd
     */
    public function testHasCpBackendPropertyExists(): Role_expire_upd
    {
        $cp = new \Role_expire_upd();
        $this->assertObjectHasAttribute('has_cp_backend', $cp);
        return $cp;
    }

    /**
     * @depends testHasCpBackendPropertyExists
     * @param Role_expire_upd $cp
     * @return Role_expire_upd
     */
    public function testCpBackendPropertyValue(Role_expire_upd $cp): Role_expire_upd
    {
        $this->assertEquals('y', $cp->has_cp_backend);
        return $cp;
    }

    /**
     * @depends testCpBackendPropertyValue
     * @return Role_expire_upd
     */
    public function testPublishFieldsPropertyExists(Role_expire_upd $cp): Role_expire_upd
    {
        $this->assertObjectHasAttribute('has_publish_fields', $cp);
        return $cp;
    }

    /**
     * @depends testPublishFieldsPropertyExists
     * @param Role_expire_upd $cp
     * @return Role_expire_upd
     */
    public function testPublishFieldsPropertyValue(Role_expire_upd $cp): Role_expire_upd
    {
        $this->assertEquals('n', $cp->has_publish_fields);
        return $cp;
    }

    /**
     * @depends testPublishFieldsPropertyValue
     * @param Role_expire_upd $cp
     * @return Role_expire_upd
     */
    public function testInstance(Role_expire_upd $cp): Role_expire_upd
    {
        $this->assertInstanceOf('ExpressionEngine\Service\Addon\Installer', new Role_expire_upd);
        return $cp;
    }

    /**
     * @depends testInstance
     * @param Role_expire_upd $cp
     * @return Role_expire_upd
     */
    public function testInstallMethodExists(Role_expire_upd $cp): Role_expire_upd
    {
        $this->assertTrue(method_exists($cp, 'install'));
        return $cp;
    }

    /**
     * @depends testInstallMethodExists
     * @param Role_expire_upd $cp
     * @return Role_expire_upd
     */
    public function testUninstallMethodExists(Role_expire_upd $cp): Role_expire_upd
    {
        $this->assertTrue(method_exists($cp, 'uninstall'));
        return $cp;
    }

    /**
     * @depends testUninstallMethodExists
     * @param Role_expire_upd $cp
     * @return Role_expire_upd
     */
    public function testUpdateMethodExists(Role_expire_upd $cp): Role_expire_upd
    {
        $this->assertTrue(method_exists($cp, 'update'));
        return $cp;
    }
}