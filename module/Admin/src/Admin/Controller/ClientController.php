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

/**
 * Clients controller
 *
 * @category    Admin
 * @package     Controller
 */
class ClientController extends AbstractActionController
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
    public function clientTableAction()
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
                'sql_id'    => 'c.id',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [],
                'sortable'  => false,
                'visible'   => false,
            ],
            'email' => [
                'title'     => $translate('Email address'),
                'sql_id'    => 'c.email',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'when_bounced' => [
                'title'     => $translate('Email bounced'),
                'sql_id'    => 'c.when_bounced',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
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

        $qb = $em->createQueryBuilder();
        $qb->select('c')
           ->from('Application\Entity\Client', 'c');

        $adapter = new DoctrineORMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($escapeHtml) {
            $whenBounced = $row->getWhenBounced();
            if ($whenBounced !== null)
                $whenBounced = $whenBounced->getTimestamp();

            return [
                'id'            => $row->getId(),
                'email'         => $escapeHtml($row->getEmail()),
                'when_bounced'  => $whenBounced,
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
