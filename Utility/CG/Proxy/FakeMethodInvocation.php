<?php

namespace RRaven\Bundle\MenuAnnotationsBundle\Utility\CG\Proxy;

use CG\Proxy\MethodInvocation as BaseMethodInvocation;

class FakeMethodInvocation extends BaseMethodInvocation {
    
    public $reflection;
    public $object;
    public $arguments;

    private $interceptors;
    private $pointer;

    public function __construct(\ReflectionMethod $reflection, $object, array $arguments, array $interceptors)
    {
        $this->reflection = $reflection;
        $this->object = $object;
        $this->arguments = $arguments;
        $this->interceptors = $interceptors;
        $this->pointer = 0;
    }
    
    public function proceed() {
        if (isset($this->interceptors[$this->pointer])) {
            return $this->interceptors[$this->pointer++]->intercept($this);
        }

        //$this->reflection->setAccessible(true);

        // Ordinarily would call the method here, but we aren't doing so we can
        // just test if something is accessible via the security interceptor.
        
        //return $this->reflection->invokeArgs($this->object, $this->arguments);
    }

    /**
     * Returns a string representation of the method.
     *
     * This is intended for debugging purposes only.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s::%s', $this->reflection->class, $this->reflection->name);
    }
}