<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DynamicTable\Table;
use DynamicTable\Adapter\DoctrineORMAdapter;
use Application\Exception\NotFoundException;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Group as GroupEntity;
use Application\Entity\Client as ClientEntity;
use Application\Model\Letter;
use Application\Form\Confirm as ConfirmForm;
use Admin\Form\EditCampaign as EditCampaignForm;
use Admin\Form\TestCampaign as TestCampaignForm;

/**
 * Campaigns controller
 *
 * @category    Admin
 * @package     Controller
 */
class CampaignController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        return new ViewModel([
            'statuses' => CampaignEntity::getStatuses(),
        ]);
    }

    /**
     * Table data retrieving action
     */
    public function campaignTableAction()
    {
        $table = $this->createTable();
        $this->connectTableData($table);

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'describe':
            $data = $table->describe();
            break;
        case 'data':
            $data = $table->setPageParams($_GET)->fetch();
            break;
        default:
            throw new \Exception('Unknown query type: ' . $query);
        }

        $data['success'] = true;
        return new JsonModel($data);
    }

    /**
     * Launch campaign form action
     */
    public function launchCampaignAction()
    {
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if (!$id)
            throw new \Exception('No "id" parameter');

        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $repo = $em->getRepository('Application\Entity\Campaign');
        $task = $sl->get('TaskDaemon');

        $campaign = $repo->find($id);
        if (!$campaign)
            throw new NotFoundException('Campaign not found');

        $script = null;
        $form = new ConfirmForm();
        $messages = [];
        $readyToStart = [
            CampaignEntity::STATUS_CREATED,
            CampaignEntity::STATUS_TESTED,
            CampaignEntity::STATUS_FINISHED,
        ];
        $readyToRestart = [
            CampaignEntity::STATUS_PAUSED,
        ];
        $tested = [
            CampaignEntity::STATUS_TESTED,
            CampaignEntity::STATUS_FINISHED,
        ];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if (in_array($campaign->getStatus(), $readyToStart)) {
                    $campaign->setStatus(CampaignEntity::STATUS_QUEUED);
                    $em->persist($campaign);
                    $em->flush();

                    $task->getDaemon()->runTask('queued_campaign', $campaign->getId());
                } else if (in_array($campaign->getStatus(), $readyToRestart)) {
                    $campaign->setStatus(CampaignEntity::STATUS_STARTED);
                    $em->persist($campaign);
                    $em->flush();

                    $task->getDaemon()->runTask('send_email');
                }

                $script = "$('#modal-form').modal('hide'); reloadTable()";
            }
        } else {
            $form->setData([
                'id' => $id
            ]);
        }

        $model = new ViewModel([
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
            'noGroups'  => count($campaign->getGroups()) == 0,
            'ready'     => in_array($campaign->getStatus(), array_merge($readyToStart, $readyToRestart)),
            'tested'    => in_array($campaign->getStatus(), $tested),
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Edit campaign action
     */
    public function editCampaignAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dfm = $sl->get('DataFormManager');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        // Handle validate request
        if ($this->params()->fromQuery('query') == 'validate') {
            $field = $this->params()->fromQuery('field');
            $data = $this->params()->fromQuery('form');

            $form = new EditCampaignForm($sl, $data['id']);
            $form->setData($data);
            $form->isValid();

            $control = $form->get($field);
            $messages = [];
            foreach ($control->getMessages() as $msg)
                $messages[] = $translate($msg);

            return new JsonModel([
                'valid'     => (count($messages) == 0),
                'messages'  => $messages,
            ]);
        }

        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if (!$id)
            throw new \Exception('No "id" parameter');

        $campaign = $em->getRepository('Application\Entity\Campaign')
                       ->find($id);
        if (!$campaign)
            throw new NotFoundException('Campaign not found');

        $form = new EditCampaignForm($sl, $id);
        $messages = [];
        $script = "";

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $date = null;
                if (!empty($data['when_deadline'])) {
                    $format = $form->get('when_deadline')->getFormat();
                    $date = \DateTime::createFromFormat($format, $data['when_deadline']);
                }

                $campaign->setName($data['name']);
                $campaign->setWhenDeadline($date);

                if ($form->get('groups')->getAttribute('disabled') != 'disabled') {
                    foreach ($campaign->getGroups() as $group) {
                        $campaign->removeGroup($group);
                        $group->removeCampaign($campaign);
                    }
                    foreach ($data['groups'] as $groupId) {
                        $group = $em->getRepository('Application\Entity\Group')
                                    ->find($groupId);
                        if (!$group)
                            continue;
                        $campaign->addGroup($group);
                        $group->addCampaign($campaign);
                        $em->persist($group);
                    }
                }

                $em->persist($campaign);
                $em->flush();

                $script = "$('#modal-form').modal('hide'); reloadTable()";
            }

            if ($form->get('groups')->getAttribute('disabled') == 'disabled') {
                $groups = [];
                foreach ($campaign->getGroups() as $group)
                    $groups[] = $group->getId();
                $form->get('groups')->setValue($groups);
            }
        } else {
            $date = "";
            if (($dt = $campaign->getWhenDeadline()) !== null) {
                $format = $form->get('when_deadline')->getFormat();
                $date = $dt->format($format);
            }

            $groups = [];
            foreach ($campaign->getGroups() as $group)
                $groups[] = $group->getId();

            $form->setData([
                'id'            => $id,
                'name'          => $campaign->getName(),
                'when_deadline' => $date,
                'groups'        => $groups,
            ]);
        }

        $templates = $em->getRepository('Application\Entity\Template')
                      ->findByCampaign($campaign);

        $model = new ViewModel([
            'id'        => $id,
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
            'templates' => $templates,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Test campaign action
     */
    public function testCampaignAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dfm = $sl->get('DataFormManager');
        $mail = $sl->get('Mail');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $form = new TestCampaignForm($sl);
        $messages = [];
        $script = "";

        // Handle validate request
        if ($this->params()->fromQuery('query') == 'validate') {
            $field = $this->params()->fromQuery('field');
            $data = $this->params()->fromQuery('form');

            $form->setData($data);
            $form->isValid();

            $control = $form->get($field);
            $messages = [];
            foreach ($control->getMessages() as $msg)
                $messages[] = $translate($msg);

            return new JsonModel([
                'valid'     => (count($messages) == 0),
                'messages'  => $messages,
            ]);
        }

        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if (!$id)
            throw new \Exception('No "id" parameter');

        $campaign = $em->getRepository('Application\Entity\Campaign')
                       ->find($id);
        if (!$campaign)
            throw new NotFoundException('Campaign not found');

        $result = false;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $client = $em->getRepository('Application\Entity\Client')
                             ->findOneByEmail($data['tester']);
                if (!$client)
                    throw new NotFoundException('Client not found');

                $letters = [];
                foreach ($campaign->getTemplates() as $template) {
                    $letter = $mail->createFromTemplate($template, $client, $data['send_to']);
                    if ($letter === false) {
                        $result = $translate('Variable substitution failed');
                        break;
                    }
                    $letters[] = $letter;
                }

                if ($result === false) {
                    foreach ($letters as $letter) {
                        if (!$mail->sendLetter($letter))
                            $result = $translate('Campaign test failed');
                    }

                    if ($result === false) {
                        $result = $translate('Letter has been sent');

                        $campaign->setStatus(CampaignEntity::STATUS_TESTED);
                        $em->persist($campaign);
                        $em->flush();
                    }
                }
            }
        } else {
            $testers = $form->get('tester')->getValueOptions();
            if (count($testers) > 0) {
                $selected = array_shift($testers);
                $form->setData([
                    'id'            => $id,
                    'tester'        => $selected,
                    'send_to'       => $selected,
                ]);
            }
        }

        $noTesters = count($form->get('tester')->getValueOptions()) == 0;

        $dataForms = [];
        foreach ($dfm->getNames() as $name) {
            $dataForms[] = [
                'url'       => $dfm->getUrl($name),
                'title'     => $dfm->getTitle($name),
            ];
        }

        $model = new ViewModel([
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
            'noTesters' => $noTesters,
            'dataForms' => $dataForms,
            'result'    => $result,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Delete campaign form action
     */
    public function deleteCampaignAction()
    {
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if (!$id)
            throw new \Exception('No "id" parameter');

        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $repo = $em->getRepository('Application\Entity\Campaign');

        $script = null;
        $form = new ConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if ($id == '_all') {
                    $repo->removeAll();
                } else {
                    foreach (explode(',', $id) as $item) {
                        $entity = $repo->find($item);
                        if (!$entity)
                            continue;

                        $em->remove($entity);
                        $em->flush();
                    }
                }

                $script = "$('#modal-form').modal('hide'); reloadTable()";
            }
        } else {
            $form->setData([
                'id' => $id
            ]);
        }

        $model = new ViewModel([
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new NotFoundException('Action is not found');
    }

    /**
     * Create Table object
     *
     * @return Table
     */
    protected function createTable()
    {
        $sl = $this->getServiceLocator();
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $table = new Table();

        $table->setColumns([
            'id' => [
                'title'     => $translate('ID'),
                'sql_id'    => 'c.id',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_BETWEEN ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'name' => [
                'title'     => $translate('Name'),
                'sql_id'    => 'c.name',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'when_created' => [
                'title'     => $translate('When created'),
                'sql_id'    => 'c.when_created',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'when_started' => [
                'title'     => $translate('When started'),
                'sql_id'    => 'c.when_started',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'when_deadline' => [
                'title'     => $translate('When deadline'),
                'sql_id'    => 'c.when_deadline',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'when_finished' => [
                'title'     => $translate('When finished'),
                'sql_id'    => 'c.when_finished',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'groups' => [
                'title'     => $translate('Groups'),
                'sql_id'    => 'g.name',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'status' => [
                'title'     => $translate('Status'),
                'sql_id'    => 'c.status',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ ],
                'sortable'  => false,
                'visible'   => true,
            ],
            'percent' => [
                'title'     => $translate('Percent'),
                'sql_id'    => 'none',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ ],
                'sortable'  => false,
                'visible'   => true,
            ],
        ]);

        return $table;
    }

    /**
     * Create adapter and mapper
     *
     * @param Table $table
     */
    protected function connectTableData($table)
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $filter = [];
        foreach (CampaignEntity::getStatuses() as $status) {
            if ($this->params()->fromQuery($status, 1))
                $filter[] = $status;
        }

        $qb = $em->createQueryBuilder();
        $qb->select('c, g')
           ->from('Application\Entity\Campaign', 'c')
           ->leftJoin('c.groups', 'g')
           ->andWhere('c.status IN (:status_filter)')
           ->setParameter('status_filter', $filter);

        $adapter = new DoctrineORMAdapter();
        $adapter->setQueryBuilder($qb);

        $repoClients = $em->getRepository('Application\Entity\Client');

        $mapper = function ($row) use ($escapeHtml, $basePath, $translate, $repoClients) {
            $name = $escapeHtml($row->getName());
            $name = '<a href="javascript:void(0)" onclick="editCampaign(' . $row->getId() . ')">' . $name . '</a>';

            $groups = [];
            foreach ($row->getGroups() as $group)
                $groups[] = $escapeHtml($group->getName());

            $whenDeadline = $row->getWhenDeadline();
            $whenCreated = $row->getWhenCreated();
            $whenStarted = $row->getWhenStarted();
            $whenFinished = $row->getWhenFinished();

            $percent = '';
            $percents = [
                CampaignEntity::STATUS_STARTED,
                CampaignEntity::STATUS_PAUSED,
                CampaignEntity::STATUS_FINISHED,
            ];
            if (in_array($row->getStatus(), $percents)) {
                $all = 0;
                $pending = 0;
                foreach ($row->getTemplates() as $template) {
                    $all += $repoClients->countWithExistingLetters($template);
                    $pending += $repoClients->countWithPendingLetters($template);
                }
                $percentDone = ($all == 0) ? 0 : ($all - $pending) / $all;
                $percentDone = round($percentDone * 100, 2);
                $percentDone = \Application\Tool\Number::localeFormat($percentDone);

                $percent = '<button type="button" class="btn btn-default btn-xs" onclick="statCampaign(' . $row->getId() . ')">';
                $percent .= $percentDone . '%';
                $percent .= '</button>';
            }

            return [
                'id'            => $row->getId(),
                'name'          => $name,
                'when_created'  => $whenCreated ? $whenCreated->getTimestamp() : null,
                'when_started'  => $whenStarted ? $whenStarted->getTimestamp() : null,
                'when_deadline' => $whenDeadline? $whenDeadline->getTimestamp() : null,
                'when_finished' => $whenFinished ? $whenFinished->getTimestamp() : null,
                'groups'        => join(', ', $groups),
                'status'        => $translate('STATUS_' . strtoupper($row->getStatus())),
                'percent'       => $percent,
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
