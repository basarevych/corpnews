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
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\EntityManager;
use Application\Validator\EntityNotExists;

/**
 * Create/Edit Client entity form
 *
 * @category    Admin
 * @package     Form
 */
class EditClient extends Form
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
     * @param ServiceLocatorInterface $em        Service locator
     * @param integer                 $id        ID of the entity being edited (null when creating)
     * @param null|int|string         $name      Optional name
     * @param array                   $options   Optional options
     */
    public function __construct(ServiceLocatorInterface $sl, $id = null, $name = 'edit-client', $options = array())
    {
        $this->em = $sl->get('Doctrine\ORM\EntityManager');
        $this->id = $id;

        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        if ($this->id) {
            $id = new Element\Hidden('id');
            $this->add($id);
        }

        $email = new Element\Text('email');
        $email->setLabel('Email address');
        $this->add($email);

        $bounced = new Element\MultiCheckbox('bounced');
        $bounced->setLabel('Errors');
        $bounced->setValueOptions([ 1 => 'Email bounced' ]);
        $bounced->setValue([]);
        $this->add($bounced);

        $entities = $this->em->getRepository('Application\Entity\Group')
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

        if ($this->id) {
            $id = new Input('id');
            $id->setRequired(true)
               ->setBreakOnFailure(false);
            $filter->add($id);
        }

        $emailParams = [
            'allow' => Validator\Hostname::ALLOW_DNS | Validator\Hostname::ALLOW_LOCAL
        ];
        $entityParams = [
            'entityManager' => $this->em,
            'entity'        => 'Application\Entity\Client',
            'property'      => 'email',
        ];
        if ($this->id)
            $entityParams['ignoreId'] = $this->id;

        $email = new Input('email');
        $email->setRequired(true)
              ->setBreakOnFailure(false)
              ->getFilterChain()
              ->attach(new Filter\StringTrim());
        $email->getValidatorChain()
              ->attach(new Validator\EmailAddress($emailParams))
              ->attach(new EntityNotExists($entityParams));
        $filter->add($email);

        $bounced = new Input('bounced');
        $bounced->setRequired(false)
                ->setBreakOnFailure(false);
        $filter->add($bounced);

        $groups = new Input('groups');
        $groups->setRequired(false)
               ->setBreakOnFailure(false);
        $filter->add($groups);

        $this->inputFilter = $filter;
        return $filter;
    }
}
