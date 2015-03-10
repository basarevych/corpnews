<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Client as ClientEntity;

class ClientControllerQueryMock {
    public function getSingleScalarResult() {
        return 0;
    }
}

class ClientControllerTest extends AbstractHttpControllerTestCase
{
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
                ],
            ],
        ];

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getRepository', 'persist', 'remove', 'flush' ])
                         ->getMock();

        $this->repoClients = $this->getMockBuilder('Application\Entity\ClientRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'find', 'removeAll' ])
                                  ->getMock();

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Client', $this->repoClients ],
                 ]));

        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'setParameter', 'getQuery' ])
                         ->getMock();

        $this->em->expects($this->any())
                 ->method('createQueryBuilder')
                 ->will($this->returnValue($this->qb));

        $this->qb->expects($this->any())
                 ->method('getQuery')
                 ->will($this->returnValue(new ClientControllerQueryMock()));

        $this->dfm = $this->getMockBuilder('Application\Service\DataFormManager')
                          ->setMethods([ 'createClientDocuments', 'updateClientDocuments', 'deleteClientDocuments', 'deleteAllDocuments' ])
                          ->getMock();

        $this->dfm->setServiceLocator($sl);

        $sl->setAllowOverride(true);
        $sl->setService('Config', $config);
        $sl->setService('DataFormManager', $this->dfm);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/client');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\client');
        $this->assertControllerClass('ClientController');
        $this->assertMatchedRouteName('admin');
    }

    public function testClientTableActionCanBeAccessed()
    {
        $this->dispatch('/admin/client/client-table');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\client');
        $this->assertControllerClass('ClientController');
        $this->assertMatchedRouteName('admin');
    }

    public function testClientTableActionSendsDescription()
    {  
        $this->dispatch('/admin/client/client-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testClientTableActionSendsData()
    {
        $this->infrastructure = new ORMInfrastructure([
            'Application\Entity\Client'
        ]);
        $this->repository = $this->infrastructure->getRepository(
            'Application\Entity\Client'
        );
        $this->em = $this->infrastructure->getEntityManager();

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);

        $a = new ClientEntity();
        $a->setEmail('foobar');

        $this->infrastructure->import([ $a ]);

        $this->dispatch('/admin/client/client-table', HttpRequest::METHOD_GET, [ 'query' => 'data' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 1, "Invalid data returned");
        $this->assertEquals(1, $data['rows'][0]['id'], "Invalid ID");
    }

    public function testEditClientActionCanBeAccessed()
    {
        $this->dispatch('/admin/client/edit-client');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\client');
        $this->assertControllerClass('ClientController');
        $this->assertMatchedRouteName('admin');
    }

    public function testEditClientActionCreatesClient()
    {
        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $reflection = new \ReflectionClass(get_class($entity));
                    $property = $reflection->getProperty('id');
                    $property->setAccessible(true);
                    $property->setValue($entity, 42);

                    $persisted = $entity;
                 }));

        $createdDocsId = null;
        $this->dfm->expects($this->any())
                  ->method('createClientDocuments')
                  ->will($this->returnCallback(function ($entity) use (&$createdDocsId) {
                        $createdDocsId = $entity->getId();
                  }));

        $getParams = [ ];
        $this->dispatch('/admin/client/edit-client', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'email' => 'test@example.com'
        ];

        $this->dispatch('/admin/client/edit-client', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals('test@example.com', $persisted->getEmail(), "Email is incorrect");
        $this->assertEquals(42, $createdDocsId, "Documents were created for wrong entity");
    }

    public function testEditClientActionUpdatesClient()
    {
        $client = new ClientEntity();
        $reflection = new \ReflectionClass(get_class($client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($client, 42);

        $this->repoClients->expects($this->any())
                          ->method('find')
                          ->will($this->returnCallback(function ($id) use (&$client) {
                                if ($id == 42)
                                    return $client;
                          }));

        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $updatedDocsId = null;
        $this->dfm->expects($this->any())
                  ->method('updateClientDocuments')
                  ->will($this->returnCallback(function ($entity) use (&$updatedDocsId) {
                        $updatedDocsId = $entity->getId();
                  }));

        $getParams = [
            'id' => 42,
        ];
        $this->dispatch('/admin/client/edit-client', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => 42,
            'email' => 'test@example.com'
        ];

        $this->dispatch('/admin/client/edit-client', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals('test@example.com', $persisted->getEmail(), "Email is incorrect");
        $this->assertEquals(42, $updatedDocsId, "Documents were updated for wrong entity");
    }

    public function testDeleteClientActionRemovesAll()
    {
        $entitiesRemoved = false;
        $this->repoClients->expects($this->any())
                          ->method('removeAll')
                          ->will($this->returnCallback(function () use (&$entitiesRemoved) {
                            $entitiesRemoved = true;
                          }));

        $documentsRemoved = false;
        $this->dfm->expects($this->any())
                  ->method('deleteAllDocuments')
                  ->will($this->returnCallback(function () use (&$documentsRemoved) {
                    $documentsRemoved = true;
                  }));

        $getParams = [
            'id' => '_all',
        ];
        $this->dispatch('/admin/client/delete-client', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => '_all',
        ];

        $this->dispatch('/admin/client/delete-client', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(true, $entitiesRemoved, "All entities were not removed");
        $this->assertEquals(true, $documentsRemoved, "All documents were not removed");
    }

    public function testDeleteClientActionRemovesClient()
    {
        $client = new ClientEntity();
        $reflection = new \ReflectionClass(get_class($client));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($client, 42);

        $this->repoClients->expects($this->any())
                          ->method('find')
                          ->will($this->returnCallback(function ($id) use (&$client) {
                                if ($id == 42)
                                    return $client;
                          }));

        $removed = null;
        $this->em->expects($this->any())
                 ->method('remove')
                 ->will($this->returnCallback(function ($entity) use (&$removed) {
                    $removed = $entity;
                 }));

        $removedDocsId = null;
        $this->dfm->expects($this->any())
                  ->method('deleteClientDocuments')
                  ->will($this->returnCallback(function ($entity) use (&$removedDocsId) {
                        $removedDocsId = $entity->getId();
                  }));

        $getParams = [
            'id' => 42,
        ];
        $this->dispatch('/admin/client/delete-client', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => 42,
        ];

        $this->dispatch('/admin/client/delete-client', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals($client, $removed, "Wrong entity was removed");
        $this->assertEquals(42, $removedDocsId, "Docs of wrong ID were removed");
    }
}
