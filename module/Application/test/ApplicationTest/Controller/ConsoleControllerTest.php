<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Application\Entity\Setting as SettingEntity;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Group as GroupEntity;
use DataForm\Document\Profile as ProfileDocument;

class ConsoleDaemonMock
{
    public function start()
    {
    }

    public function runTask()
    {
    }
}

class ConsoleControllerTest extends AbstractConsoleControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->repoSetting = $this->getMockBuilder('Application\Entity\SettingRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findOneByName', 'getValue' ])
                                  ->getMock();

        $this->repoClient = $this->getMockBuilder('Application\Client\ClientRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'find', 'findAll' ])
                                 ->getMock();

        $this->repoGroup = $this->getMockBuilder('Application\Client\GroupRepository')
                                ->disableOriginalConstructor()
                                ->setMethods([ 'findOneByName' ])
                                ->getMock();

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Setting', $this->repoSetting ],
                    [ 'Application\Entity\Client', $this->repoClient ],
                    [ 'Application\Entity\Group', $this->repoGroup ],
                ]));

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush', 'remove' ])
                         ->getMock();

        $this->repoProfile = $this->getMockBuilder('DataForm\Document\ProfileRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'find', 'findAll' ])
                                  ->getMock();

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'DataForm\Document\Profile', $this->repoProfile ],
                ]));

        $this->task = $this->getMockBuilder('Application\Service\TaskDaemon')
                           ->setMethods([ 'getDaemon' ])
                           ->getMock();

        $this->task->expects($this->any())
                   ->method('getDaemon')
                   ->will($this->returnValue(new ConsoleDaemonMock()));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
        $sl->setService('TaskDaemon', $this->task);
    }

    public function testCronActionCanBeAccessed()
    {
        $this->dispatch('cron');

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

        $this->repoGroup->expects($this->any())
                        ->method('findOneByName')
                        ->will($this->returnValue(null));

        $persistedSettings = [];
        $persistedGroups = [];
        $callback = function ($entity) use (&$persistedSettings, &$persistedGroups) {
            if ($entity instanceof SettingEntity)
                $persistedSettings[] = $entity;
            else if ($entity instanceof GroupEntity)
                $persistedGroups[] = $entity;
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

        $this->assertEquals(true, count($persistedGroups) == count(GroupEntity::getSystemNames()), "All system Groups should have been saved");

        $system = GroupEntity::getSystemNames();
        for ($i = 0; $i < count($persistedGroups); $i++)
            $this->assertEquals($system[$i], $persistedGroups[$i]->getName(), "System group not created");
    }

    public function testCheckDbActionCanBeAccessed()
    {
        $this->dispatch('check-db');

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\console');
        $this->assertControllerClass('ConsoleController');
        $this->assertMatchedRouteName('check-db');
    }

    public function testCheckDbActionCreatesDeletesDocuments()
    {
        $client = new ClientEntity();

        $reflection = new \ReflectionClass(get_class($client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($client, 42);

        $this->repoClient->expects($this->any())
                         ->method('findAll')
                         ->will($this->returnValue([ $client ]));

        $doc = new ProfileDocument();
        $doc->setId(9000);

        $this->repoProfile->expects($this->any())
                          ->method('findAll')
                          ->will($this->returnValue([ $doc ]));

        $createdDocs = [];
        $this->dm->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($doc) use (&$createdDocs) {
                    $createdDocs[] = $doc;
                 }));

        $removedDocs = [];
        $this->dm->expects($this->any())
                 ->method('remove')
                 ->will($this->returnCallback(function ($doc) use (&$removedDocs) {
                    $removedDocs[] = $doc;
                 }));

        ob_start();
        $this->dispatch('check-db --repair');
        ob_end_clean();
        $this->assertResponseStatusCode(0);

        $this->assertEquals(1, count($createdDocs), "One document should have been created");
        $this->assertEquals(42, $createdDocs[0]->getId(), "Incorrect created doc id");

        $this->assertEquals(1, count($removedDocs), "One document should have been created");
        $this->assertEquals(9000, $removedDocs[0]->getId(), "Incorrect removed doc id");
    }

    public function testCheckDbActionCorrectsEmail()
    {
        $client = new ClientEntity();
        $client->setEmail('foo');

        $reflection = new \ReflectionClass(get_class($client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($client, 42);

        $this->repoClient->expects($this->any())
                         ->method('findAll')
                         ->will($this->returnValue([ $client ]));

        $doc = new ProfileDocument();
        $doc->setId(42);
        $doc->setClientEmail('bar');

        $this->repoProfile->expects($this->any())
                          ->method('find')
                          ->will($this->returnValue($doc));

        $this->repoProfile->expects($this->any())
                          ->method('findAll')
                          ->will($this->returnValue([]));

        ob_start();
        $this->dispatch('check-db --repair');
        ob_end_clean();
        $this->assertResponseStatusCode(0);

        $this->assertEquals('foo', $doc->getClientEmail(), "Incorrect email was set");
    }

    protected function setProp($object, $property, $value)
    {
        $class = new \ReflectionClass(get_class($object));

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
