<?php

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Application\Entity\Setting as SettingEntity;
use Application\Model\Mailbox;
use Application\Model\Letter;

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

        $this->repoSetting = $this->getMockBuilder('Application\Repository\Setting')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findOneByName', 'getValue' ])
                                  ->getMock();

        $this->autodelete = 30;
        $this->repoSetting->expects($this->any())
                          ->method('getValue')
                          ->will($this->returnValue($this->autodelete));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Setting', $this->repoSetting ],
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
        $class = new \ReflectionClass(get_class($letterMock));

        $property = $class->getProperty('subject');
        $property->setAccessible(true);
        $property->setValue($letterMock, 'subject');

        $this->imap->expects($this->any())
                   ->method('getLetter')
                   ->will($this->returnValue($letterMock));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
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
