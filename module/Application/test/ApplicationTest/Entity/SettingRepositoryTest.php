<?php

namespace ApplicationTest\Entity;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Setting as SettingEntity;

class SettingRepositoryTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Setting',
        ]);

        $this->em = $this->infrastructure->getEntityManager();
        $this->repo = $this->em->getRepository('Application\Entity\Setting');

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testGetStringValue()
    {
        $setting = new SettingEntity();
        $setting->setName('foobar');
        $setting->setType(SettingEntity::TYPE_STRING);
        $setting->setValueString('string');

        $this->infrastructure->import([ $setting ]);

        $value = $this->repo->getValue('foobar');
        $this->assertEquals('string', $value);
    }

    public function testGetIntegerValue()
    {
        $setting = new SettingEntity();
        $setting->setName('foobar');
        $setting->setType(SettingEntity::TYPE_INTEGER);
        $setting->setValueInteger(9000);

        $this->infrastructure->import([ $setting ]);

        $value = $this->repo->getValue('foobar');
        $this->assertEquals(9000, $value);
    }

    public function testGetFloatValue()
    {
        $setting = new SettingEntity();
        $setting->setName('foobar');
        $setting->setType(SettingEntity::TYPE_FLOAT);
        $setting->setValueFloat(9000.42);

        $this->infrastructure->import([ $setting ]);

        $value = $this->repo->getValue('foobar');
        $this->assertEquals(9000.42, $value);
    }

    public function testGetBooleanValue()
    {
        $setting = new SettingEntity();
        $setting->setName('foobar');
        $setting->setType(SettingEntity::TYPE_BOOLEAN);
        $setting->setValueBoolean(true);

        $this->infrastructure->import([ $setting ]);

        $value = $this->repo->getValue('foobar');
        $this->assertEquals(true, $value);
    }

    public function testGetDatetimeValue()
    {
        $dt = new \DateTime();

        $setting = new SettingEntity();
        $setting->setName('foobar');
        $setting->setType(SettingEntity::TYPE_DATETIME);
        $setting->setValueDatetime($dt);

        $this->infrastructure->import([ $setting ]);

        $value = $this->repo->getValue('foobar');
        $this->assertEquals($dt, $value);
    }
}
