<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Application\Model\Letter;
use Application\Model\Mailbox;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Template as TemplateEntity;

class MailboxControllerTest extends AbstractHttpControllerTestCase
{
    use \ApplicationTest\Controller\RegexAtLeastOnceTrait;

    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;

        $this->imap = $this->getMockBuilder('Application\Service\ImapClient')
                           ->setMethods([ 'search', 'getLetter', 'loadLetter', 'deleteLetter', 'moveLetter' ])
                           ->getMock();

        $this->imap->expects($this->any())
                   ->method('search')
                   ->will($this->returnValue([ 1, 2, 3 ]));

        $letter1 = new Letter(1);
        $letter2 = new Letter(2);
        $letter3 = new Letter(3);

        $letterMock = new Letter(42);
        $class = new \ReflectionClass(get_class($letterMock));

        $property = $class->getProperty('subject');
        $property->setAccessible(true);
        $property->setValue($letterMock, 'subject');

        $property = $class->getProperty('htmlMessage');
        $property->setAccessible(true);
        $property->setValue($letterMock, 'foo');

        $property = $class->getProperty('textMessage');
        $property->setAccessible(true);
        $property->setValue($letterMock, 'bar');

        $this->attBody = file_get_contents(__DIR__ . '/../../../../../public.prod/img/loader.gif');
        $property = $class->getProperty('attachments');
        $property->setAccessible(true);
        $property->setValue($letterMock, [
            [
                'cid'   => '<cid>',
                'name'  => 'att1',
                'type'  => 'application/octet-stream',
                'data'  => $this->attBody
            ]
        ]);

        $property = $class->getProperty('log');
        $property->setAccessible(true);
        $property->setValue($letterMock, 'log');

        $property = $class->getProperty('rawHeaders');
        $property->setAccessible(true);
        $property->setValue($letterMock, 'headers');

        $property = $class->getProperty('rawBody');
        $property->setAccessible(true);
        $property->setValue($letterMock, 'body');

        $letterCallback = function ($boxName, $uid) use ($letter1, $letter2, $letter3, $letterMock) {
            switch ($uid) {
                case 1: return $letter1;
                case 2: return $letter2;
                case 3: return $letter3;
                case 42: return $letterMock;
            }
        };

        $this->imap->expects($this->any())
                   ->method('getLetter')
                   ->will($this->returnCallback($letterCallback));

        $this->imap->expects($this->any())
                   ->method('loadLetter')
                   ->will($this->returnValue(true));

        $sl->setAllowOverride(true);
        $sl->setService('ImapClient', $this->imap);
    }


    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/mailbox');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\mailbox');
        $this->assertControllerClass('MailboxController');
        $this->assertMatchedRouteName('admin');
    }

    public function testIndexActionDisplaysMailboxes()
    {
        $this->dispatch('/admin/mailbox');

        $this->assertQueryContentRegexAtLeastOnce('.nav-tabs li a', '/^.*\s+Incoming\s+.*$/m');
        $this->assertQueryContentRegexAtLeastOnce('.nav-tabs li a', '/^.*\s+Replies\s+.*$/m');
        $this->assertQueryContentRegexAtLeastOnce('.nav-tabs li a', '/^.*\s+Bounces\s+.*$/m');

        $this->assertQuery('div#tab-Incoming  div#table-Incoming');
        $this->assertQueryContentRegexAtLeastOnce('div#tab-Incoming script', '/^.*\$\(\'#table-Incoming\'\)\.dynamicTable\(.*$/m');

        $this->assertQuery('div#tab-Replies div#table-Replies');
        $this->assertQueryContentRegexAtLeastOnce('div#tab-Replies script', '/^.*\$\(\'#table-Replies\'\)\.dynamicTable\(.*$/m');

        $this->assertQuery('div#tab-Bounces div#table-Bounces');
        $this->assertQueryContentRegexAtLeastOnce('div#tab-Bounces script', '/^.*\$\(\'#table-Bounces\'\)\.dynamicTable\(.*$/m');
    }

    public function testLetterTableActionCanBeAccessed()
    {
        $this->dispatch('/admin/mailbox/letter-table');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\mailbox');
        $this->assertControllerClass('MailboxController');
        $this->assertMatchedRouteName('admin');
    }

    public function testLetterTableActionSendsDescription()
    {  
        $this->dispatch('/admin/mailbox/letter-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testLetterTableActionSendsData()
    {
        $this->dispatch('/admin/mailbox/letter-table', HttpRequest::METHOD_GET, [ 'query' => 'data' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 3, "Invalid data returned");
        $this->assertEquals(1, $data['rows'][0]['uid'], "Invalid UID");
        $this->assertEquals(2, $data['rows'][1]['uid'], "Invalid UID");
        $this->assertEquals(3, $data['rows'][2]['uid'], "Invalid UID");
    }

    public function testCreateCampaignActionCanBeAccessed()
    {
        $this->dispatch('/admin/mailbox/create-campaign');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\mailbox');
        $this->assertControllerClass('MailboxController');
        $this->assertMatchedRouteName('admin');
    }

    public function testCreateCampaignActionWorks()
    {
        $sl = $this->getApplicationServiceLocator();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $persisted = [];
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted[] = $entity;
                 }));

        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);

        $getParams = [
            'box' => 'box',
            'uid' => 42
        ];
        $this->dispatch('/admin/mailbox/create-campaign', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'box' => 'box',
            'uid' => 42
        ];

        $this->dispatch('/admin/mailbox/create-campaign', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);
        $this->assertEquals(2, count($persisted), "Two entities should have been saved");

        $this->assertEquals(true, $persisted[0] instanceof CampaignEntity, "First entity is not a Campaign");
        $this->assertEquals('subject', $persisted[0]->getName(), "Campaign name is wrong");

        $this->assertEquals(true, $persisted[1] instanceof TemplateEntity, "Second entity is not a Template");
        $this->assertEquals('subject', $persisted[1]->getSubject(), "Template subject is wrong");
        $this->assertEquals('headers', $persisted[1]->getHeaders(), "Template headers are wrong");
        $this->assertEquals('body', $persisted[1]->getBody(), "Template body is wrong");
    }

    public function testDeleteLetterActionCanBeAccessed()
    {
        $this->dispatch('/admin/mailbox/delete-letter');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\mailbox');
        $this->assertControllerClass('MailboxController');
        $this->assertMatchedRouteName('admin');
    }

    public function testDeleteLetterActionWorks()
    {
        $getParams = [
            'box' => 'box',
            'uid' => 42
        ];
        $this->dispatch('/admin/mailbox/delete-letter', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'box' => 'box',
            'uid' => 42
        ];

        $deletedBox = null;
        $deletedUid = null;
        $this->imap->expects($this->any())
                   ->method('deleteLetter')
                   ->will($this->returnCallback(function ($box, $uid) use (&$deletedBox, &$deletedUid) {
                        $deletedBox = $box;
                        $deletedUid = $uid;
                    }));

        $this->dispatch('/admin/mailbox/delete-letter', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals('box', $deletedBox, "Box name is incorrect");
        $this->assertEquals(42, $deletedUid, "UID is incorrect");
    }

    public function testReanalyzeLetterActionCanBeAccessed()
    {
        $this->dispatch('/admin/mailbox/reanalyze-letter');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\mailbox');
        $this->assertControllerClass('MailboxController');
        $this->assertMatchedRouteName('admin');
    }

    public function testReanalyzeLetterActionWorks()
    {
        $getParams = [
            'box' => 'box',
            'uid' => 42
        ];
        $this->dispatch('/admin/mailbox/reanalyze-letter', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'box' => 'box',
            'uid' => 42
        ];

        $movedUid = null;
        $movedFrom = null;
        $movedTo = null;
        $this->imap->expects($this->any())
                   ->method('moveLetter')
                   ->will($this->returnCallback(function ($uid, $from, $to) use (&$movedUid, &$movedFrom, &$movedTo) {
                        $movedUid = $uid;
                        $movedFrom = $from;
                        $movedTo = $to;
                    }));

        $this->dispatch('/admin/mailbox/reanalyze-letter', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(42, $movedUid, "UID is incorrect");
        $this->assertEquals('box', $movedFrom, "From box name is incorrect");
        $this->assertEquals(Mailbox::NAME_INBOX, $movedTo, "To box name is incorrect");
    }
}
