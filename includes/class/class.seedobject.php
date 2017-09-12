<?php
/* Class d'usurpation de coreobject.class.php de Dolibarr
 * 
 * Copyright (C) 2016		ATM Consulting			<support@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       htdocs/core/class/coreobject.class.php
 *	\ingroup    core
 *	\brief      File of class to manage all object. Might be replace or merge into commonobject
 */
 
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

class SeedObject extends CommonObject
{
	public $withChild = true;

	/**
	 *  @var Array $_fields Fields to synchronize with Database
	 */
	protected $fields=array();

    /**
	 *  Constructor
	 *
	 *  @param      DoliDB		$db      Database handler
	 */
	function __construct(DoliDB &$db)
    {
        $this->db = $db;
	}

    /**
     * Function to init fields
     *
     * @return bool
     */
	protected function init()
    {
		$this->id = 0;
		$this->datec = 0;
		$this->tms = 0;
		
		if (!empty($this->fields))
		{
			foreach ($this->fields as $field=>$info)
			{
		        if ($this->isDate($info)) $this->{$field} = time();
		        elseif ($this->isArray($info)) $this->{$field} = array();
		        elseif ($this->isInt($info)) $this->{$field} = (int) 0;
		        elseif ($this->isFloat($info)) $this->{$field} = (double) 0;
				else $this->{$field} = '';
		    }

            $this->to_delete=false;
            $this->is_clone=false;
			
			return true;
		}
		else
        {
			return false;
		}
			
	}

    /**
     * Test type of field
     *
     * @param   string  $field  name of field
     * @param   string  $type   type of field to test
     * @return                  value of field or false
     */
    private function checkFieldType($field, $type)
    {
		if (isset($this->fields[$field]) && method_exists($this, 'is_'.$type))
		{
			return $this->{'is_'.$type}($this->fields[$field]);
		}
		else
        {
            return false;
        }
	}

    /**
     *	Get object and children from database
     *
     *	@param      int			$id       		Id of object to load
     * 	@param		bool		$loadChild		used to load children from database
     *	@return     int         				>0 if OK, <0 if KO, 0 if not found
     */
	public function fetch($id, $loadChild = true)
    {
    	$res = $this->fetchCommon($id);
    	if($res>0) {
    		if ($loadChild) $this->fetchChild();
    	}
    	
    	return $res;
	}


    /**
     * Function to instantiate a new child
     *
     * @param   string  $tabName        Table name of child
     * @param   int     $id             If id is given, we try to return his key if exist or load if we try_to_load
     * @param   string  $key            Attribute name of the object id
     * @param   bool    $try_to_load    Force the fetch if an id is given
     * @return                          int
     */
    public function addChild($tabName, $id=0, $key='id', $try_to_load = false)
    {
		if(!empty($id))
		{
			foreach($this->{$tabName} as $k=>&$object)
			{
				if($object->{$key} === $id) return $k;
			}
		}
	
		$k = count($this->{$tabName});
	
		$className = ucfirst($tabName);
		$this->{$tabName}[$k] = new $className($this->db);
		if($id>0 && $key==='id' && $try_to_load)
		{
			$this->{$tabName}[$k]->fetch($id); 
		}

		return $k;
	}


    /**
     * Function to set a child as to delete
     *
     * @param   string  $tabName        Table name of child
     * @param   int     $id             Id of child to set as to delete
     * @param   string  $key            Attribute name of the object id
     * @return                          bool
     */
    public function removeChild($tabName, $id, $key='id')
    {
		foreach ($this->{$tabName} as &$object)
		{
			if ($object->{$key} == $id)
			{
				$object->to_delete = true;
				return true;
			}
		}
		return false;
	}


    /**
     * Function to fetch children objects
     */
    public function fetchChild()
    {
		if($this->withChild && !empty($this->childtables) && !empty($this->fk_element))
		{
			foreach($this->childtables as &$childTable)
			{
                $className = ucfirst($childTable);

                $this->{$className}=array();

                $sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.$childTable.' WHERE '.$this->fk_element.' = '.$this->id;
                $res = $this->db->query($sql);

                if($res)
                {
                    while($obj = $this->db->fetch_object($res))
                    {
                        $o=new $className($this->db);
                        $o->fetch($obj->rowid);

                        $this->{$className}[] = $o;
                    }
                }
                else
                {
                    $this->errors[] = $this->db->lasterror();
                }
			}
		}
	}

    /**
     * Function to update children data
     *
     * @param   User    $user   user object
     */
	public function saveChild(User &$user)
    {
		if($this->withChild && !empty($this->childtables) && !empty($this->fk_element))
		{
			foreach($this->childtables as &$childTable)
			{
				$className = ucfirst($childTable);
				if(!empty($this->{$className}))
				{
					foreach($this->{$className} as $i => &$object)
					{
						$object->{$this->fk_element} = $this->id;
						
						$object->update($user);
						if($this->unsetChildDeleted && isset($object->to_delete) && $object->to_delete==true) unset($this->{$className}[$i]);
					}
				}
			}
		}
	}


    /**
     * Function to update object or create or delete if needed
     *
     * @param   User    $user   user object
     * @return                  < 0 if ko, > 0 if ok
     */
    public function update(User &$user)
    {
		if (empty($this->id)) return $this->create($user); // To test, with that, no need to test on high level object, the core decide it, update just needed
        elseif (isset($this->to_delete) && $this->to_delete==true) return $this->delete($user);

        $error = 0;
        $this->db->begin();

        $res = $this->updateCommon($user);
        if ($res)
        {
            $result = $this->call_trigger(strtoupper($this->element). '_UPDATE', $user);
            if ($result < 0) $error++;
            else $this->saveChild($user);
        }
        else
        {
            $error++;
            $this->error = $this->db->lasterror();
            $this->errors[] = $this->error;
        }

        if (empty($error))
        {
            $this->db->commit();
            return $this->id;
        }
        else
        {
            $this->db->rollback();
            return -1;
        }

	}

    /**
     * Function to create object in database
     *
     * @param   User    $user   user object
     * @return                  < 0 if ko, > 0 if ok
     */
    public function create(User &$user)
    {
		if($this->id > 0) return $this->update($user);

        $error = 0;
        $this->db->begin();

        $res = $this->createCommon($user);
		if($res)
		{
			$this->id = $this->db->last_insert_id($this->table_element);

			$result = $this->call_trigger(strtoupper($this->element). '_CREATE', $user);
            if ($result < 0) $error++;
            else $this->saveChild($user);
		}
		else
        {
            $error++;
            $this->error = $this->db->lasterror();
            $this->errors[] = $this->error;
		}

        if (empty($error))
        {
            $this->db->commit();
            return $this->id;
        }
        else
        {
            $this->db->rollback();
            return -1;
        }
	}

    /**
     * Function to delete object in database
     *
     * @param   User    $user   user object
     * @return                  < 0 if ko, > 0 if ok
     */
	public function delete(User &$user)
    {
		if ($this->id <= 0) return 0;

        $error = 0;
        $this->db->begin();

        $result = $this->call_trigger(strtoupper($this->element). '_DELETE', $user);
        if ($result < 0) $error++;

        if (!$error)
        {
            $this->deleteCommon($user);
            if($this->withChild && !empty($this->childtables))
            {
                foreach($this->childtables as &$childTable)
                {
                    $className = ucfirst($childTable);
                    if (!empty($this->{$className}))
                    {
                        foreach($this->{$className} as &$object)
                        {
                            $object->delete($user);
                        }
                    }
                }
            }
        }

        if (empty($error))
        {
            $this->db->commit();
            return 1;
        }
        else
        {
            $this->error = $this->db->lasterror();
            $this->errors[] = $this->error;
            $this->db->rollback();
            return -1;
        }
	}


    /**
     * Function to get a formatted date
     *
     * @param   string  $field  Attribute to return
     * @param   string  $format Output date format
     * @return          string
     */
    public function getDate($field, $format='')
    {
		if(empty($this->{$field})) return '';
		else
        {
			return dol_print_date($this->{$field}, $format);
		}
	}

    /**
     * Function to set date in field
     *
     * @param   string  $field  field to set
     * @param   string  $date   formatted date to convert
     * @return                  mixed
     */
    public function setDate($field, $date)
    {
	  	if (empty($date))
	  	{
	  		$this->{$field} = 0;
	  	}
		else
        {
			require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
			$this->{$field} = dol_stringtotime($date);
		}

		return $this->{$field};
	}


    /**
     * Function to update current object
     *
     * @param   array   $Tab    Array of values
     * @return                  int
     */
    public function setValues(&$Tab)
    {
		foreach ($Tab as $key => $value)
		{
			if($this->checkFieldType($key, 'date'))
			{
				$this->setDate($key, $value);
			}
			else if( $this->checkFieldType($key, 'array'))
			{
				$this->{$key} = $value;
			}
			else if( $this->checkFieldType($key, 'float') )
			{
				$this->{$key} = (double) price2num($value);
			}
			else if( $this->checkFieldType($key, 'int') ) {
				$this->{$key} = (int) price2num($value);
			}
			else
            {
				$this->{$key} = $value;
			}
		}

		return 1;
	}
	
	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function createCommon(User $user, $notrigger = false)
	{
		// method_exists() with key word 'parent' doesn't work
		if (is_callable('parent::createCommon')) return parent::createCommon($user, $notrigger);
		
		
        $error = 0;

        $now=dol_now();

	    $fieldvalues = $this->set_save_query();
		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation']=$this->db->idate($now);
		unset($fieldvalues['rowid']);	// We suppose the field rowid is reserved field for autoincrement field.

	    $keys=array();
	    $values = array();
	    foreach ($fieldvalues as $k => $v) {
	    	$keys[] = $k;
	    	$values[] = $this->quote($v, $this->fields[$k]);
	    }

	    $this->db->begin();

	    if (! $error)
	    {
    	    $sql = 'INSERT INTO '.MAIN_DB_PREFIX.$this->table_element;
    		$sql.= ' ('.implode( ", ", $keys ).')';
    		$sql.= ' VALUES ('.implode( ", ", $values ).')';

			$res = $this->db->query( $sql );
    	    if ($res===false) {
    	        $error++;
    	        $this->errors[] = $this->db->lasterror();
    	    }
	    }

        if (! $error && ! $notrigger) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);

            if (!$notrigger) {
                // Call triggers
                $result=$this->call_trigger(strtoupper(get_class($this)).'_CREATE',$user);
                if ($result < 0) { $error++; }
                // End call triggers
            }
        }

		// Commit or rollback
		if ($error) {
			$this->db->rollback();
			return -1;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}
	
	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchCommon($id, $ref = null)
	{
		// method_exists() with key word 'parent' doesn't work
		if (is_callable('parent::fetchCommon')) return parent::fetchCommon($id, $ref);
		
		
		if (empty($id) && empty($ref)) return false;

		$sql = 'SELECT '.$this->get_field_list().', date_creation, tms';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element;

		if(!empty($id)) $sql.= ' WHERE rowid = '.$id;
		else $sql.= " WHERE ref = ".$this->quote($ref, $this->fields['ref']);

		$res = $this->db->query($sql);
		if ($res)
		{
    		if ($obj = $this->db->fetch_object($res))
    		{
    		    if ($obj)
    		    {
        			$this->id = $id;
        			$this->set_vars_by_db($obj);

        			$this->date_creation = $this->db->idate($obj->date_creation);
        			$this->tms = $this->db->idate($obj->tms);

        			return $this->id;
    		    }
    		    else
    		    {
    		        return 0;
    		    }
    		}
    		else
    		{
    			$this->error = $this->db->lasterror();
    			$this->errors[] = $this->error;
    			return -1;
    		}
		}
		else
		{
		    $this->error = $this->db->lasterror();
		    $this->errors[] = $this->error;
		    return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function updateCommon(User $user, $notrigger = false)
	{
		// method_exists() with key word 'parent' doesn't work
		if (is_callable('parent::updateCommon')) return parent::updateCommon($user, $notrigger);
		
		
	    $error = 0;

		$fieldvalues = $this->set_save_query();
		unset($fieldvalues['rowid']);	// We don't update this field, it is the key to define which record to update.

		foreach ($fieldvalues as $k => $v) {
			if (is_array($key)){
				$i=array_search($k, $key);
				if ( $i !== false) {
					$where[] = $key[$i].'=' . $this->quote($v, $this->fields[$k]);
					continue;
				}
			} else {
				if ( $k == $key) {
					$where[] = $k.'=' .$this->quote($v, $this->fields[$k]);
					continue;
				}
			}
			$tmp[] = $k.'='.$this->quote($v, $this->fields[$k]);
		}
		$sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element.' SET '.implode( ',', $tmp ).' WHERE rowid='.$this->id ;

		$this->db->begin();
		if (! $error)
		{
    		$res = $this->db->query($sql);
    		if ($res===false)
    		{
    		    $error++;
    	        $this->errors[] = $this->db->lasterror();
    		}
		}

		if (! $error && ! $notrigger) {
		    // Call triggers
		    $result=$this->call_trigger(strtoupper(get_class($this)).'_MODIFY',$user);
		    if ($result < 0) { $error++; } //Do also here what you must do to rollback action if trigger fail
		    // End call triggers
		}

		// Commit or rollback
		if ($error) {
		    $this->db->rollback();
		    return -1;
		} else {
		    $this->db->commit();
		    return $this->id;
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function deleteCommon(User $user, $notrigger = false)
	{
		// method_exists() with key word 'parent' doesn't work
		if (is_callable('parent::deleteCommon')) return parent::deleteCommon($user, $notrigger);
		
		
	    $error=0;

	    $this->db->begin();

	    if (! $error) {
	        if (! $notrigger) {
	            // Call triggers
	            $result=$this->call_trigger(strtoupper(get_class($this)).'_DELETE', $user);
	            if ($result < 0) { $error++; } // Do also here what you must do to rollback action if trigger fail
	            // End call triggers
	        }
	    }

	    if (! $error)
	    {
    		$sql = 'DELETE FROM '.MAIN_DB_PREFIX.$this->table_element.' WHERE rowid='.$this->id;

    		$res = $this->db->query($sql);
    		if($res===false) {
    		    $error++;
    		    $this->errors[] = $this->db->lasterror();
    		}
	    }

    	// Commit or rollback
		if ($error) {
		    $this->db->rollback();
		    return -1;
		} else {
		    $this->db->commit();
		    return 1;
		}
	}
	
	
	function addFieldsInDb()
	{
		$resql = $this->db->query('SHOW FIELDS FROM `' . $this->table_element . '`');
		$Tab = array();
		while ($obj = $this->db->fetch_object($resql))
		{
			$Tab[] = $this->db->Field;
		}

		$TChamps = array_merge(array(OBJETSTD_DATECREATE => array('type' => 'date'), OBJETSTD_DATEUPDATE => array('type' => 'date')), $this->fields);

		foreach ($TChamps as $champs => $info)
		{
			if (!in_array($champs, $Tab))
			{
				if ($this->isInt($info))
				{
					$this->db->query('ALTER TABLE `' . $this->table_element . '` ADD `' . $champs . '` int(11) NOT NULL DEFAULT \'' . (!empty($info['default']) && is_int($info['default']) ? $info['default'] : '0') . '\'');
				}
				else if ($this->isDate($info))
				{
					$this->db->query('ALTER TABLE `' . $this->table_element . '` ADD `' . $champs . '` datetime NULL');
				}
				else if ($this->isFloat($info))
				{
					$this->db->query('ALTER TABLE `' . $this->table_element . '` ADD `' . $champs . '` DOUBLE NOT NULL DEFAULT \'' . (!empty($info['default']) ? $info['default'] : '0') . '\'');
				}
				else if ($this->isArray($info) || $this->isText($info))
				{
					$this->db->query('ALTER TABLE `' . $this->table_element . '` ADD `' . $champs . '` LONGTEXT');
				}
				else
				{
					$this->db->query('ALTER TABLE `' . $this->table_element . '` ADD `' . $champs . '` VARCHAR(' . (is_array($info) && !empty($info['length']) ? $info['length'] : 255 ) . ')');
				}
				
				if ($this->isIndex($info))
				{
					$this->db->query('ALTER TABLE ' . $this->table_element . ' ADD INDEX `' . $champs . '`(`' . $champs . '`)');
				}
			}
		}
	}

	function init_db_by_vars()
	{
		global $conf;

		$resql = $this->db->query("SHOW TABLES FROM `" . DB_NAME . "` LIKE '" . $this->table_element . "'");
		if ($resql && $this->db->num_rows($resql) == 0)
		{
			/*
			 * La table n'existe pas, on la crée
			 */
			$charset = $conf->db->character_set;

			$sql = "CREATE TABLE `" . $this->table_element . "` (
 				`" . OBJETSTD_MASTERKEY . "` int(11) NOT NULL DEFAULT '0'
 				,`" . OBJETSTD_DATECREATE . "` datetime NULL
 				,`" . OBJETSTD_DATEUPDATE . "` datetime NULL

 				,PRIMARY KEY (`" . OBJETSTD_MASTERKEY . "`)
 				,KEY `" . OBJETSTD_DATECREATE . "` (`" . OBJETSTD_DATECREATE . "`)
 				,KEY `" . OBJETSTD_DATEUPDATE . "` (`" . OBJETSTD_DATEUPDATE . "`)
 				) ENGINE=InnoDB DEFAULT CHARSET=" . $charset;

			$this->db->query($sql);
		}

		$this->addFieldsInDb();
	}

	
}
