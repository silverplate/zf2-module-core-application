<?php

namespace CoreApplication\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $data = array('content' => 'Hello, world!');

        $view = new ViewModel($data);
        $view->setTemplate('core-application/index/index');

        return $view;
    }
}
