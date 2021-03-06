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
use Application\Exception\NotFoundException;
use Application\Entity\Campaign as CampaignEntity;

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
     * Enable editing groups?
     *
     * @var boolean
     */
    protected $fullEdit = true;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $em        Service locator
     * @param integer                 $id        Campaign ID
     * @param null|int|string         $name      Optional name
     * @param array                   $options   Optional options
     */
    public function __construct(ServiceLocatorInterface $sl, $id, $name = 'edit-campaign', $options = array())
    {
        $this->em = $sl->get('Doctrine\ORM\EntityManager');

        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $campaign = $this->em->getRepository('Application\Entity\Campaign')
                             ->find($id);
        if (!$campaign)
            throw new NotFoundException('Campaign not found');

        $newGroup = [
            CampaignEntity::STATUS_CREATED,
            CampaignEntity::STATUS_TESTED,
            CampaignEntity::STATUS_FINISHED,
            CampaignEntity::STATUS_ARCHIVED,
        ];

        $this->fullEdit = in_array($campaign->getStatus(), $newGroup);

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $id = new Element\Hidden('id');
        $this->add($id);

        $name = new Element\Text('name');
        $name->setLabel('Name');
        $this->add($name);

        $translate = $sl->get('viewhelpermanager')->get('translate');

        $whenDeadline = new Element\DateTime('when_deadline');
        $whenDeadline->setLabel('When deadline');
        $whenDeadline->setFormat($translate('GENERIC_DATETIME_FORMAT'));
        $whenDeadline->setAttribute('step', 'any');
        $this->add($whenDeadline);

        $entities = $this->em->getRepository('Application\Entity\Group')
                             ->findBy([], [ 'name' => 'ASC' ]);
        $options = [];
        foreach ($entities as $entity)
            $options[$entity->getId()] = $entity->getName();

        $groups = new Element\MultiCheckbox('groups');
        $groups->setLabel('Groups');
        $groups->setValueOptions($options);
        $groups->setValue([]);
        if (!$this->fullEdit)
            $groups->setAttribute('disabled', 'disabled');
        $this->add($groups);

        $entities = $this->em->getRepository('Application\Entity\Tag')
                             ->findBy([], [ 'name' => 'ASC' ]);
        $options = [];
        foreach ($entities as $entity)
            $options[$entity->getId()] = $entity->getName();

        if (count($options) > 0) {
            $tags = new Element\MultiCheckbox('tags');
            $tags->setLabel('Tags');
            $tags->setValueOptions($options);
            $tags->setValue([]);
            if (!$this->fullEdit)
                $tags->setAttribute('disabled', 'disabled');
            $this->add($tags);
        }
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

        $id = new Input('id');
        $id->setRequired(true)
           ->setBreakOnFailure(false);
        $filter->add($id);

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
                     ->getFilterChain()
                     ->attach(new Filter\StringTrim());
        $whenDeadline->getValidatorChain()
                     ->attach(new Validator\Date($params));
        $filter->add($whenDeadline);

        $groups = new Input('groups');
        $groups->setRequired($this->fullEdit)
               ->setBreakOnFailure(false);
        $filter->add($groups);

        if ($this->has('tags')) {
            $tags = new Input('tags');
            $tags->setRequired(false)
                 ->setBreakOnFailure(false);
            $filter->add($tags);
        }

        $this->inputFilter = $filter;
        return $filter;
    }
}
