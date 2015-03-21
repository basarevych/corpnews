<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Mail\Message;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Group as GroupEntity;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Letter as LetterEntity;
use Admin\Form\StartCampaign as StartCampaignForm;

class CampaignControllerQueryMock {
    public function getSingleScalarResult() {
        return 0;
    }
}

class CampaignControllerTransportMock {
    public function send() {
        return null;
    }
}

class CampaignControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->sl = $this->getApplicationServiceLocator();
        $session = $this->sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'createQueryBuilder', 'getRepository', 'persist', 'remove', 'flush' ])
                         ->getMock();

        $this->repoCampaigns = $this->getMockBuilder('Application\Entity\CampaignRepository')
                                    ->disableOriginalConstructor()
                                    ->setMethods([ 'find', 'removeAll' ])
                                    ->getMock();

        $this->template = new TemplateEntity();
        $this->template->setMessageId('mid');
        $this->template->setSubject('subject');
        $this->template->setHeaders('headers');
        $this->template->setBody('body');

        $this->campaign = new CampaignEntity();
        $this->campaign->setName('foo');
        $this->campaign->setStatus('bar');

        $this->campaign->addTemplate($this->template);
        $this->template->setCampaign($this->campaign);

        $reflection = new \ReflectionClass(get_class($this->campaign));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->campaign, 42);

        $this->repoCampaigns->expects($this->any())
                            ->method('find')
                            ->will($this->returnCallback(function ($id) {
                                if ($id == 42)
                                    return $this->campaign;
                            }));

        $this->repoGroups = $this->getMockBuilder('Application\Entity\GroupRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'find', 'findBy' ])
                                 ->getMock();

        $this->group = new GroupEntity();
        $this->group->setName('group');

        $reflection = new \ReflectionClass(get_class($this->group));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->group, 9000);

        $this->repoGroups->expects($this->any())
                         ->method('find')
                         ->will($this->returnCallback(function ($id) {
                            if ($id == 9000)
                                return $this->group;
                         }));

        $this->repoGroups->expects($this->any())
                         ->method('findBy')
                         ->will($this->returnValue([ $this->group ]));

        $this->repoClients = $this->getMockBuilder('Application\Entity\GroupRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findByGroupName', 'findOneByEmail' ])
                                  ->getMock();

        $this->client = new ClientEntity();
        $this->client->setEmail('foo@bar');

        $this->repoClients->expects($this->any())
                          ->method('findByGroupName')
                          ->will($this->returnCallback(function ($name) {
                                if ($name == GroupEntity::NAME_TESTERS)
                                    return [ $this->client ];
                                return [];
                          }));

        $this->repoClients->expects($this->any())
                          ->method('findOneByEmail')
                          ->will($this->returnCallback(function ($email) {
                                if ($email == 'foo@bar')
                                    return $this->client;
                          }));

        $this->repoTemplates = $this->getMockBuilder('Application\Entity\TemplateRepository')
                                    ->disableOriginalConstructor()
                                    ->setMethods([ 'findByCampaign' ])
                                    ->getMock();

        $this->repoTemplates->expects($this->any())
                            ->method('findByCampaign')
                            ->will($this->returnCallback(function ($campaign) {
                                if ($campaign == $this->campaign)
                                    return $this->template;
                            }));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Campaign', $this->repoCampaigns ],
                    [ 'Application\Entity\Group', $this->repoGroups ],
                    [ 'Application\Entity\Client', $this->repoClients ],
                    [ 'Application\Entity\Template', $this->repoTemplates ],
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
                 ->will($this->returnValue(new CampaignControllerQueryMock()));

        $this->mail = $this->getMockBuilder('Application\Service\Mail')
                           ->setMethods([ 'createFromTemplate', 'getTransport' ])
                           ->getMock();

        $this->mail->expects($this->any())
                   ->method('getTransport')
                   ->will($this->returnValue(new CampaignControllerTransportMock()));

        $this->mail->setServiceLocator($this->sl);

        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $this->sl->setService('Mail', $this->mail);
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/campaign');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\campaign');
        $this->assertControllerClass('CampaignController');
        $this->assertMatchedRouteName('admin');
    }

    public function testCampaignTableActionCanBeAccessed()
    {
        $this->dispatch('/admin/campaign/campaign-table');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\campaign');
        $this->assertControllerClass('CampaignController');
        $this->assertMatchedRouteName('admin');
    }

    public function testCampaignTableActionSendsDescription()
    {  
        $this->dispatch('/admin/campaign/campaign-table', HttpRequest::METHOD_GET, [ 'query' => 'describe' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['columns']) && count($data['columns']), "No columns described");
    }

    public function testCampaignTableActionSendsData()
    {
        $this->infrastructure = new ORMInfrastructure([
            'Application\Entity\Campaign',
            'Application\Entity\Group',
        ]);
        $this->repository = $this->infrastructure->getRepository(
            'Application\Entity\Campaign'
        );
        $this->em = $this->infrastructure->getEntityManager();

        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);

        $a = new CampaignEntity();
        $a->setName('foobar');
        $a->setStatus(CampaignEntity::STATUS_CREATED);

        $this->infrastructure->import([ $a ]);

        $this->dispatch('/admin/campaign/campaign-table', HttpRequest::METHOD_GET, [ 'query' => 'data' ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse()->getContent();
        $data = Json::decode($response, Json::TYPE_ARRAY);

        $this->assertEquals(true, isset($data['rows']) && count($data['rows']) == 1, "Invalid data returned");
        $this->assertEquals(1, $data['rows'][0]['id'], "Invalid ID");
    }

    public function testStartCampaignActionCanBeAccessed()
    {
        $this->dispatch('/admin/campaign/start-campaign');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\campaign');
        $this->assertControllerClass('CampaignController');
        $this->assertMatchedRouteName('admin');
    }

    public function testStartCampaignActionDisplaysTesters()
    {
        $this->dispatch('/admin/campaign/start-campaign', HttpRequest::METHOD_GET, [ 'id' => 42 ]);

        $this->assertQuery('input[type="radio"][value="foo@bar"]');
    }

    public function testStartCampaignActionUpdatesCampaign()
    {
        $this->dispatch('/admin/campaign/start-campaign', HttpRequest::METHOD_GET, [ 'id' => 42 ]);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $form = new StartCampaignForm($this->sl);
        $dt = new \DateTime();
        $format = $form->get('when_deadline')->getFormat();

        $postParams = [
            'security'  => $security,
            'id' => 42,
            'name' => ' name ',
            'when_deadline' => $dt->format($format),
            'groups' => [ 9000 ],
        ];
        $this->dispatch('/admin/campaign/start-campaign', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $groups = array_values($this->campaign->getGroups()->toArray());
        $this->assertEquals('name', $this->campaign->getName(), "Name is wrong");
        $this->assertEquals($dt, $this->campaign->getWhenDeadline(), "Deadline is wrong");
        $this->assertEquals(9000, $groups[0]->getId(), "Group ID is wrong");
    }

    public function testTestLetterActionCanBeAccessed()
    {
        $this->dispatch('/admin/campaign/test-letter');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\campaign');
        $this->assertControllerClass('CampaignController');
        $this->assertMatchedRouteName('admin');
    }

    public function testTestLetterActionWorks()
    {
        $passedTemplate = null;
        $passedClient = null;
        $this->mail->expects($this->any())
                   ->method('createFromTemplate')
                   ->will($this->returnCallback(function ($template, $client) use (&$passedTemplate, &$passedClient) {
                        $passedTemplate = $template;
                        $passedClient = $client;

                        $fixture = file_get_contents(__DIR__ . '/../../../../Application/test/ApplicationTest/LetterFixture.txt');
                        $pos = strpos($fixture, "\n\n");
                        $headers = substr($fixture, 0, $pos);
                        $body = substr($fixture, $pos, strlen($fixture) - $pos);

                        $letter = new LetterEntity();
                        $letter->setHeaders($headers);
                        $letter->setBody($body);
                        return $letter;
                   }));

        $persisted = [];
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                        $persisted[] = $entity;
                 }));

        $getParams = [
            'campaign' => 42,
            'email' => 'foo@bar',
            'send_to' => 'foo@bar'
        ];
        $this->dispatch('/admin/campaign/test-letter', HttpRequest::METHOD_GET, $getParams);

        $this->assertEquals($this->template, $passedTemplate, "Wrong template used");
        $this->assertEquals($this->client, $passedClient, "Wrong client used");

        $this->assertEquals(true,
            count($persisted) == 2
                && $persisted[0] instanceof LetterEntity
                && $persisted[1] instanceof CampaignEntity,
            "Only letter and campaign should be persisted"
        );
        $this->assertEquals(CampaignEntity::STATUS_TESTED, $persisted[1]->getStatus(), "Status should be STATUS_TESTED");
    }

    public function testDeleteCampaignActionCanBeAccessed()
    {
        $this->dispatch('/admin/campaign/delete-campaign');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\campaign');
        $this->assertControllerClass('CampaignController');
        $this->assertMatchedRouteName('admin');
    }

    public function testDeleteCamapignActionRemovesAll()
    {
        $entitiesRemoved = false;
        $this->repoCampaigns->expects($this->any())
                            ->method('removeAll')
                            ->will($this->returnCallback(function () use (&$entitiesRemoved) {
                                $entitiesRemoved = true;
                            }));

        $getParams = [
            'id' => '_all',
        ];
        $this->dispatch('/admin/campaign/delete-campaign', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => '_all',
        ];

        $this->dispatch('/admin/campaign/delete-campaign', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(true, $entitiesRemoved, "All entities were not removed");
    }

    public function testDeleteCampaignActionRemovesCampaign()
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
        $this->dispatch('/admin/campaign/delete-campaign', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $postParams = [
            'security' => $security,
            'id' => 42,
        ];

        $this->dispatch('/admin/campaign/delete-campaign', HttpRequest::METHOD_POST, $postParams);
        $this->assertResponseStatusCode(200);

        $this->assertEquals($this->campaign, $removed, "Wrong entity was removed");
    }
}
