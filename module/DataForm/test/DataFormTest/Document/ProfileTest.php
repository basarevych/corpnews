<?php

namespace DataFormTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use DataForm\Document\Profile as ProfileDocument;

class ProfileDocumentTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testToArray()
    {
        $dt = new \DateTime();
        $dtFormat = 'Y-m-d H:i:s P';

        $doc = new ProfileDocument();
        $doc->setId('id');
        $doc->setClientEmail('foo@bar');
        $doc->setWhenUpdated($dt);
        $doc->setFirstName('first');
        $doc->setMiddleName('middle');
        $doc->setLastName('last');
        $doc->setGender('gender');
        $doc->setCompany('company');
        $doc->setPosition('position');

        $array = $doc->toArray();

        $this->assertEquals('id', $array['id'], "id is wrong");
        $this->assertEquals('foo@bar', $array['client_email'], "Email is wrong");
        $this->assertEquals($dt->format($dtFormat), $array['when_updated'], "when_updated is wrong");
        $this->assertEquals('first', $array['first_name'], "first_name is wrong");
        $this->assertEquals('middle', $array['middle_name'], "middle_name is wrong");
        $this->assertEquals('last', $array['last_name'], "last_name is wrong");
        $this->assertEquals('gender', $array['gender'], "gender is wrong");
        $this->assertEquals('company', $array['company'], "company is wrong");
        $this->assertEquals('position', $array['position'], "position is wrong");
    }

    public function testFromArray()
    {
        $dt = new \DateTime();
        $dtFormat = 'Y-m-d H:i:s P';

        $doc = new ProfileDocument();
        $doc->fromArray([
            'id'                => 'id',
            'client_email'      => 'foo@bar',
            'when_updated'      => $dt->format($dtFormat),
            'first_name'        => 'first',
            'middle_name'       => 'middle',
            'last_name'         => 'last',
            'gender'            => 'gender',
            'company'           => 'company',
            'position'          => 'position'
        ]);

        $array = $doc->toArray();

        $this->assertEquals('id', $doc->getId(), "id is wrong");
        $this->assertEquals('foo@bar', $doc->getClientEmail(), "Email is wrong");
        $this->assertEquals($dt, $doc->getWhenUpdated(), "when_updated is wrong");
        $this->assertEquals('first', $doc->getFirstName(), "first_name is wrong");
        $this->assertEquals('middle', $doc->getMiddleName(), "middle_name is wrong");
        $this->assertEquals('last', $doc->getLastName(), "last_name is wrong");
        $this->assertEquals('gender', $doc->getGender(), "gender is wrong");
        $this->assertEquals('company', $doc->getCompany(), "company is wrong");
        $this->assertEquals('position', $doc->getPosition(), "position is wrong");
    }
}
