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
 * Sent log controller
 *
 * @category    Admin
 * @package     Controller
 */
class SentLogController extends AbstractActionController
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
    public function sentTableAction()
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
            'secret_key' => [
                'title'     => $translate('Secret key'),
                'sql_id'    => 'l.secret_key',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'error' => [
                'title'     => $translate('Error'),
                'sql_id'    => 'l.error',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'when_sent' => [
                'title'     => $translate('When sent'),
                'sql_id'    => 'l.when_sent',
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
                'visible'   => true,
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

        $qb = $em->createQueryBuilder();
        $qb->select('l')
           ->from('Application\Entity\Letter', 'l')
           ->leftJoin('l.template', 't')
           ->leftJoin('t.campaign', 'c');

        $adapter = new DoctrineORMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($escapeHtml, $translate) {
            $date = $row->getWhenSent();
            if ($date !== null)
                $date = $date->getTimestamp();

            $subject = $row->getSubject();
            if (!$subject)
                $subject = $translate('(No subject)');
            $subject = '<a href="javascript:void(0)" onclick="openLetter({ letter: '
                . $row->getId() . ' })">' . $escapeHtml($subject) . '</a>';

            return [
                'id'            => $row->getId(),
                'secret_key'    => $row->getSecretKey(),
                'error'         => $row->getError(),
                'when_sent'     => $date,
                'campaign'      => $row->getTemplate()->getCampaign()->getName(),
                'from_address'  => $row->getFromAddress(),
                'to_address'    => $row->getToAddress(),
                'subject'       => $subject,
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
