<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Application\Model\Letter;

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
                           ->setMethods([ 'search', 'getLetter' ])
                           ->getMock();

        $this->imap->expects($this->any())
                   ->method('search')
                   ->will($this->returnValue([ 1, 2, 3 ]));

        $letter1 = new Letter(1);
        $letter2 = new Letter(2);
        $letter3 = new Letter(3);

        $letterCallback = function ($boxName, $uid) use ($letter1, $letter2, $letter3) {
            switch ($uid) {
                case 1: return $letter1;
                case 2: return $letter2;
                case 3: return $letter3;
            }
        };

        $this->imap->expects($this->any())
                   ->method('getLetter')
                   ->will($this->returnCallback($letterCallback));

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
        //$this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 3, "Invalid data returned");
        $this->assertEquals(1, $data['rows'][0]['uid'], "Invalid UID");
        $this->assertEquals(2, $data['rows'][1]['uid'], "Invalid UID");
        $this->assertEquals(3, $data['rows'][2]['uid'], "Invalid UID");
    }
/*
    public function testMailboxFormActionCreatesEntity()
    {
        $setting = new SettingEntity();
        $setting->setName('MailboxAutodelete');
        $setting->setType(SettingEntity::TYPE_INTEGER);
        $setting->setValueInteger(1);

        $this->repoSetting->expects($this->any())
                          ->method('findOneByName')
                          ->will($this->returnValue($setting));

        $this->dispatch('/admin/setting/mailbox-form');
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $dt = new \DateTime();
        $postParams = [
            'security' => $security,
            'autodelete' => 123,
        ];

        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $this->dispatch('/admin/setting/mailbox-form', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(true, $persisted instanceof SettingEntity, "Setting entity was not created");
        $this->assertEquals('MailboxAutodelete', $persisted->getName(), "MailboxAutodelete was not created");
        $this->assertEquals(123, $persisted->getValueInteger(), "MailboxAutodelete has incorrect value");
    }
*/
}
