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
use Doctrine\ORM\EntityManager;
use Application\Validator\EntityNotExists;

/**
 * Create/Edit group entity form
 *
 * @category    Admin
 * @package     Form
 */
class EditGroup extends Form
{
    /**
     * The input filter
     *
     * @var InputFilter
     */
    protected $inputFilter = null;

    /**
     * Doctrine EntityManager
     *
     * @var EntityManager
     */
    protected $em = null;

    /**
     * ID of the entity being edited (null when creating)
     *
     * @var integer
     */
    protected $id = null;

    /**
     * Constructor
     *
     * @param EntityManager    $em          Doctrine EntityManager
     * @param integer          $id          ID of the entity being edited (null when creating)
     * @param null|int|string  $name        Optional name
     * @param array            $options     Optional options
     */
    public function __construct($em, $id = null, $name = 'edit-client', $options = array())
    {
        $this->em = $em;
        $this->id = $id;

        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        if ($this->id) {
            $id = new Element\Hidden('id');
            $this->add($id);
        }

        $name = new Element\Text('name');
        $name->setLabel('Name');
        $this->add($name);
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

        if ($this->id) {
            $id = new Input('id');
            $id->setRequired(true)
               ->setBreakOnFailure(false);
            $filter->add($id);
        }

        $entityParams = [
            'entityManager' => $this->em,
            'entity'        => 'Application\Entity\Group',
            'property'      => 'name',
        ];
        if ($this->id)
            $entityParams['ignoreId'] = $this->id;

        $name = new Input('name');
        $name->setRequired(true)
             ->setBreakOnFailure(false)
             ->getFilterChain()
             ->attach(new Filter\StringTrim());
        $name->getValidatorChain()
             ->attach(new EntityNotExists($entityParams));
        $filter->add($name);

        $this->inputFilter = $filter;
        return $filter;
    }
}
