<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

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
}
