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
use DynamicTable\Adapter\DoctrineMongoODMAdapter;
use Application\Exception\NotFoundException;

/**
 * Documents controller
 *
 * @category    Admin
 * @package     Controller
 */
class DocumentController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');

        $names = $dfm->getNames();
        $dataForms = [];
        foreach ($names as $name)
            $dataForms[$name] = $dfm->getTitle($name);

        $name = $this->params()->fromQuery('name');

        $current = null;
        if ($name && in_array($name, $names))
            $current = $name;
        else if (count($names))
            $current = $names[0];

        return new ViewModel([
            'dataForms' => $dataForms,
            'current'   => $current,
        ]);
    }

    /**
     * Table data retrieving action
     */
    public function documentTableAction()
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');

        $name = $this->params()->fromQuery('name');
        if (!$name)
            throw new \Exception("No data form 'name' parameter");

        $table = $this->createTable();
        $this->connectTableData($table, $name);

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
                'title'         => $translate('ID'),
                'field_name'    => 'id',
                'type'          => Table::TYPE_INTEGER,
                'filters'       => [ Table::FILTER_EQUAL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'client_email' => [
                'title'         => $translate('Email address'),
                'field_name'    => 'client_email',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'when_updated' => [
                'title'         => $translate('When updated'),
                'field_name'    => 'when_bounced',
                'type'          => Table::TYPE_DATETIME,
                'filters'       => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'first_name' => [
                'title'         => $translate('First name'),
                'field_name'    => 'first_name',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'middle_name' => [
                'title'         => $translate('Middle name'),
                'field_name'    => 'middle_name',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'last_name' => [
                'title'         => $translate('Last name'),
                'field_name'    => 'last_name',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'gender' => [
                'title'         => $translate('Gender'),
                'field_name'    => 'gender',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'company' => [
                'title'         => $translate('Company'),
                'field_name'    => 'company',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'position' => [
                'title'         => $translate('Position'),
                'field_name'    => 'position',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
        ]);

        return $table;
    }

    /**
     * Create adapter and mapper
     *
     * @param Table $table
     * @param string $name      Data form name
     */
    protected function connectTableData($table, $name)
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');
        $translate = $sl->get('viewhelpermanager')->get('translate');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');

        $url = $basePath($dfm->getUrl($name));
        $class = $dfm->getDocumentClass($name);
        if (!$class)
            throw new NotFoundException("Document for $name not found");

        $qb = $dm->createQueryBuilder();
        $qb->find($class);

        $adapter = new DoctrineMongoODMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($url, $escapeHtml, $translate) {
            $email = $escapeHtml($row->getClientEmail());
            $email = '<a href="' . $url . '?email=' . urlencode($email) . '" target="_blank">' . $email . '</a>';

            $whenUpdated = $row->getWhenUpdated();
            if ($whenUpdated !== null)
                $whenUpdated = $whenUpdated->getTimestamp();

            return [
                'id'            => $row->getId(),
                'client_email'  => $email,
                'when_updated'  => $whenUpdated,
                'first_name'    => $escapeHtml($row->getFirstName()),
                'middle_name'   => $escapeHtml($row->getMiddleName()),
                'last_name'     => $escapeHtml($row->getLastName()),
                'gender'        => $translate(($value = $row->getGender()) ? $value : ''),
                'company'       => $escapeHtml($row->getCompany()),
                'position'      => $escapeHtml($row->getPosition()),
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
