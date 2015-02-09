<?php

namespace BKUser;

return [
    'router' => [
        'routes' => [
            'bkuser-login' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/user/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login'
                    ]
                ]
            ],
            'bkuser-logout' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/user/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout'
                    ]
                ]
            ]
        ],
    ],
    'controllers' => [
        'invokables' => [
            Controller\AuthController::class => Controller\AuthController::class
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'bk-user/auth/login' => __DIR__ . '/../view/bk-user/auth/login.phtml',
            'bk-user/auth/logout' => __DIR__ . '/../view/bk-user/auth/logout.phtml'
        ],

    ],
];
