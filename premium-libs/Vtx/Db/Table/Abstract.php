<?php

/**
 * 
 * Vtx_Db_Table_Abstract Db_Table
 * @uses Zend_Db_Table_Abstract
 * @author tsouza
 * @return Zend_Db_Table
 *
 */
class Vtx_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    protected $_rowClass = 'Vtx_Db_Table_Row_Abstract';   
    
    /**
     * $_name - Name of database table
     *
     * @return string
     */
    protected $_name;

    /**
     * $_id - The primary key name(s)
     *
     * @return string|array
     */
    protected $_id;
    

	public function __construct($config = null)
	{
		return parent::__construct(
			isset($this->_adapter)===false? null : Zend_Registry::get($this->_adapter)
		);
	}

    /**
     * Returns the primary key column name(s)
     *
     * @return string|array
     */
    public function getPrimaryKeyName()
    {
        return $this->_id;
    }

    /**
     * Returns the table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->_name;
    }

    /**
     * Returns the number of rows in the table
     *
     * @return int
     */
    public function countAllRows()
    {
        $query = $this->select()->from($this->_name, 'count(*) AS all_count');
        $numRows = $this->fetchRow($query);

        return $numRows['all_count'];
    }

    /**
     * Returns the number of rows in the table with optional WHERE clause
     *
     * @param $where mixed Where clause to use with the query
     * @return int
     */
    public function countByQuery($where = '')
    {
        $query = $this->select()->from($this->_name, 'count(*) AS all_count');

		if (! empty($where) && is_string($where))
        {
            $query->where($where);
        }
        elseif(is_array($where) && isset($where[0]))
        {
			foreach($where as $i => $v)
			{
				/**
				 * Checks if you're passing an PDO escape statement
				 * ->where('price > ?', $price)
				 */
				if(isset($v[1]) && is_string($v[0]) && count($v) == 2)
				{
					$query->where($v[0], $v[1]);
				}
				elseif(is_string($v))
				{
					$query->where($v);
				}
			}
        }
        else
        {
            throw new Exception("You must pass integer indexes on the select statement array.");
        }


        $row = $this->getAdapter()->query($query)->fetch();

        return $row['all_count'];
    }

    /**
     * Generates a query to fetch a list with the given parameters
     *
     * @param $where mixed Where clause to use with the query
     * @param $order string Order clause to use with the query
     * @param $count int Maximum number of results
     * @param $offset int Offset for the limited number of results
     * @return Zend_Db_Select
     */
    public function fetchList($where = null, $order = null, $count = null, $offset = null)
    {
        $select = $this->select()
                    ->order($order)
                    ->limit($count, $offset);

        if (! empty($where) && is_string($where))
        {
            $select->where($where);
        }
        elseif(is_array($where) && isset($where[0]))
        {
			/**
			 * Adds a where/and statement for each of the inner arrays, and checks if it is a PDO escape statement or a string
			 */
			foreach($where as $i => $v)
			{
				if(isset($v[1]) && is_string($v[0]) && count($v) == 2)
				{
					$select->where($v[0], $v[1]);
				}
				elseif(is_string($v))
				{
					$select->where($v);
				}
			}
        }
        else
        {
            throw new Exception("You must pass integer indexes on the select statement array.");
        }

        return $select;
    }

    /**
     * Returns an instance of Zend_Db_Table for php 5.3
     *
     * Singleton pattern implementation
     *
     * @return Zend_Db_Table_Abstract Provides a fluent interface
     */
	final public static function getInstance()
	{
		static $aoInstance = array();

        $calledClassName = get_called_class();

		if( !isset($aoInstance[$calledClassName]) ) $aoInstance[$calledClassName] = new $calledClassName();

		return $aoInstance[$calledClassName];
	}
    
    /**
     * @param string $tableClassname
     * @param string $ruleKey OPTIONAL
     * @return array
     * @throws Zend_Db_Table_Exception
     */
    public function getReference($tableClassname, $ruleKey = null)
    {
        $tableClassname = str_replace('DbTable_', '', $tableClassname);
        return parent::getReference($tableClassname, $ruleKey);
    }
    
	public function fetch($select, $fetchMode = 'select')
	{
		switch ($fetchMode)
		{
			case 'pairs':
				return $this->getAdapter()->fetchPairs($select);
			break;
			
			case 'assoc':
				return $this->getAdapter()->fetchAssoc($select);
			break;
			
			case 'col':
				return $this->getAdapter()->fetchCol($select);
			break;
			
			case 'all':
				return $this->fetchAll($select);
			break;
			
			case 'row':
				return $this->fetchRow($select);
			break;
        
			case 'one':
				return $this->getAdapter()->fetchOne($select);
			break;
			
			case 'select':
				return $select;
			break;
		} 	
	}
}