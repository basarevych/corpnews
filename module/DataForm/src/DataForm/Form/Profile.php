<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Filter;

/**
 * Profile form
 *
 * @category    DataForm
 * @package     Form
 */
class Profile extends Form
{
    /**
     * The input filter
     *
     * @var InputFilter
     */
    protected $inputFilter = null;

    /**
     * Constructor
     *
     * @param  null|int|string  $name    Optional name
     * @param  array            $options Optional options
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name ? $name : 'profile', $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $firstName = new Element\Text('first_name');
        $firstName->setLabel('First name');
        $this->add($firstName);

        $middleName = new Element\Text('middle_name');
        $middleName->setLabel('Middle name');
        $this->add($middleName);

        $lastName = new Element\Text('last_name');
        $lastName->setLabel('Last name');
        $this->add($lastName);

        $gender = new Element\Radio('gender');
        $gender->setLabel('Gender');
        $gender->setValueOptions([
            ''          => 'Not specified',
            'male'      => 'male',
            'female'    => 'female',
        ]);
        $this->add($gender);

        $company = new Element\Text('company');
        $company->setLabel('Company');
        $this->add($company);

        $position = new Element\Text('position');
        $position->setLabel('Position');
        $this->add($position);

        $submit = new Element\Submit('submit');
        $submit->setLabel('Save changes');
        $this->add($submit);
    }

    /**
     * Retrieve input filter used by this form
     *
     * @return null|InputFilterInterface
     */
    public function getInputFilter()
    {
        if ($this->inputFilter)
            return $this->inputFilter;

        $filter = new InputFilter();

        $csrf = new Input('security');
        $csrf->setRequired(true);
        $filter->add($csrf);

        $firstName = new Input('first_name');
        $firstName->setRequired(false)
                  ->getFilterChain()
                  ->attach(new Filter\StringTrim());
        $filter->add($firstName);

        $middleName = new Input('middle_name');
        $middleName->setRequired(false)
                   ->getFilterChain()
                   ->attach(new Filter\StringTrim());
        $filter->add($middleName);

        $lastName = new Input('last_name');
        $lastName->setRequired(false)
                 ->getFilterChain()
                 ->attach(new Filter\StringTrim());
        $filter->add($lastName);

        $gender = new Input('gender');
        $gender->setRequired(false);
        $filter->add($gender);

        $company = new Input('company');
        $company->setRequired(false)
                ->getFilterChain()
                ->attach(new Filter\StringTrim());
        $filter->add($company);

        $position = new Input('position');
        $position->setRequired(false)
                 ->getFilterChain()
                 ->attach(new Filter\StringTrim());
        $filter->add($position);

        $this->inputFilter = $filter;
        return $filter;
    }
}
