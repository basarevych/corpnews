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

class LetterControllerTest extends AbstractHttpControllerTestCase
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


    public function testShowLetterActionCanBeAccessed()
    {
        $this->dispatch('/admin/letter/show-letter');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\letter');
        $this->assertControllerClass('LetterController');
        $this->assertMatchedRouteName('admin');
    }

    public function testShowLetterActionWorks()
    {
        $params = [
            'box' => 'box',
            'uid' => 42
        ];
        $this->dispatch('/admin/letter/show-letter', HttpRequest::METHOD_GET, $params);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(false, isset($data['error']) && $data['error'], "Result is not a success");
        $this->assertEquals('subject', $data['subject'], "Subject item is wrong");
        $this->assertEquals('foo', $data['html'], "HTML item is wrong");
        $this->assertEquals('<p>bar</p>', $data['text'], "Text item is wrong");
        $this->assertEquals('<div class="pre">log</div>', $data['log'], "Log item is wrong");
        $this->assertEquals('<div class="pre">headers' . "\n\n" . 'body</div>', $data['source'], "Source item is wrong");

        $subPage = $data['attachments'];
        $this->assertQueryContentRegexAtLeastOnce(
            'table tbody tr td img[src="/admin/letter/attachment?box=box&uid=42&cid=cid&filename=att1&preview=1"]',
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
        $this->dispatch('/admin/letter/attachment');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\letter');
        $this->assertControllerClass('LetterController');
        $this->assertMatchedRouteName('admin');
    }

    public function testAttachmentActionWorks()
    {
        $params = [
            'box' => 'box',
            'uid' => 42,
            'cid' => 'cid'
        ];
        $this->dispatch('/admin/letter/attachment', HttpRequest::METHOD_GET, $params);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $this->assertEquals($this->attBody, $response);
    }
}
