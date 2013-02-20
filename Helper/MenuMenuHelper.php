<?php

namespace RRaven\Bundle\MenuAnnotationsBundle\Helper;

use RRaven\Bundle\MenuAnnotationsBundle\Annotations\Menu\Menu as MenuMenu;
use RRaven\Bundle\MenuAnnotationsBundle\Annotations\Menu\Item as MenuItem;
use JMS\SecurityExtraBundle\Annotation\Secure;
use RRaven\Bundle\MenuAnnotationsBundle\Utility\CG\Proxy\FakeMethodInvocation;
use CG\Proxy\MethodInterceptorInterface;
use Symfony\Component\Routing\RouterInterface;

use Doctrine\Common\Annotations\Reader as AnnotationReaderInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class MenuMenuHelper
{

    private $_annotation_reader = null;
    private $_logger = null;
    private $_router = null;
    private $_security_interceptor = null;

    public function __construct(AnnotationReaderInterface $annotation_reader, LoggerInterface $logger, RouterInterface $router, MethodInterceptorInterface $securityInterceptor)
    {
        $this->_annotation_reader = $annotation_reader;
        $this->_logger = $logger;
        $this->_router = $router;
        $this->_security_interceptor = $securityInterceptor;
    }

    /**
     * @param \Doctrine\Common\Annotations\FileCacheReader $annotation_reader
     */
    public function setAnnotationReader($annotation_reader)
    {
        $this->_annotation_reader = $annotation_reader;
    }

    private function getAnnotationReader()
    {
        return $this->_annotation_reader;
    }

    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }

    /**
     * @return \Monolog\Logger
     */
    private function getLogger()
    {
        return $this->_logger;
    }

    public function sniffMenus()
    {

        $menu_items = array();
        $menu_menus = array();

        $routes = $this->_router->getRouteCollection()->all();

        $annotation_reader = $this->getAnnotationReader();

        foreach ($routes as $route_name => $route) {
            /* @var $route \Symfony\Component\Routing\Route */
            $controller = $route->getDefault("_controller");
            if (strpos($controller, "::") !== false) {
                $bits = explode("::", $controller);
                $class = $bits[0];
                $method = $bits[1];

                $reflectionClass = new \ReflectionClass($class);
                $reflectionMethod = $reflectionClass->getMethod($method);
                
                try
                {
                    $fakeInvocation = new FakeMethodInvocation($reflectionMethod, $reflectionClass, array(), array($this->_security_interceptor));
                    $fakeInvocation->proceed();
                    
                    $methodAnnotations = $annotation_reader->getMethodAnnotations($reflectionMethod);

                    foreach ($methodAnnotations as $annotation) {
                        if ($annotation instanceof MenuItem) {
                            $annotation->setRoute("@" . $route_name);
                            $menu_items[] = $annotation;
                        } else if ($annotation instanceof MenuMenu) {
                            $menu_menus[] = $annotation;
                        }
                    }
                }
                catch (\Exception $E) {
                    $E = $E;
                }
            }
        }
        
        $menu = new MenuMenu(array());
        $menu->setIsRoot(true);
        
        foreach ($menu_items as $item /* @var $item MenuItem */) {
            $menu->addItem($item);
        }
        
        foreach ($menu_menus as $submenu /* @var $menu MenuMenu */) {
            $menu->addMenu($submenu);
        }
        
        $menu->sort();
        
        return $menu->toArray();
    }

}

?>
