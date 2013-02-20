<?php

namespace RRaven\Bundle\MenuAnnotationsBundle\Annotations\Menu;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Item extends Annotation
{

    /**
     * The name to display on the menu
     *
     * @var string
     */
    public $name = null;

    /**
     * 
     * 
     * @var string
     */
    public $path = null;

    /**
     * Attempts to place the annotated item before the given route.
     *
     * @var string 
     */
    public $before = null;
    
    private $_route = null;
    
    public function getName()
    {
        return $this->name ? $this->name : $this->value;
    }

    public function getPath()
    {
        if (!is_null($this->path) && !is_array($this->path)) {
            $this->path = array($this->path);
        }
        
        return $this->path;
    }
    
    public function setRoute($route) {
        $this->_route = $route;
    }
    
    public function getRoute() {
        return $this->_route;
    }

}