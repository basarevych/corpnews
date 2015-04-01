<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Application\Entity\Setting as SettingEntity;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Group as GroupEntity;
use DataForm\Document\Profile as ProfileDocument;
use DataForm\Document\Subscription as SubscriptionDocument;

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

        $client = new ClientEntity();
        $client->setEmail('foo');

        $reflection = new \ReflectionClass(get_class($client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($client, 42);

        $this->repoClient->expects($this->any())
                         ->method('findAll')
                         ->will($this->returnValue([ $client ]));

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

        $docProfile = new ProfileDocument();
        $docProfile->setId(9000);

        $this->repoProfile->expects($this->any())
                          ->method('findAll')
                          ->will($this->returnValue([ $docProfile ]));

        $this->repoSubscription = $this->getMockBuilder('DataForm\Document\SubscriptionRepository')
                                      ->disableOriginalConstructor()
                                      ->setMethods([ 'find', 'findAll' ])
                                      ->getMock();

        $docSubscription = new SubscriptionDocument();
        $docSubscription->setId(9000);

        $this->repoSubscription->expects($this->any())
                              ->method('findAll')
                              ->will($this->returnValue([ $docSubscription ]));

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'DataForm\Document\Profile', $this->repoProfile ],
                    [ 'DataForm\Document\Subscription', $this->repoSubscription ],
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

        $this->assertEquals(2, count($persistedSettings), "Two Setting should have been saved");

        $autodelete = false;
        $mailInterval = false;
        foreach ($persistedSettings as $setting) {
            if ($setting->getName() == 'MailboxAutodelete'
                    && $setting->getType() == SettingEntity::TYPE_INTEGER) {
                $autodelete = true;
            }
            if ($setting->getName() == 'MailInterval'
                    && $setting->getType() == SettingEntity::TYPE_INTEGER) {
                $mailInterval = true;
            }
        }
        $this->assertEquals(true, $autodelete, "MailboxAutodelete was not created");
        $this->assertEquals(true, $mailInterval, "MailInterval was not created");

        $this->assertEquals(true, count($persistedGroups) == count(GroupEntity::getSystemNames()), "All system Groups should have been saved");

        $system = GroupEntity::getSystemNames();
        for ($i = 0; $i < count($persistedGroups); $i++)
            $this->assertEquals($system[$i], $persistedGroups[$i]->getName(), "System group not created");
    }

    public function testCheckDbActionCanBeAccessed()
    {
        ob_start();
        $this->dispatch('check-db');
        ob_end_clean();

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\console');
        $this->assertControllerClass('ConsoleController');
        $this->assertMatchedRouteName('check-db');
    }

    public function testCheckDbActionCreatesDeletesDocuments()
    {
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

        $this->assertEquals(2, count($createdDocs), "Two documents should have been created");
        $this->assertEquals(true, $createdDocs[0] instanceof ProfileDocument, "Profile is not created");
        $this->assertEquals(42, $createdDocs[0]->getId(), "Incorrect created doc id");
        $this->assertEquals(true, $createdDocs[1] instanceof SubscriptionDocument, "Subscription is not created");
        $this->assertEquals(42, $createdDocs[1]->getId(), "Incorrect created doc id");

        $this->assertEquals(2, count($removedDocs), "Two documents should have been removed");
        $this->assertEquals(true, $removedDocs[0] instanceof ProfileDocument, "Profile is not removed");
        $this->assertEquals(9000, $removedDocs[0]->getId(), "Incorrect removed doc id");
        $this->assertEquals(true, $removedDocs[1] instanceof SubscriptionDocument, "Subscription is not removed");
        $this->assertEquals(9000, $removedDocs[1]->getId(), "Incorrect removed doc id");
    }

    public function testCheckDbActionCorrectsEmail()
    {
        $docProfile = new ProfileDocument();
        $docProfile->setId(42);
        $docProfile->setClientEmail('bar');

        $this->repoProfile->expects($this->any())
                          ->method('find')
                          ->will($this->returnValue($docProfile));

        $this->repoProfile->expects($this->any())
                          ->method('findAll')
                          ->will($this->returnValue([]));

        $docSubscription = new SubscriptionDocument();
        $docSubscription->setId(42);
        $docSubscription->setClientEmail('bar');

        $this->repoSubscription->expects($this->any())
                              ->method('find')
                              ->will($this->returnValue($docSubscription));

        $this->repoSubscription->expects($this->any())
                              ->method('findAll')
                              ->will($this->returnValue([]));

        ob_start();
        $this->dispatch('check-db --repair');
        ob_end_clean();
        $this->assertResponseStatusCode(0);

        $this->assertEquals('foo', $docProfile->getClientEmail(), "Incorrect email was set");
        $this->assertEquals('foo', $docSubscription->getClientEmail(), "Incorrect email was set");
    }

    protected function setProp($object, $property, $value)
    {
        $class = new \ReflectionClass(get_class($object));

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
