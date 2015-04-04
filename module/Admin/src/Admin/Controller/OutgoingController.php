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
use Application\Entity\Letter as LetterEntity;

/**
 * Outgoing messages controller
 *
 * @category    Admin
 * @package     Controller
 */
class OutgoingController extends AbstractActionController
{
    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel([
            'statuses' => LetterEntity::getStatuses(),
        ]);
    }

    /**
     * Table data retrieving action
     *
     * @return JsonModel
     */
    public function outgoingTableAction()
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
                'sql_id'    => 'l.id',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_BETWEEN ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'status' => [
                'title'     => $translate('Status'),
                'sql_id'    => 'l.status',
                'type'      => Table::TYPE_STRING,
                'filters'   => [],
                'sortable'  => false,
                'visible'   => false,
            ],
            'when_created' => [
                'title'     => $translate('When created'),
                'sql_id'    => 'l.when_created',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'when_processed' => [
                'title'     => $translate('When processed'),
                'sql_id'    => 'l.when_processed',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'campaign' => [
                'title'     => $translate('Campaign'),
                'sql_id'    => 'c.name',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'from_address' => [
                'title'     => $translate('From address'),
                'sql_id'    => 'l.from_address',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'to_address' => [
                'title'     => $translate('To address'),
                'sql_id'    => 'l.to_address',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'subject' => [
                'title'     => $translate('Subject'),
                'sql_id'    => 'l.subject',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
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
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $filter = [];
        foreach (LetterEntity::getStatuses() as $status) {
            if ($this->params()->fromQuery($status, 1))
                $filter[] = $status;
        }

        $qb = $em->createQueryBuilder();
        $qb->select('l')
           ->from('Application\Entity\Letter', 'l')
           ->leftJoin('l.template', 't')
           ->leftJoin('t.campaign', 'c')
           ->andWhere('l.status IN (:status_filter)')
           ->setParameter('status_filter', $filter);

        $adapter = new DoctrineORMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($escapeHtml, $translate) {
            $dateCreated = $row->getWhenCreated();
            if ($dateCreated !== null)
                $dateCreated = $dateCreated->getTimestamp();

            $dateProcessed = $row->getWhenProcessed();
            if ($dateProcessed !== null)
                $dateProcessed = $dateProcessed->getTimestamp();

            $subject = $row->getSubject();
            if (!$subject)
                $subject = $translate('(No subject)');
            $subject = '<a href="javascript:void(0)" onclick="openLetter({ letter: '
                . $row->getId() . ' })">' . $escapeHtml($subject) . '</a>';

            return [
                'id'                => $row->getId(),
                'status'            => $translate('STATUS_' . $row->getStatus()),
                'when_created'      => $dateCreated,
                'when_processed'    => $dateProcessed,
                'campaign'          => $row->getTemplate()->getCampaign()->getName(),
                'from_address'      => $row->getFromAddress(),
                'to_address'        => $row->getToAddress(),
                'subject'           => $subject,
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
