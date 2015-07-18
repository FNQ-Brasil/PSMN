<?php

/**
 * 
 * Vtx_Db_Table_Row_Abstract Db_Table_Row
 * @uses Zend_Db_Table_Row_Abstract
 * @author tsouza
 * @return Zend_Db_Table_Row
 *
 */
class Vtx_Db_Table_Row_Abstract extends Zend_Db_Table_Row_Abstract
{
	public function view()
	{
		if (!$this->_view) {
            $this->_view = Zend_Registry::get('view');
        }
		return $this->_view;
	}

	public function escape( $str )
	{
		return $this->view()->escape( $str );
	}

    /**
     * _getTableFromString
     *
     * @param string $tableName
     * @return Zend_Db_Table_Abstract
     */
    protected function _getTableFromString($tableName)
    {
        $tableName = 'DbTable_' . str_replace('DbTable_', '', $tableName);
        return parent::_getTableFromString($tableName);
    }
    
    public function __call($method, array $args)
    {
        if (!preg_match('~^(set|get)([A-Z_])(.*)$~', $method, $matches)) {
            return parent::__call($method, $args);
        }
         //$property = strtolower($matches[2]) . $matches[3];
         $property = $matches[2] . $matches[3];
        if ($matches[1] == 'set') {
            $this->__set($property, $args[0]);
            return $this;
        }
        return $this->__get($property);
    }

}