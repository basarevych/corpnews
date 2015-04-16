<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Group as GroupEntity;
use DataForm\Document\Profile as ProfileDocument;

class ImportExportControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->repoGroups = $this->getMockBuilder('Application\Entity\ClientRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'find', 'findBy' ])
                                 ->getMock();

        $this->group = new GroupEntity();
        $this->group->setName('a');

        $reflection = new \ReflectionClass(get_class($this->group));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->group, 9000);

        $this->repoGroups->expects($this->any())
                         ->method('find')
                         ->will($this->returnValue($this->group));

        $this->repoGroups->expects($this->any())
                         ->method('findBy')
                         ->will($this->returnValue([ $this->group ]));

        $this->repoClients = $this->getMockBuilder('Application\Entity\ClientRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findByGroupIds', 'findOneByEmail' ])
                                  ->getMock();

        $this->client = new ClientEntity();
        $this->client->setEmail('foo@bar');

        $reflection = new \ReflectionClass(get_class($this->client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->client, 42);

        $this->repoClients->expects($this->any())
                          ->method('findByGroupIds')
                          ->will($this->returnValue([ $this->client ]));

        $this->repoClients->expects($this->any())
                          ->method('findOneByEmail')
                          ->will($this->returnValue($this->client));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Client', $this->repoClients ],
                    [ 'Application\Entity\Group', $this->repoGroups ],
                 ]));

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->repoProfiles = $this->getMockBuilder('DataForm\Document\ProfileRepository')
                                   ->disableOriginalConstructor()
                                   ->setMethods([ 'find' ])
                                   ->getMock();

        $this->profile = new ProfileDocument();
        $this->profile->setLastName('Lastname');

        $this->repoProfiles->expects($this->any())
                           ->method('find')
                           ->will($this->returnValue($this->profile));

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->repoProfiles));

        $this->sl = $this->getApplicationServiceLocator();
        $session = $this->sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;
        $cnt->import = [
            'groups' => '9000',
            'fields' => 'email,profile-last_name',
            'rows' => [
                [
                    'email' => 'foo@bar',
                    'docs' => [ 'profile' => $this->profile ],
                ],
            ],
        ];

        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $this->sl->setService('doctrine.documentmanager.odm_default', $this->dm);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testDownloadActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export/download');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testGenerateCsvActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export/generate-csv');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testGenerateCsvActionGeneratesFile()
    {
        $params = [
            'fields'    => 'email,profile-last_name',
            'separator' => 'comma',
            'ending'    => 'unix',
            'encoding'  => 'utf-8',
            'groups'    => 9000,
        ];
        $this->dispatch('/admin/import-export/generate-csv', HttpRequest::METHOD_GET, $params);
        $this->assertResponseStatusCode(200);

        $file = $this->getResponse()->getContent();
        $lines = explode("\n", $file);

        $this->assertEquals(3, count($lines), "Two lines should be generated");
        $this->assertEquals('"email","profile / last_name"', $lines[0], "Header is wrong");
        $this->assertEquals('"foo@bar","Lastname"', $lines[1], "Data is wrong");
    }

    public function testImportPreviewActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export/import-preview');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testAcceptImportActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export/accept-import');
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testAcceptImportActionWorks()
    {
        $persistedEntities = [];
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persistedEntities) {
                    $reflection = new \ReflectionClass(get_class($entity));
                    $property = $reflection->getProperty('id');
                    $property->setAccessible(true);
                    $property->setValue($entity, 42);

                    $persistedEntities[] = $entity;
                 }));

        $persistedDocuments = [];
        $this->dm->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($document) use (&$persistedDocuments) {
                    $persistedDocuments[] = $document;
                 }));

        $this->dispatch('/admin/import-export/accept-import');

        $this->assertEquals(1, count($persistedEntities), "One entity should have been persisted");
        $this->assertEquals('foo@bar', $persistedEntities[0]->getEmail(), "Email is wrong");

        $groups = $persistedEntities[0]->getGroups()->toArray();
        $this->assertEquals(1, count($groups), "Client should be in one group");
        $this->assertEquals(9000, $groups[0]->getId(), "Client in the wrong group");

        $this->assertEquals(1, count($persistedDocuments), "One document should have been persisted");
        $this->assertEquals(42, $persistedDocuments[0]->getId(), "ID is wrong");
        $this->assertEquals('foo@bar', $persistedDocuments[0]->getClientEmail(), "Email is wrong");
        $this->assertEquals('Lastname', $persistedDocuments[0]->getLastName(), "Last name is wrong");
    }

    public function testCancelImportActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export/cancel-import');
        $this->assertResponseStatusCode(302);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testCancelImportActionWorks()
    {
        $this->dispatch('/admin/import-export/cancel-import');

        $session = $this->sl->get('Session');
        $cnt = $session->getContainer();

        $this->assertEquals(false, $cnt->offsetExists('import'), "Import should be cleared");
    }

    public function testPreviewTableActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export/preview-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testPreviewTableActionSendsDescription()
    {  
        $this->dispatch('/admin/import-export/preview-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testPreviewTableActionSendsData()
    {
        $this->dispatch('/admin/import-export/preview-table', HttpRequest::METHOD_GET, [ 'query' => 'data' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 1, "Invalid data returned");
        $this->assertEquals('foo@bar', $data['rows'][0]['email'], "Invalid email");
    }
}
