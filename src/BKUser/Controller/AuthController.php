<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BKUser\Controller;

use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\Result as AuthResult;
use bkuser\Authentication\AuthenticationService;

/**
 * Description of AuthController
 *
 * @author Kathryn
 */
class AuthController extends AbstractActionController
{
    public function indexAction()
    {
        return "Nothing to see here - move along now";
    }

    public function loginAction()
    {
        $config = $this->getServiceLocator()->get('bkuser\auth\config');

        /** @var AuthenticationService $authService */
        $authService = $this->getServiceLocator()->get('bkuser\auth\service');
        if ($authService->hasIdentity()) {
            $this->redirect()->toUrl($config['routes']['success']);
        }

//        $prg = $this->params()->fromPost();
        $prg = $this->postRedirectGet('bkuser-login');
        if ($prg instanceof Response) {
            return $prg;
        } else {
            /** @var \Zend\Form\Form $form */
            $form = $this->getServiceLocator()->get('bkuser\form\loginForm');
            if ($prg) {
                $form->setData($prg);
                if ($form->isValid()) {

                    $result = $authService->authenticateCredentials($form->get('username')->getValue(), $form->get('password')->getValue());
                    if ($result->isValid()) {
                        $this->redirect()->toUrl($config['routes']['success']);
                    } else {
                        $message = $config['messages'][$result->getCode()];

                        $this->flashMessenger()->addMessage($message);
                        $this->redirect()->refresh();
                    }
                }
            }

            return array(
                'loginForm' => $form
            );
        }
    }

    public function logoutAction()
    {
        /** @var AuthenticationService $authService */
        $authService = $this->getServiceLocator()->get('bkuser\auth\service');
        $authService->clearIdentity();
        $this->redirect()->toRoute('bkuser-login');
    }

}
