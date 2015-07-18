<?php

class Vtx_Action_Abstract extends Zend_Controller_Action
{
    public function __call($method, $args)
    {
        if (!preg_match('~^(set|get)([A-Z_])(.*)$~', $method, $matches)) { return; }
        $property = strtolower($matches[2]) . $matches[3];
        if (!$this->propertyExistsSafe($this, $property)) { throw new Exception("$property not exists");}
        if ($matches[1] == 'set') { $this->$property = $args[0]; return $this; }
        return $this->$property;
    }

    protected function propertyExistsSafe($class, $property)
    {
        $r = property_exists($class, $property);
        if (!$r) {
            $x = new ReflectionClass($class);
            $r = $x->hasProperty($property);
        }
        return $r;
    }

    public function init() {
        $writer = new Zend_Log_Writer_Stream(Zend_Registry::get('config')->paths->root.'/log/application.log');
        $this->logger = new Zend_Log($writer);
    }
}