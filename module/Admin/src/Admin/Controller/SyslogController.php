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
use Application\Document\Syslog as SyslogDocument;
use Application\Form\Confirm as ConfirmForm;

/**
 * Syslog controller
 *
 * @category    Admin
 * @package     Controller
 */
class SyslogController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        return new ViewModel([
            'levels' => SyslogDocument::getLevels()
        ]);
    }

    /**
     * Table data retrieving action
     */
    public function syslogTableAction()
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
     * Clear syslog form action
     */
    public function clearSyslogAction()
    {
        $sl = $this->getServiceLocator();
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $repo = $dm->getRepository('Application\Document\Syslog');

        $script = null;
        $form = new ConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $repo->removeAll();

                $script = "$('#modal-form').modal('hide'); reloadTable()";
            }
        } else {
            $form->setData([
                'id' => '_all'
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
                'title'         => $translate('ID'),
                'field_name'    => 'id',
                'type'          => Table::TYPE_INTEGER,
                'filters'       => [ Table::FILTER_BETWEEN ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'when_happened' => [
                'title'         => $translate('When happened'),
                'field_name'    => 'when_happened',
                'type'          => Table::TYPE_DATETIME,
                'filters'       => [ Table::FILTER_BETWEEN ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'level' => [
                'title'         => $translate('Level'),
                'field_name'    => 'level',
                'type'          => Table::TYPE_STRING,
                'filters'       => [],
                'sortable'      => false,
                'visible'       => true,
            ],
            'message' => [
                'title'         => $translate('Message'),
                'field_name'    => 'none',
                'type'          => Table::TYPE_STRING,
                'filters'       => [],
                'sortable'      => false,
                'visible'       => true,
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
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $logger = $sl->get('Logger');
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $qb = $dm->createQueryBuilder();
        $qb->find('Application\Document\Syslog');

        $adapter = new DoctrineMongoODMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($escapeHtml, $translate, $logger) {
            return [
                'id'            => $row->getId(),
                'when_happened' => $row->getWhenHappened()->getTimestamp(),
                'level'         => $translate('LEVEL_' . strtoupper($row->getLevel())),
                'message'       => $escapeHtml($logger->prepareMessage($row)),
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
