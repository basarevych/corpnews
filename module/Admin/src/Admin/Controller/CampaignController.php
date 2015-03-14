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
use Application\Form\Confirm as ConfirmForm;

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
        return new ViewModel();
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
     * Edit campaign action
     */
    public function editAction()
    {
        return new ViewModel();
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
                'sql_id'    => 'g.name',
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
                'sortable'  => false,
                'visible'   => true,
            ],
            'when_started' => [
                'title'     => $translate('When started'),
                'sql_id'    => 'c.when_started',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => false,
                'visible'   => true,
            ],
            'when_finished' => [
                'title'     => $translate('When finished'),
                'sql_id'    => 'c.when_finished',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
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

        $createdFilter = $this->params()->fromQuery('created', 1);
        $testedFilter = $this->params()->fromQuery('tested', 1);
        $queuedFilter = $this->params()->fromQuery('queued', 1);
        $startedFilter = $this->params()->fromQuery('started', 1);
        $pausedFilter = $this->params()->fromQuery('paused', 1);
        $finishedFilter = $this->params()->fromQuery('finished', 1);

        $filter = [];
        foreach (CampaignEntity::getStatuses() as $status) {
            if ($this->params()->fromQuery($status, 1))
                $filter[] = $status;
        }

        $qb = $em->createQueryBuilder();
        $qb->select('c')
           ->from('Application\Entity\Campaign', 'c')
           ->andWhere('c.status IN (:status_filter)')
           ->setParameter('status_filter', $filter);

        $adapter = new DoctrineORMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($escapeHtml, $basePath) {
            $name = '<a href="' . $basePath('/admin/campaign/edit?id=' . $row->getId()) .'">'
                . $escapeHtml($row->getName()) . '</a>';
            $whenCreated = $row->getWhenCreated();
            $whenStarted = $row->getWhenStarted();
            $whenFinished = $row->getWhenFinished();

            return [
                'id'            => $row->getId(),
                'name'          => $name,
                'when_created'  => $whenCreated ? $whenCreated->getTimestamp() : null,
                'when_started'  => $whenStarted ? $whenStarted->getTimestamp() : null,
                'when_finished' => $whenFinished ? $whenFinished->getTimestamp() : null,
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
