<?php

/**
 * Singletown Factory getInstance para PHP 5.2 já que só 5.3 funciona get_class_name()
 * Vtx_Model
 * @author tsouza
 *
 */
class Vtx_Model
{
    protected $properties = array();

    public function __get($key)
    {
        if(property_exists($this,'properties') && is_array($this->properties))
        {
            return isset($this->properties[$key]) ? $this->properties[$key] : null;
        }
    }

    public function __set($key,$value)
    {
        if(property_exists($this,'properties') && is_array($this->properties))
        {
            $this->properties[$key] = $value;
        }
    }
}
