<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BKUser\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Description of AuthController
 *
 * @author Kathryn
 */
class IndexController extends AbstractActionController
{
    public function indexAction()
    {
    }

    public function redirecttohomeAction()
    {
        $response = $this->getResponse();
        $response->setContent(password_hash('frank', PASSWORD_DEFAULT));
        return $response;
        return $this->redirect()->toRoute('home');
    }

}
