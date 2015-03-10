<?php

namespace DataFormTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use DataForm\Form\Profile as ProfileForm;

class ProfileTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testInvalidConfirmForm()
    {
        $form = new ProfileForm();

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
    }

    public function testValidConfirmForm()
    {
        $form = new ProfileForm();

        $input = [
            'security' => $form->get('security')->getValue(),
            'first_name' => ' first ',
            'middle_name' => ' middle ',
            'last_name' => ' last ',
            'gender' => 'female',
            'company' => ' company ',
            'position' => ' position ',
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals('first', $output['first_name'], "First name is wrong");
        $this->assertEquals('middle', $output['middle_name'], "Middle name is wrong");
        $this->assertEquals('last', $output['last_name'], "Last name is wrong");
        $this->assertEquals('female', $output['gender'], "Gender is wrong");
        $this->assertEquals('company', $output['company'], "Company is wrong");
        $this->assertEquals('position', $output['position'], "Position is wrong");
    }
}
