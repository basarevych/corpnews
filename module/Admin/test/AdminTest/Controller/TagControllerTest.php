<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Tag as TagEntity;

class TagControllerQueryMock {
    public function getSingleScalarResult() {
        return 0;
    }
}

class TagControllerTest extends AbstractHttpControllerTestCase
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

        $this->repoTags = $this->getMockBuilder('Application\Entity\TagRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'find', 'removeAll' ])
                                 ->getMock();

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Tag', $this->repoTags ],
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
                 ->will($this->returnValue(new TagControllerQueryMock()));

        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/tag');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\tag');
        $this->assertControllerClass('TagController');
        $this->assertMatchedRouteName('admin');
    }

    public function testTagTableActionCanBeAccessed()
    {
        $this->dispatch('/admin/tag/tag-table');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\tag');
        $this->assertControllerClass('TagController');
        $this->assertMatchedRouteName('admin');
    }

    public function testTagTableActionSendsDescription()
    {  
        $this->dispatch('/admin/tag/tag-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testTagTableActionSendsData()
    {
        $this->infrastructure = new ORMInfrastructure([
            'Application\Entity\Tag'
        ]);
        $this->repository = $this->infrastructure->getRepository(
            'Application\Entity\Tag'
        );
        $this->em = $this->infrastructure->getEntityManager();

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);

        $a = new TagEntity();
        $a->setName('foobar');
        $a->setDescr('Description');

        $this->infrastructure->import([ $a ]);

        $this->dispatch('/admin/tag/tag-table', HttpRequest::METHOD_GET, [ 'query' => 'data' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 1, "Invalid data returned");
        $this->assertEquals(1, $data['rows'][0]['id'], "Invalid ID");
    }

    public function testEditTagActionCanBeAccessed()
    {
        $this->dispatch('/admin/tag/edit-tag');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\tag');
        $this->assertControllerClass('TagController');
        $this->assertMatchedRouteName('admin');
    }

    public function testEditTagActionCreatesTag()
    {
        $persisted = null;
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted = $entity;
                 }));

        $getParams = [ ];
        $this->dispatch('/admin/tag/edit-tag', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'name' => 'test',
            'descr' => 'descr',
        ];

        $this->dispatch('/admin/tag/edit-tag', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals('test', $persisted->getName(), "Name is incorrect");
        $this->assertEquals('descr', $persisted->getDescr(), "Description is wrong");
    }

    public function testEditTagActionUpdatesTag()
    {
        $entity = new TagEntity();
        $reflection = new \ReflectionClass(get_class($entity));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 42);

        $this->repoTags->expects($this->any())
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
        $this->dispatch('/admin/tag/edit-tag', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => 42,
            'name' => ' test ',
            'descr' => ' descr ',
        ];

        $this->dispatch('/admin/tag/edit-tag', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals('test', $persisted->getName(), "Name is incorrect");
        $this->assertEquals('descr', $persisted->getDescr(), "Descr is incorrect");
    }

    public function testDeleteTagActionCanBeAccessed()
    {
        $this->dispatch('/admin/tag/delete-tag');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\tag');
        $this->assertControllerClass('TagController');
        $this->assertMatchedRouteName('admin');
    }

    public function testDeleteTagActionRemovesAll()
    {
        $entitiesRemoved = false;
        $this->repoTags->expects($this->any())
                         ->method('removeAll')
                         ->will($this->returnCallback(function () use (&$entitiesRemoved) {
                            $entitiesRemoved = true;
                         }));

        $getParams = [
            'id' => '_all',
        ];
        $this->dispatch('/admin/tag/delete-tag', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => '_all',
        ];

        $this->dispatch('/admin/tag/delete-tag', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(true, $entitiesRemoved, "All entities were not removed");
    }

    public function testDeleteTagActionRemovesTag()
    {
        $entity = new TagEntity();
        $reflection = new \ReflectionClass(get_class($entity));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, 42);

        $this->repoTags->expects($this->any())
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
        $this->dispatch('/admin/tag/delete-tag', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => 42,
        ];

        $this->dispatch('/admin/tag/delete-tag', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals($entity, $removed, "Wrong entity was removed");
    }
}
