<?php

namespace ApplicationTest\TaskDaemon;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Model\Mailbox;
use Application\Model\Letter;
use Application\TaskDaemon\CheckEmail as CheckEmailTask;

class CheckEmailConnectionMock
{
    public function close()
    {
    }

    public function connect()
    {
    }
}

class CheckEmailTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->getApplication()->bootstrap();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getConnection', 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->em->expects($this->any())
                 ->method('getConnection')
                 ->will($this->returnValue(new CheckEmailConnectionMock()));

        $this->repoSetting = $this->getMockBuilder('Application\Entity\SettingRepository')
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
        $this->setProp($letterMock, 'subject', 'subject');

        $this->imap->expects($this->any())
                   ->method('getLetter')
                   ->will($this->returnValue($letterMock));

        $this->logger = $this->getMockBuilder('Application\Service\Logger')
                             ->disableOriginalConstructor()
                             ->setMethods([ 'log' ])
                             ->getMock();

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $this->sl->setService('ImapClient', $this->imap);
        $this->sl->setService('Logger', $this->logger);
    }

    public function testTaskCreatesMailboxes()
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

        $task = new CheckEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run();

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

    public function testTaskSearchesForLetters()
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

        $task = new CheckEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run();

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

    protected function setProp($object, $property, $value)
    {
        $class = new \ReflectionClass(get_class($object));

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
} 
