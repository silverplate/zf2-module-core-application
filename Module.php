<?php

namespace CoreApplication;

use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

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

    public function onBootstrap(MvcEvent $_event)
    {
        $application = $_event->getApplication();
        $sm = $application->getServiceManager();
        $em = $application->getEventManager();

        GlobalAdapterFeature::setStaticAdapter(
            $sm->get('\Zend\Db\Adapter\Adapter')
        );

        $em->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            array($this, 'handleError')
        );

        $em->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            array($this, 'handleError')
        );
    }

    public function handleError(MvcEvent $_event)
    {
        $_event->getViewModel()->setTemplate('error/layout');
    }
}
