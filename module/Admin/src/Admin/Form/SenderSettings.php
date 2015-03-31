<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Filter;
use Zend\Validator;
use Application\Validator\Integer;
use Application\Filter\LocaleFormattedNumber;

/**
 * Edit Email sender settings form
 *
 * @category    Admin
 * @package     Form
 */
class SenderSettings extends Form
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
     * @param null|int|string  $name        Optional name
     * @param array            $options     Optional options
     */
    public function __construct($name = 'sender-settings', $options = array())
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $interval = new Element\Text('interval');
        $interval->setLabel('Send interval');
        $this->add($interval);
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
        $csrf->setRequired(true)
             ->setBreakOnFailure(false);
        $filter->add($csrf);

        $interval = new Input('interval');
        $interval->setRequired(true)
                 ->setBreakOnFailure(false)
                 ->getFilterChain()
                 ->attach(new Filter\StringTrim())
                 ->attach(new LocaleFormattedNumber());
        $interval->getValidatorChain()
                 ->attach(new Integer());
        $filter->add($interval);

        $this->inputFilter = $filter;
        return $filter;
    }
}
