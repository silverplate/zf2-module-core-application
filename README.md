Common scripts for Zend Framework 2 projects
============================================

To add core module to your project do:

    cd `zf2 app folder`
    git submodule add https://github.com/silverplate/zf2-module-core-application.git module/CoreApplication

Use core in your application project module
-------------------------------------------

`module/Application/config/module.config.php` could be:

    <?php

    return array(
        'router' => array(
            'routes' => array(
                'home' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Index',
                            'action' => 'index'
                        )
                    )
                )
            )
        ),

        'controllers' => array(
            'invokables' => array(
                'Application\Controller\Index' =>
                    'Application\Controller\IndexController'
            ),
        ),
    );

`module/Application/Module.php` could be:

    <?php

    namespace Application;

    class Module extends \CoreApplication\Module
    {
        public function getConfig()
        {
            $config = include __DIR__ . '/config/module.config.php';
            $config = array_merge(parent::getConfig(), $config);

            $config['view_manager']['template_path_stack'][] = __DIR__ . '/view';

            return $config;
        }

        public function getAutoloaderConfig()
        {
            return array_merge_recursive(
                parent::getAutoloaderConfig(),
                array(
                    'Zend\Loader\StandardAutoloader' => array(
                        'namespaces' => array(
                            __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                        ),
                    ),
                )
            );
        }
    }
