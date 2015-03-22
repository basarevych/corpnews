<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Letter as LetterEntity;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Campaign as CampaignEntity;

class OutgoingControllerQueryMock {
    public function getSingleScalarResult() {
        return 0;
    }
}

class OutgoingControllerTest extends AbstractHttpControllerTestCase
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
                 ->will($this->returnValue(new OutgoingControllerQueryMock()));

        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/outgoing');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\outgoing');
        $this->assertControllerClass('OutgoingController');
        $this->assertMatchedRouteName('admin');
    }

    public function testOutgoingTableActionCanBeAccessed()
    {
        $this->dispatch('/admin/outgoing/outgoing-table');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\outgoing');
        $this->assertControllerClass('OutgoingController');
        $this->assertMatchedRouteName('admin');
    }

    public function testOutgoingTableActionSendsDescription()
    {  
        $this->dispatch('/admin/outgoing/outgoing-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testOutgoingTableActionSendsData()
    {
        $this->infrastructure = new ORMInfrastructure([
            'Application\Entity\Letter',
            'Application\Entity\Template',
            'Application\Entity\Campaign',
        ]);
        $this->repository = $this->infrastructure->getRepository(
            'Application\Entity\Letter'
        );
        $this->em = $this->infrastructure->getEntityManager();

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);

        $c = new CampaignEntity();
        $c->setName('campaign');
        $c->setStatus(CampaignEntity::STATUS_CREATED);

        $t = new TemplateEntity();
        $t->setCampaign($c);
        $t->setMessageId('mid');
        $t->setSubject('subject');
        $t->setHeaders('headers');
        $t->setBody('body');

        $a = new LetterEntity();
        $a->setTemplate($t);
        $a->setError('foobar');
        $a->setFromAddress('from');
        $a->setToAddress('to');
        $a->setSubject('subject');
        $a->setHeaders('headers');
        $a->setBody('body');

        $this->infrastructure->import([ $c, $t, $a ]);

        $this->dispatch('/admin/outgoing/outgoing-table', HttpRequest::METHOD_GET, [ 'query' => 'data' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 1, "Invalid data returned");
        $this->assertEquals(1, $data['rows'][0]['id'], "Invalid ID");
    }
}
