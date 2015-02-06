<?php

namespace BKUser;

use Zend\Authentication\Storage\Session as SessionStorage;
use BKUser\Authentication\AuthenticationService as BKUserAuthenticationService;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter as CallbackCheckAuthAdapter;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\Literal as LiteralRoute;

class Module
{

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'bkuser\auth\config' => array($this, 'factory_bkuser_auth_config'),
                'bkuser\auth\service' => array($this, 'factory_bkuser_auth_service'),
                'bkuser\form\loginform' => array($this, 'factory_bkuser_form_login'),
            )
        );
    }

    public function factory_bkuser_auth_config(ServiceManager $sm)
    {
        $cfg = $sm->get('config');

        if (!isset($cfg['bkuser\auth\config'])) {
            $cfg['bkuser\auth\config'] = array();
        }
        $cfg_options = $cfg['bkuser\auth\config'];

        $defaults = array(
            'table' => 'users',
            'identity_column' => 'username',
            'credential_column' => 'password',
            'user-entity' => '\bkuser\Model\User',
            'user-entity-hydrator' => '\Zend\Stdlib\Hydrator\ClassMethods',

            'messages' => array(
                AuthenticationResult::FAILURE                    => "The Username or Password was incorrect",
                AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND => "The Username or Password was incorrect",
                AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS => "The Username or Password was incorrect",
                AuthenticationResult::FAILURE_CREDENTIAL_INVALID => "The Username or Password was incorrect",
            ),

            'routes' => array(
                'success' => '/',
                'login' => '/user/login',
                'logout' => '/user/logout'
            )
        );

        return ArrayUtils::merge($defaults, $cfg_options);
    }

    public function onBootstrap(MvcEvent $event)
    {
        $this->bootstrapRoutes($event);
    }

    public function bootstrapRoutes(MvcEvent $event)
    {
        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
        $serviceManager = $event->getApplication()->getServiceManager();
        $config = $serviceManager->get('bkuser\auth\config');

        $routeConfig = $config['routes'];

        /** @var \Zend\Mvc\Router\Http\TreeRouteStack $r */
        $r = $serviceManager->get('router');

        /** @var \Zend\Mvc\Router\Http\Literal $loginRoute */
        $loginRoute = $r->getRoute('bkuser-login');
        $logoutRoute = $r->getRoute('bkuser-logout');
//        $loginRoute = $r->getRoute('bkuser-logout');

        if ($loginRoute->assemble() != $routeConfig['login']) {
            $newLoginRoute = new LiteralRoute($routeConfig['login'], [
                "controller" => "BKUser\\Controller\\Auth",
                "action" => "login"
            ]);
            $r->addRoute('bkuser-login', $newLoginRoute);
        }
        if ($logoutRoute->assemble() != $routeConfig['logout']) {
            $newLoginRoute = new LiteralRoute($routeConfig['logout'], [
                "controller" => "BKUser\\Controller\\Auth",
                "action" => "logout"
            ]);
            $r->addRoute('bkuser-logout', $newLoginRoute);
        }
//        \Zend\Debug\Debug::dump($r->getRoute('bkuser'));
//        var_dump($r->getRoute('bkuser'));
//        die();
//        \Zend\Debug\Debug::dump($r);
    }

    public function factory_bkuser_auth_service(ServiceManager $sm)
    {
        /** @var \Zend\Db\Adapter\Adapter $db */
        $db = $sm->get('Zend\Db\Adapter\Adapter');
        /** @var array $options */
        $options = $sm->get('bkuser\auth\config');

        $authAdapter = new CallbackCheckAuthAdapter(
            $db,
            $options['table'],
            $options['identity_column'],
            $options['credential_column'],
            function ($hash, $password) use ($sm, $options) {
                return password_verify($password, $hash);
            }
        );

        return new BKUserAuthenticationService(new SessionStorage('Authentication'), $authAdapter);
    }

    public function factory_bkuser_form_login(ServiceManager $sm)
    {
        return new Form\Login();
    }
}