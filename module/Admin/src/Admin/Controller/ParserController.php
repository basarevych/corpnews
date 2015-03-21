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
 * Parser controller
 *
 * @category    Admin
 * @package     Controller
 */
class ParserController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');
        $parser = $sl->get('Parser');

        $functions = [];
        foreach ($parser->getFunctions() as $name) {
            $functions[$name] = [
                'descr'     => $parser->getFunctionDescr($name),
            ];
        }

        $dataForms = [];
        foreach ($dfm->getNames() as $name) {
            $dataForms[$name] = [
                'title'     => $dfm->getTitle($name),
            ];
        }

        return new ViewModel([
            'functions' => $functions,
            'dataForms' => $dataForms,
        ]);
    }

    /**
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new NotFoundException('Action is not found');
    }
}
