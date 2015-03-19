<?php

namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Request as HttpRequest;
use Zend\Json\Json;
use Zend\Dom\Query;
use Application\Document\Syslog as SyslogDocument;

class SyslogQueryMock {
    protected $cursor;

    public function __construct($cursor) {
        $this->cursor = $cursor;
    }

    public function execute() {
        return $this->cursor;
    }
}

class SyslogControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        \Locale::setDefault('en_US');

        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getDocumentCollection', 'getClassMetadata', 'getRepository' ])
                         ->getMock();

        $this->dm->expects($this->any())
                 ->method('getClassMetadata')
                 ->will($this->returnCallback(function ($name) {
                    return new \Doctrine\ODM\MongoDB\Mapping\ClassMetadata($name);
                 }));

        $this->repo = $this->getMockBuilder('Application\Document\SyslogRepository')
                           ->disableOriginalConstructor()
                           ->setMethods([ 'removeAll' ])
                           ->getMock();

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->repo));

        $this->qb = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Builder')
                         ->setConstructorArgs([ $this->dm, 'DataForm\Document\Profile' ])
                         ->setMethods([ 'expr', 'getQuery', 'sort' ])
                         ->getMock();

        $this->dm->expects($this->any())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($this->qb));

        $this->expr = $this->getMockBuilder('Doctrine\ODM\MongoDB\Query\Expr')
                           ->setConstructorArgs([ $this->dm ])
                           ->setMethods([ 'getQuery', 'equals', 'range', 'gte', 'lte', 'exists' ])
                           ->getMock();

        $this->qb->expects($this->any())
                 ->method('expr')
                 ->will($this->returnValue($this->expr));

        $this->cursor = $this->getMockBuilder('Doctrine\ODM\MongoDB\Cursor')
                             ->disableOriginalConstructor()
                             ->setMethods([ 'valid', 'count', 'current', 'getMongoCursor', 'skip', 'limit' ])
                             ->getMock();

        $this->mongoCursor = $this->getMockBuilder('MongoCursor')
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->cursor->expects($this->any())
                     ->method('getMongoCursor')
                     ->will($this->returnValue($this->mongoCursor));

        $this->qb->expects($this->any())
                 ->method('getQuery')
                 ->will($this->returnValue(new SyslogQueryMock($this->cursor)));

        $sl->setAllowOverride(true);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/syslog');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\syslog');
        $this->assertControllerClass('SyslogController');
        $this->assertMatchedRouteName('admin');
    }

    public function testSyslogTableActionCanBeAccessed()
    {
        $this->dispatch('/admin/syslog/syslog-table');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\syslog');
        $this->assertControllerClass('SyslogController');
        $this->assertMatchedRouteName('admin');
    }

    public function testSyslogTableActionSendsDescription()
    {  
        $this->dispatch('/admin/syslog/syslog-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testSyslogTableActionSendsData()
    {
        $dt = new \DateTime();
        $doc = new SyslogDocument();
        $doc->setWhenHappened($dt);
        $doc->setLevel('foo');
        $doc->setMessage('bar');

        $fixture = [ $doc ];

        $i = 0; $count = count($fixture);
        $this->cursor->expects($this->any())
                     ->method('valid')
                     ->will($this->returnCallback(function () use (&$i, $count) {
                        return $i < $count;
                     }));

        $this->cursor->expects($this->any())
                     ->method('count')
                     ->will($this->returnValue($count));

        $this->cursor->expects($this->any())
                     ->method('current')
                     ->will($this->returnCallback(function () use (&$i, $fixture) {
                        return $fixture[$i++];
                     }));

        $this->dispatch('/admin/syslog/syslog-table', HttpRequest::METHOD_GET, [ 'query' => 'data' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 1, "Invalid data returned");
        $this->assertEquals($dt->getTimestamp(), $data['rows'][0]['when_happened'], "Invalid date");
        $this->assertEquals('LEVEL_FOO', $data['rows'][0]['level'], "Invalid level");
        $this->assertEquals('bar', $data['rows'][0]['message'], "Invalid message");
    }

    public function testClearSyslogActionCanBeAccessed()
    {
        $this->dispatch('/admin/syslog/clear-syslog');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\syslog');
        $this->assertControllerClass('SyslogController');
        $this->assertMatchedRouteName('admin');
    }

    public function testClearSyslogActionRemovesAll()
    {
        $allRemoved = false;
        $this->repo->expects($this->any())
                   ->method('removeAll')
                   ->will($this->returnCallback(function () use (&$allRemoved) {
                        $allRemoved = true;
                   }));

        $this->dispatch('/admin/syslog/clear-syslog');
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => '_all',
        ];

        $this->dispatch('/admin/syslog/clear-syslog', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(true, $allRemoved, "All entities were not removed");
    }
}
