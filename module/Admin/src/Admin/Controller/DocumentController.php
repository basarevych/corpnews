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
     *
     * @return ViewModel
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
     *
     * @return JsonModel
     */
    public function documentTableAction()
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');

        $name = $this->params()->fromQuery('name');
        if (!$name)
            throw new \Exception("No data form 'name' parameter");

        $class = $dfm->getTableClass($name);
        $table = new $class($sl);

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
}
