<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use DataForm\Document\Profile as ProfileDocument;

class DocumentControllerQueryMock {
    protected $cursor;

    public function __construct($cursor) {
        $this->cursor = $cursor;
    }

    public function execute() {
        return $this->cursor;
    }
}

class DocumentControllerTest extends AbstractHttpControllerTestCase
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

        $config = $sl->get('Config');
        $config['corpnews'] = [
            'data_forms' => [
                'profile' => [
                    'title'     => 'Profile',
                    'url'       => '/data-form/profile',
                    'document'  => 'DataForm\Document\Profile',
                    'form'      => 'DataForm\Form\Profile',
                    'table'     => 'DataForm\Table\Profile',
                ],
            ],
        ];

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getDocumentCollection', 'getClassMetadata', 'getRepository' ])
                         ->getMock();

        $this->dm->expects($this->any())
                 ->method('getClassMetadata')
                 ->will($this->returnCallback(function ($name) {
                    return new \Doctrine\ODM\MongoDB\Mapping\ClassMetadata($name);
                 }));

        $this->repo = $this->getMockBuilder('DataForm\Document\ProfileRepository')
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
                 ->will($this->returnValue(new DocumentControllerQueryMock($this->cursor)));

        $sl->setAllowOverride(true);
        $sl->setService('Config', $config);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/document');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\document');
        $this->assertControllerClass('DocumentController');
        $this->assertMatchedRouteName('admin');
    }

    public function testIndexActionDisplaysDataForms()
    {
        $this->dispatch('/admin/document');

        $this->assertQueryContentRegexAtLeastOnce('a[href="/admin/document?name=profile"]', '/^.*Profile.*$/m');
    }

    public function testDocumentTableActionCanBeAccessed()
    {
        $this->dispatch(
            '/admin/document/document-table',
            HttpRequest::METHOD_GET,
            [ 'query' => 'describe', 'name' => 'profile' ]
        );

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\document');
        $this->assertControllerClass('DocumentController');
        $this->assertMatchedRouteName('admin');
    }
}
