<?php

return array(
    'router' => array(
        'routes' => array(
            'bkuser' => array(
                'type' => "literal",
                'options' => array(
                    'route' => '/user/demo',
                    'defaults' => array(
                        'controller' => 'BKUser\Controller\Index',
                        'action' => 'redirecttohome'
                    )
                ),
            ),

            'bkuser-login' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/user/login',
                    'defaults' => array(
                        'controller' => 'BKUser\Controller\Auth',
                        'action' => 'login'
                    )
                )
            ),
            'bkuser-logout' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/user/logout',
                    'defaults' => array(
                        'controller' => 'BKUser\Controller\Auth',
                        'action' => 'logout'
                    )
                )
            )
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'BKUser\Controller\Index' => 'BKUser\Controller\IndexController',
            'BKUser\Controller\Auth' => 'BKUser\Controller\AuthController'
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'bk-user/auth/login' => __DIR__ . '/../view/bk-user/auth/login.phtml',
            'bk-user/auth/logout' => __DIR__ . '/../view/bk-user/auth/logout.phtml'
        ),

    ),
);
