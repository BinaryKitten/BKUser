<?php

namespace BKUser;

use Zend\Authentication\Storage\Session as SessionStorage;
use BKUser\Authentication\AuthenticationService as BKUserAuthenticationService;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter as CallbackCheckAuthAdapter;
use Zend\Http\Request as HttpRequest;
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
            'aliases' => array(
                'Zend\Authentication\AuthenticationService' => 'bkuser\auth\service',
            ),
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
            'form-class' => null,
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
        $request = $event->getRequest();

        if (!$request instanceof HttpRequest) {
            return;
        }

        /** @var \Zend\ServiceManager\ServiceManager $serviceManager */
        $serviceManager = $event->getApplication()->getServiceManager();
        $config = $serviceManager->get('bkuser\auth\config');

        $routeConfig = $config['routes'];

        /** @var \Zend\Mvc\Router\Http\TreeRouteStack $r */
        $router = $serviceManager->get('router');

        /** @var \Zend\Mvc\Router\Http\Literal $loginRoute */
        $loginRoute = $router->getRoute('bkuser-login');

        /** @var \Zend\Mvc\Router\Http\Literal $loginRoute */
        $logoutRoute = $router->getRoute('bkuser-logout');

        if ($loginRoute->assemble() != $routeConfig['login']) {
            $newLoginRoute = new LiteralRoute($routeConfig['login'], [
                "controller" => Controller\AuthController::class,
                "action" => "login"
            ]);
            $router->addRoute('bkuser-login', $newLoginRoute);
        }
        if ($logoutRoute->assemble() != $routeConfig['logout']) {
            $newLoginRoute = new LiteralRoute($routeConfig['logout'], [
                "controller" => Controller\AuthController::class,
                "action" => "logout"
            ]);
            $router->addRoute('bkuser-logout', $newLoginRoute);
        }
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
        $config = $sm->get('bkuser\auth\config');

        if (isset($config['form-class'])) {
            if (class_exists($config['form-class'])) {
                return new $config['form-class'];
            } else {
                return $sm->get($config['form-class']);
            }
        }
        return new Form\Login();
    }
}
