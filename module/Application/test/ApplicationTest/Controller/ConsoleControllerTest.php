<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Application\Entity\Setting as SettingEntity;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Group as GroupEntity;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Letter as LetterEntity;
use Application\Entity\Template as TemplateEntity;
use Application\Model\Mailbox;
use Application\Model\Letter;
use DataForm\Document\Profile as ProfileDocument;

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
                                 ->setMethods([ 'find', 'findAll', 'findWithoutLetters', 'findWithFailedLetters' ])
                                 ->getMock();

        $a = new ClientEntity();
        $a->setEmail('foo@bar');

        $this->repoClient->expects($this->any())
                         ->method('findWithoutLetters')
                         ->will($this->returnValue([ $a ]));

        $b = new ClientEntity();
        $b->setEmail('baz@bar');

        $this->repoClient->expects($this->any())
                         ->method('findWithFailedLetters')
                         ->will($this->returnValue([ $b ]));

        $this->repoGroup = $this->getMockBuilder('Application\Client\GroupRepository')
                                ->disableOriginalConstructor()
                                ->setMethods([ 'findOneByName' ])
                                ->getMock();

        $this->repoCampaign = $this->getMockBuilder('Application\Client\CampaignRepository')
                                   ->disableOriginalConstructor()
                                   ->setMethods([ 'findBy' ])
                                   ->getMock();

        $template = new TemplateEntity();
        $template->setMessageId('mid');
        $template->setSubject('subject');
        $template->setHeaders('header: foo');
        $template->setBody('body');
        $this->setProp($template, 'id', 42);

        $campaign = new CampaignEntity();
        $campaign->setName('foobar');
        $campaign->setStatus(CampaignEntity::STATUS_CREATED);
        $this->setProp($campaign, 'id', 42);

        $campaign->addTemplate($template);
        $template->setCampaign($campaign);

        $this->repoCampaign->expects($this->any())
                           ->method('findBy')
                           ->will($this->returnValue([ $campaign ]));

        $this->autodelete = 30;
        $this->repoSetting->expects($this->any())
                          ->method('getValue')
                          ->will($this->returnValue($this->autodelete));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Setting', $this->repoSetting ],
                    [ 'Application\Entity\Client', $this->repoClient ],
                    [ 'Application\Entity\Group', $this->repoGroup ],
                    [ 'Application\Entity\Campaign', $this->repoCampaign ],
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

        $this->imap = $this->getMockBuilder('Application\Service\ImapClient')
                           ->setMethods([ 'getMailboxes', 'createMailbox', 'search', 'getLetter', 'deleteLetter', 'moveLetter' ])
                           ->getMock();

        $info = new \StdClass();
        $info->name = Mailbox::NAME_INBOX;
        $box = new Mailbox($info);

        $this->imap->expects($this->any())
                   ->method('getMailboxes')
                   ->will($this->returnValue([ $box ]));

        $letterMock = new Letter(42);
        $this->setProp($letterMock, 'subject', 'subject');

        $this->imap->expects($this->any())
                   ->method('getLetter')
                   ->will($this->returnValue($letterMock));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
        $sl->setService('ImapClient', $this->imap);
    }

    public function testCronActionCanBeAccessed()
    {
        $this->dispatch('cron');

        $this->assertModuleName('application');
        $this->assertControllerName('application\controller\console');
        $this->assertControllerClass('ConsoleController');
        $this->assertMatchedRouteName('cron');
    }

    public function testCronActionCreatesMailboxes()
    {
        $created = [];
        $createCallback = function ($name) use (&$created) {
            $info = new \StdClass();
            $info->name = $name;

            $box = new Mailbox($info);
            $created[] = $box;

            return $box;
        };

        $this->imap->expects($this->any())
                   ->method('createMailbox')
                   ->will($this->returnCallback($createCallback));

        $this->dispatch('cron');
        $this->assertResponseStatusCode(0);

        $incoming = false;
        $replies = false;
        $bounces = false;
        foreach ($created as $box) {
            switch ($box->getName()) {
                case Mailbox::NAME_INCOMING:    $incoming = true; break;
                case Mailbox::NAME_REPLIES:     $replies = true; break;
                case Mailbox::NAME_BOUNCES:     $bounces = true; break;
            }
        }

        $this->assertEquals(true, $incoming, "Incoming mailbox was not created");
        $this->assertEquals(true, $replies, "Replies mailbox was not created");
        $this->assertEquals(true, $bounces, "Bounces mailbox was not created");
    }

    public function testCronActionSearchesForLetters()
    {
        $searched = [];
        $searchCallback = function ($name, $sortColumn, $sortDir, $criteria) use (&$searched) {
            $searched[] = [
                'name' => $name,
                'criteria' => $criteria,
            ];
            return [ 42 ];
        };

        $oldDate = new \DateTime();
        $oldDate->setTimezone(new \DateTimeZone('GMT'));
        $oldDate->sub(new \DateInterval('P' . $this->autodelete . 'D'));
        $oldDate->setTime(0, 0, 0);
        $oldDate = \Application\Tool\Date::toImapString($oldDate);

        $this->imap->expects($this->any())
                   ->method('search')
                   ->will($this->returnCallback($searchCallback));

        $deletedBox = null;
        $deletedUid = null;
        $deleteCallback = function ($box, $uid) use (&$deletedBox, &$deletedUid) {
            $deletedBox = $box;
            $deletedUid = $uid;
        };

        $this->imap->expects($this->any())
                   ->method('deleteLetter')
                   ->will($this->returnCallback($deleteCallback));

        $movedUid = null;
        $movedFrom = null;
        $movedTo = null;
        $moveCallback = function ($uid, $from, $to) use (&$movedUid, &$movedFrom, &$movedTo) {
            $movedUid = $uid;
            $movedFrom = $from;
            $movedTo = $to;
        };

        $this->imap->expects($this->any())
                   ->method('moveLetter')
                   ->will($this->returnCallback($moveCallback));

        $this->dispatch('cron');
        $this->assertResponseStatusCode(0);

        $this->assertEquals(2, count($searched), "Wrong number of search request was made");
        $this->assertEquals(
            [
                'name' => Mailbox::NAME_INBOX,
                'criteria' => 'BEFORE "' . $oldDate . '"',
            ],
            $searched[0],
            "Delete search was wrong"
        );
        $this->assertEquals(
            [
                'name' => Mailbox::NAME_INBOX,
                'criteria' => 'SINCE "' . $oldDate . '"',
            ],
            $searched[1],
            "Delete search was wrong"
        );

        $this->assertEquals(Mailbox::NAME_INBOX, $deletedBox, "Box of deleted message is wrong");
        $this->assertEquals(42, $deletedUid, "UID of deleted message is wrong");

        $this->assertEquals(42, $movedUid, "UID of moved message is wrong");
        $this->assertEquals(Mailbox::NAME_INBOX, $movedFrom, "Source box of moved message is wrong");
        $this->assertEquals(Mailbox::NAME_INCOMING, $movedTo, "Destination box of moved message is wrong");
    }

    public function testCronActionFindsNewLetters()
    {
        $persisted = [];
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted[] = $entity;
                 }));

        $this->dispatch('cron');
        $this->assertResponseStatusCode(0);

        $this->assertEquals(2, count($persisted), "Two entities should have been persisted");
        $this->assertEquals('foo@bar', $persisted[0]->getToAddress(), "Email is wrong");
        $this->assertEquals('baz@bar', $persisted[1]->getToAddress(), "Email is wrong");
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
