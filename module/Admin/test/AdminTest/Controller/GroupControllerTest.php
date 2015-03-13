<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Group as GroupEntity;

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
                                 ->setMethods([ 'find', 'removeAll' ])
                                 ->getMock();

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
        $this->dispatch('/admin/group/group-table');

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
        $entity = new GroupEntity();
        $reflection = new \ReflectionClass(get_class($entity));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 42);

        $this->repoGroups->expects($this->any())
                         ->method('find')
                         ->will($this->returnCallback(function ($id) use (&$entity) {
                                if ($id == 42)
                                    return $entity;
                         }));

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

        $this->assertEquals(true, $entitiesRemoved, "All entities were not removed");
    }

    public function testDeleteGroupActionRemovesGroup()
    {
        $entity = new GroupEntity();
        $reflection = new \ReflectionClass(get_class($entity));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 42);

        $this->repoGroups->expects($this->any())
                         ->method('find')
                         ->will($this->returnCallback(function ($id) use (&$entity) {
                                if ($id == 42)
                                    return $entity;
                         }));

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

        $this->assertEquals($entity, $removed, "Wrong entity was removed");
    }
}
