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
            'bkuser/login' => __DIR__ . '/../view/rd-user/auth/login.phtml',
            'bkuser/logout' => __DIR__ . '/../view/rd-user/auth/logout.phtml'
//            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
//            'landing/index/index' => __DIR__ . '/../view/index/index.phtml',
//            'landing/index/form' => __DIR__ . '/../view/index/form.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
