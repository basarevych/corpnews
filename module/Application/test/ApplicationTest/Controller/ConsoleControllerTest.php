<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Application\Entity\Setting as SettingEntity;

class ConsoleControllerTest extends AbstractConsoleControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->repoSetting = $this->getMockBuilder('Application\Repository\Setting')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findOneByName' ])
                                  ->getMock();

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Setting', $this->repoSetting ],
                ]));

        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testCronActionCanBeAccessed()
    {
        $this->dispatch('cron');
        $this->assertResponseStatusCode(0);

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\console');
        $this->assertControllerClass('ConsoleController');
        $this->assertMatchedRouteName('cron');
    }

    public function testPopulateDbActionCanBeAccessed()
    {
        $this->dispatch('populate-db');
        $this->assertResponseStatusCode(0);

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\console');
        $this->assertControllerClass('ConsoleController');
        $this->assertMatchedRouteName('populate-db');
    }

    public function testPopulateDbActionWorks()
    {
        $this->repoSetting->expects($this->any())
                          ->method('findOneByName')
                          ->will($this->returnValue(null));

        $persistedSettings = [];
        $callback = function ($entity) use (&$persistedSettings) {
            if ($entity instanceof SettingEntity)
                $persistedSettings[] = $entity;
        };

        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback($callback));

        $this->dispatch('populate-db');

        $this->assertEquals(1, count($persistedSettings), "One Setting should have been saved");

        $autodelete = false;
        foreach ($persistedSettings as $setting) {
            if ($setting->getName() == 'MailboxAutodelete'
                    && $setting->getType() == SettingEntity::TYPE_INTEGER) {
                $autodelete = true;
            }
        }

        $this->assertEquals(true, $autodelete, "MailboxAutodelete was not created");
    }
}
