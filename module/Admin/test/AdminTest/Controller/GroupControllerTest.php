<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Group as GroupEntity;
use Application\Entity\Client as ClientEntity;

class GroupControllerQueryMock {
    public function getSingleScalarResult() {
        return 0;
    }
}

class GroupControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getRepository', 'persist', 'remove', 'flush' ])
                         ->getMock();

        $this->repoGroups = $this->getMockBuilder('Application\Entity\GroupRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'find', 'findAll', 'removeAll' ])
                                 ->getMock();

        $this->group1 = new GroupEntity();
        $this->group1->setName('Group 1');
        $reflection = new \ReflectionClass(get_class($this->group1));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->group1, 42);

        $this->group2 = new GroupEntity();
        $this->group2->setName('Group 2');
        $property->setValue($this->group2, 9000);

        $this->client = new ClientEntity();
        $this->client->setEmail('foo@bar');

        $this->group1->addClient($this->client);
        $this->client->addGroup($this->group1);

        $this->group2->addClient($this->client);
        $this->client->addGroup($this->group2);

        $this->repoGroups->expects($this->any())
                         ->method('find')
                         ->will($this->returnCallback(function ($id) {
                                if ($id == 42)
                                    return $this->group1;
                                if ($id == 9000)
                                    return $this->group2;
                                return null;
                         }));

        $this->repoGroups->expects($this->any())
                         ->method('findAll')
                         ->will($this->returnCallback(function () {
                                return [ $this->group1, $this->group2 ];
                         }));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Group', $this->repoGroups ],
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
                 ->will($this->returnValue(new GroupControllerQueryMock()));

        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/group');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\group');
        $this->assertControllerClass('GroupController');
        $this->assertMatchedRouteName('admin');
    }

    public function testGroupTableActionCanBeAccessed()
    {
        $this->dispatch('/admin/group/group-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\group');
        $this->assertControllerClass('GroupController');
        $this->assertMatchedRouteName('admin');
    }

    public function testGroupTableActionSendsDescription()
    {  
        $this->dispatch('/admin/group/group-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testGroupTableActionSendsData()
    {
        $this->infrastructure = new ORMInfrastructure([
            'Application\Entity\Client',
            'Application\Entity\Group'
        ]);
        $this->repository = $this->infrastructure->getRepository(
            'Application\Entity\Group'
        );
        $this->em = $this->infrastructure->getEntityManager();

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);

        $a = new GroupEntity();
        $a->setName('foobar');

        $this->infrastructure->import([ $a ]);

        $this->dispatch('/admin/group/group-table', HttpRequest::METHOD_GET, [ 'query' => 'data' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 1, "Invalid data returned");
        $this->assertEquals(1, $data['rows'][0]['id'], "Invalid ID");
    }

    public function testEditGroupActionCanBeAccessed()
    {
        $this->dispatch('/admin/group/edit-group');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\group');
        $this->assertControllerClass('GroupController');
        $this->assertMatchedRouteName('admin');
    }

    public function testEditGroupActionCreatesGroup()
    {
        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $getParams = [ ];
        $this->dispatch('/admin/group/edit-group', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'name' => 'test'
        ];

        $this->dispatch('/admin/group/edit-group', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals('test', $persisted->getName(), "Name is incorrect");
    }

    public function testEditGroupActionUpdatesGroup()
    {
        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $getParams = [
            'id' => 42,
        ];
        $this->dispatch('/admin/group/edit-group', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => 42,
            'name' => 'test'
        ];

        $this->dispatch('/admin/group/edit-group', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals('test', $persisted->getName(), "Name is incorrect");
    }

    public function testEmptyGroupActionCanBeAccessed()
    {
        $this->dispatch('/admin/group/empty-group', HttpRequest::METHOD_GET, [ 'id' => 42 ]);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\group');
        $this->assertControllerClass('GroupController');
        $this->assertMatchedRouteName('admin');
    }

    public function testEmptyGroupActionClearsAll()
    {
        $getParams = [
            'id' => '_all',
        ];
        $this->dispatch('/admin/group/empty-group', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => '_all',
        ];

        $this->dispatch('/admin/group/empty-group', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(0, count($this->group1->getClients()), "Group 1 was not cleared");
        $this->assertEquals(0, count($this->group2->getClients()), "Group 2 was not cleared");
    }

    public function testEmptyGroupActionClearsGroup()
    {
        $getParams = [
            'id' => 42,
        ];
        $this->dispatch('/admin/group/empty-group', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => 42,
        ];

        $this->dispatch('/admin/group/empty-group', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(0, count($this->group1->getClients()), "Group 1 was not cleared");
        $this->assertGreaterThan(0, count($this->group2->getClients()), "Group 2 should not be cleared");
    }

    public function testDeleteGroupActionCanBeAccessed()
    {
        $this->dispatch('/admin/group/delete-group', HttpRequest::METHOD_GET, [ 'id' => 42 ]);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\group');
        $this->assertControllerClass('GroupController');
        $this->assertMatchedRouteName('admin');
    }

    public function testDeleteGroupActionRemovesAll()
    {
        $entitiesRemoved = false;
        $this->repoGroups->expects($this->any())
                         ->method('removeAll')
                         ->will($this->returnCallback(function () use (&$entitiesRemoved) {
                            $entitiesRemoved = true;
                         }));

        $getParams = [
            'id' => '_all',
        ];
        $this->dispatch('/admin/group/delete-group', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => '_all',
        ];

        $this->dispatch('/admin/group/delete-group', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

//        $this->assertEquals(true, $entitiesRemoved, "All entities were not removed");
    }

    public function testDeleteGroupActionRemovesGroup()
    {
        $removed = null;
        $this->em->expects($this->any())
                 ->method('remove')
                 ->will($this->returnCallback(function ($entity) use (&$removed) {
                    $removed = $entity;
                 }));

        $getParams = [
            'id' => 42,
        ];
        $this->dispatch('/admin/group/delete-group', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => 42,
        ];

        $this->dispatch('/admin/group/delete-group', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

//        $this->assertEquals(42, $removed->getId(), "Wrong entity was removed");
    }
}
