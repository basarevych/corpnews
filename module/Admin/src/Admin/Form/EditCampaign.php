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

/**
 * Edit campaign entity form
 *
 * @category    Admin
 * @package     Form
 */
class EditCampaign extends Form
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
     * Constructor
     *
     * @param EntityManager    $em          Doctrine EntityManager
     * @param null|int|string  $name        Optional name
     * @param array            $options     Optional options
     */
    public function __construct($em, $name = 'edit-campaign', $options = array())
    {
        $this->em = $em;

        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $name = new Element\Text('name');
        $name->setLabel('Name');
        $this->add($name);

        $whenDeadline = new Element\DateTime('when_deadline');
        $whenDeadline->setLabel('When deadline');
        $whenDeadline->setFormat("Y-m-d H:i:s P");
        $whenDeadline->setAttribute('step', 'any');
        $this->add($whenDeadline);

        $entities = $em->getRepository('Application\Entity\Group')
                       ->findBy([], [ 'name' => 'ASC' ]);
        $options = [];
        foreach ($entities as $entity)
            $options[$entity->getId()] = $entity->getName();

        $groups = new Element\MultiCheckbox('groups');
        $groups->setLabel('Groups');
        $groups->setValueOptions($options);
        $groups->setValue([]);
        $this->add($groups);
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

        $name = new Input('name');
        $name->setRequired(true)
             ->setBreakOnFailure(false)
             ->getFilterChain()
             ->attach(new Filter\StringTrim());
        $filter->add($name);

        $params = [
            'format' => $this->get('when_deadline')->getFormat()
        ];

        $whenDeadline = new Input('when_deadline');
        $whenDeadline->setRequired(false)
                     ->setBreakOnFailure(false)
                     ->getValidatorChain()
                     ->attach(new Validator\Date($params));
        $filter->add($whenDeadline);

        $groups = new Input('groups');
        $groups->setRequired(true)
               ->setBreakOnFailure(false);
        $filter->add($groups);

        $this->inputFilter = $filter;
        return $filter;
    }
}
