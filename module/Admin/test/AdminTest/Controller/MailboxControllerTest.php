<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Application\Model\Letter;
use Application\Model\Mailbox;

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

    public function testLetterActionCanBeAccessed()
    {
        $this->dispatch('/admin/mailbox/letter');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\mailbox');
        $this->assertControllerClass('MailboxController');
        $this->assertMatchedRouteName('admin');
    }

    public function testLetterActionWorks()
    {
        $params = [
            'box' => 'box',
            'uid' => 42
        ];
        $this->dispatch('/admin/mailbox/letter', HttpRequest::METHOD_GET, $params);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['success']) && $data['success'], "Result is not a success");
        $this->assertEquals('subject', $data['subject'], "Subject item is wrong");
        $this->assertEquals('foo', $data['html'], "HTML item is wrong");
        $this->assertEquals('<p>bar</p>', $data['text'], "Text item is wrong");
        $this->assertEquals('<div class="pre">log</div>', $data['log'], "Log item is wrong");
        $this->assertEquals('<div class="pre">headers' . "\n\n" . 'body</div>', $data['source'], "Source item is wrong");

        $subPage = $data['attachments'];
        $this->assertQueryContentRegexAtLeastOnce(
            'table tbody tr td img[src="/admin/mailbox/attachment?box=box&uid=42&cid=cid&filename=att1&preview=1"]',
            '/^$/m',
            false,
            $subPage
        );
        $this->assertQueryContentRegexAtLeastOnce(
            'table tbody tr td',
            '/^.*att1.*$/m',
            false,
            $subPage
        );
        $this->assertQueryContentRegexAtLeastOnce(
            'table tbody tr td',
            '/^.*application\\/octet-stream.*$/m',
            false,
            $subPage
        );
    }

    public function testAttachmentActionCanBeAccessed()
    {
        $this->dispatch('/admin/mailbox/attachment');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\mailbox');
        $this->assertControllerClass('MailboxController');
        $this->assertMatchedRouteName('admin');
    }

    public function testAttachmentActionWorks()
    {
        $params = [
            'box' => 'box',
            'uid' => 42,
            'cid' => 'cid'
        ];
        $this->dispatch('/admin/mailbox/attachment', HttpRequest::METHOD_GET, $params);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $this->assertEquals($this->attBody, $response);
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