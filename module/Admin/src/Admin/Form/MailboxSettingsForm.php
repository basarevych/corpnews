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
 * Edit Mailbox settings form
 *
 * @category    Admin
 * @package     Form
 */
class MailboxSettingsForm extends Form
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
    public function __construct($name = 'mailbox-settings', $options = array())
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $autodelete = new Element\Text('autodelete');
        $autodelete->setLabel('Autodelete');
        $this->add($autodelete);
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

        $autodelete = new Input('autodelete');
        $autodelete->setRequired(true)
                   ->setBreakOnFailure(false)
                   ->getFilterChain()
                   ->attach(new Filter\StringTrim())
                   ->attach(new LocaleFormattedNumber());
        $autodelete->getValidatorChain()
                   ->attach(new Integer());
        $filter->add($autodelete);

        $this->inputFilter = $filter;
        return $filter;
    }
}
