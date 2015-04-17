<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Writer_Excel2007;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
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

        $this->dtValue = new \DateTime();
        $this->dtFormat = 'Y-m-d H:i:s P';

        $this->profile = new ProfileDocument();
        $this->profile->setWhenUpdated($this->dtValue);
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
            'groups' => [ 9000 ],
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
            'fields'    => 'email,profile-when_updated,profile-last_name',
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
        $this->assertEquals('"email","profile / when_updated","profile / last_name"', $lines[0], "Header is wrong");
        $this->assertEquals('"foo@bar","' . $this->dtValue->format($this->dtFormat) . '","Lastname"', $lines[1], "Data is wrong");
    }

    public function testGenerateExcelActionGeneratesFile()
    {
        $params = [
            'fields'    => 'email,profile-when_updated,profile-last_name',
            'groups'    => 9000,
        ];
        $this->dispatch('/admin/import-export/generate-excel', HttpRequest::METHOD_GET, $params);
        $this->assertResponseStatusCode(200);

        $filename = '/tmp/corpnews-test.xlsx';
        $response = $this->getResponse()->getContent();
        file_put_contents($filename, $response);

        try {
            $type = PHPExcel_IOFactory::identify($filename);
            $reader = PHPExcel_IOFactory::createReader($type);
            $excel = $reader->load($filename);
        } catch(Exception $e) {
            die('Error loading file: ' . $e->getMessage());
        }

        $worksheet = $excel->getSheet(0); 
        $highestRow = $worksheet->getHighestRow(); 
        $highestColumn = $worksheet->getHighestColumn();

        $this->assertEquals(2, $highestRow, "Two rows should be generated");
        $this->assertEquals('C', $highestColumn, "Two columns should be generated");

        $emailTitle = $worksheet->getCell('A1')->getValue();
        $this->assertEquals('email', $emailTitle, "Email header is wrong");
        $whenUpdatedTitle = $worksheet->getCell('B1')->getValue();
        $this->assertEquals('profile / when_updated', $whenUpdatedTitle, "WhenUpdated header is wrong");
        $lastNameTitle = $worksheet->getCell('C1')->getValue();
        $this->assertEquals('profile / last_name', $lastNameTitle, "LastName header is wrong");

        $emailData = $worksheet->getCell('A2')->getValue();
        $this->assertEquals('foo@bar', $emailData, "Email data is wrong");
        $whenUpdatedData = $worksheet->getCell('B2')->getValue();
        $date = PHPExcel_Shared_Date::ExcelToPHPObject($whenUpdatedData);
        $this->assertEquals($this->dtValue, $date, "WhenUpdated data is wrong");
        $lastNameData = $worksheet->getCell('C2')->getValue();
        $this->assertEquals('Lastname', $lastNameData, "LastName data is wrong");

        unlink($filename);
    }

    public function testUploadActionCanBeAccessed()
    {
        $this->dispatch('/admin/import-export/upload');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\importexport');
        $this->assertControllerClass('ImportExportController');
        $this->assertMatchedRouteName('admin');
    }

    public function testUploadActionWorksForCsv()
    {
        $getParams = [
            'fields'    => 'email,profile-when_updated,profile-last_name',
            'format'    => 'csv',
            'separator' => 'comma',
            'ending'    => 'unix',
            'encoding'  => 'utf-8',
            'groups'    => [ 9000 ],
        ];
        $this->dispatch('/admin/import-export/upload', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        global $__UPLOAD_MOCK;
        $__UPLOAD_MOCK = true;

        $mock = '"email","profile / when_updated","profile / last_name"' . "\n"
               .'"new@email","' . $this->dtValue->format($this->dtFormat) . '","new name"' . "\n";

        $params = [
            'security'  => $security,
            'fields'    => 'email,profile-when_updated,profile-last_name',
            'format'    => 'csv',
            'separator' => 'comma',
            'ending'    => 'unix',
            'encoding'  => 'utf-8',
            'groups'    => [ 9000 ],
            'file'      => [
                'name'      => 'import.csv',
                'type'      => 'application/vnd.ms-excel',
                'tmp_name'  => '/tmp/corpnews-test.csv',
                'error'     => 0,
                'size'      => strlen($mock),
            ],
        ];

        file_put_contents($params['file']['tmp_name'], $mock);

        $this->dispatch('/admin/import-export/upload', HttpRequest::METHOD_POST, $params);
        $this->assertResponseStatusCode(200);

        if (is_file($params['file']['tmp_name']))
            unlink($params['file']['tmp_name']);

        $profile = new ProfileDocument();
        $profile->setWhenUpdated($this->dtValue);
        $profile->setLastName('new name');

        $session = $this->sl->get('Session');
        $cnt = $session->getContainer();
        $this->assertEquals(
            $cnt->import,
            [
                'groups' => [ 9000 ],
                'fields' => 'email,profile-when_updated,profile-last_name',
                'rows' => [
                    [
                        'email' => 'new@email',
                        'docs' => [ 'profile' => $profile ],
                    ],
                ],
            ],
            "Import data is wrong"
        );
    }

    public function testUploadActionWorksForExcel()
    {
        $getParams = [
            'fields'    => 'email,profile-when_updated,profile-last_name',
            'format'    => 'excel',
            'separator' => 'comma',
            'ending'    => 'unix',
            'encoding'  => 'utf-8',
            'groups'    => [ 9000 ],
        ];
        $this->dispatch('/admin/import-export/upload', HttpRequest::METHOD_GET, $getParams);
        $this->assertResponseStatusCode(200);

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        global $__UPLOAD_MOCK;
        $__UPLOAD_MOCK = true;

        $params = [
            'security'  => $security,
            'fields'    => 'email,profile-when_updated,profile-last_name',
            'format'    => 'csv',
            'separator' => 'comma',
            'ending'    => 'unix',
            'encoding'  => 'utf-8',
            'groups'    => [ 9000 ],
            'file'      => [
                'name'      => 'import.csv',
                'type'      => 'application/vnd.ms-excel',
                'tmp_name'  => '/tmp/corpnews-test.csv',
                'error'     => 0,
                'size'      => 123,
            ],
        ];

        $spreadsheet = new PHPExcel();
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();

        $worksheet->SetCellValueByColumnAndRow(0, 2, 'new@email');

        $worksheet->SetCellValueByColumnAndRow(
            1,
            2,
            PHPExcel_Shared_Date::FormattedPHPToExcel(
                $this->dtValue->format('Y'),
                $this->dtValue->format('n'),
                $this->dtValue->format('j'),
                $this->dtValue->format('G'),
                $this->dtValue->format('i'),
                $this->dtValue->format('s')
            )
        );
        $worksheet->getStyleByColumnAndRow(1, 2)->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');

        $worksheet->SetCellValueByColumnAndRow(2, 2, 'new name');

        $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save($params['file']['tmp_name']);

        $this->dispatch('/admin/import-export/upload', HttpRequest::METHOD_POST, $params);
        $this->assertResponseStatusCode(200);

        if (is_file($params['file']['tmp_name']))
            unlink($params['file']['tmp_name']);

        $profile = new ProfileDocument();
        $profile->setWhenUpdated($this->dtValue);
        $profile->setLastName('new name');

        $session = $this->sl->get('Session');
        $cnt = $session->getContainer();
        $this->assertEquals(
            $cnt->import,
            [
                'groups' => [ 9000 ],
                'fields' => 'email,profile-when_updated,profile-last_name',
                'rows' => [
                    [
                        'email' => 'new@email',
                        'docs' => [ 'profile' => $profile ],
                    ],
                ],
            ],
            "Import data is wrong"
        );
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
        $this->assertEquals($this->dtValue, $persistedDocuments[0]->getWhenUpdated(), "WhenUpdated is wrong");
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
