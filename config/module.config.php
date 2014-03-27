<?php

$dir = realpath(__DIR__ . '/../view');

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'CoreApplication\Controller\Index',
                        'action'     => 'index'
                    )
                )
            )
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'CoreApplication\Controller\Index' =>
                'CoreApplication\Controller\IndexController'
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_path_stack'      => array($dir),
        'template_map' => array(
            'layout/layout'        => $dir . '/layout/layout.phtml',
            'error/404'            => $dir . '/error/404.phtml',
            'error/index'          => $dir . '/error/index.phtml',
            'core-application/index/index' =>
                $dir . '/core-application/index/index.phtml',
        ),
    )
);
