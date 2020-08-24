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

if ((float) DOL_VERSION < 7.0)
{
	class SeedObjectDolibarr extends CommonObject
	{
		/**
		* Function test if type is date
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   protected function isDate($info)
	   {
		   if (is_callable('parent::isDate')) return parent::isDate($info);

		   if(isset($info['type']) && ($info['type']=='date' || $info['type']=='datetime' || $info['type']=='timestamp')) return true;
		   else return false;
	   }

	   /**
		* Function test if type is array
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   protected function isArray($info)
	   {
		   if (is_callable('parent::isArray')) return parent::isArray($info);

		   if(is_array($info))
		   {
			   if(isset($info['type']) && $info['type']=='array') return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if type is null
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   protected function isNull($info)
	   {
		   if (is_callable('parent::isNull')) return parent::isNull($info);

		   if(is_array($info))
		   {
			   if(isset($info['type']) && $info['type']=='null') return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if type is integer
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   protected function isInt($info)
	   {
		   if (is_callable('parent::isInt')) return parent::isInt($info);

		   if(is_array($info))
		   {
			   if(isset($info['type']) && ($info['type']=='int' || $info['type']=='integer' )) return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if type is float
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   protected function isFloat($info)
	   {
		   if (is_callable('parent::isFloat')) return parent::isFloat($info);

		   if(is_array($info))
		   {
			   if (isset($info['type']) && (preg_match('/^(double|real)/i', $info['type']))) return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if type is text
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   protected function isText($info)
	   {
		   if (is_callable('parent::isText')) return parent::isText($info);

		   if(is_array($info))
		   {
			   if(isset($info['type']) && $info['type']=='text') return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if is indexed
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   protected function isIndex($info)
	   {
		   if (is_callable('parent::isIndex')) return parent::isIndex($info);

		   if(is_array($info))
		   {
			   if(isset($info['index']) && $info['index']==true) return true;
			   else return false;
		   }
		   else return false;
	   }

		/**
		 * Function to concat keys of fields
		 *
		 * @return string
		 */
		public function get_field_list()
		{
			$keys = array_keys($this->fields);
			return implode(',', $keys);
		}

		/**
		 * Function to load data into current object this
		 *
		 * @param   stdClass    $obj    Contain data of object from database
		 */
		protected function set_vars_by_db(&$obj)
		{
			foreach ($this->fields as $field => $info)
			{
				if($this->isDate($info))
				{
					if(empty($obj->{$field}) || $obj->{$field} === '0000-00-00 00:00:00' || $obj->{$field} === '1000-01-01 00:00:00') $this->{$field} = 0;
					else $this->{$field} = strtotime($obj->{$field});
				}
				elseif($this->isArray($info))
				{
					$this->{$field} = @unserialize($obj->{$field});
					// Hack for data not in UTF8
					if($this->{$field } === FALSE) @unserialize(utf8_decode($obj->{$field}));
				}
				elseif($this->isInt($info))
				{
					$this->{$field} = (int) $obj->{$field};
				}
				elseif($this->isFloat($info))
				{
					$this->{$field} = (double) $obj->{$field};
				}
				elseif($this->isNull($info))
				{
					$val = $obj->{$field};
					// zero is not null
					$this->{$field} = (is_null($val) || (empty($val) && $val!==0 && $val!=='0') ? null : $val);
				}
				else
				{
					$this->{$field} = $obj->{$field};
				}
			}
		}

		/**
		 * Function to prepare the values to insert.
		 * Note $this->${field} are set by the page that make the createCommon or the updateCommon.
		 *
		 * @return array
		 */
		protected function set_save_query()
		{
			global $conf;
			$queryarray=array();
			foreach ($this->fields as $field=>$info)	// Loop on definition of fields
			{
				// Depending on field type ('datetime', ...)
				if($this->isDate($info))
				{
					if(empty($this->{$field}))
					{
						$queryarray[$field] = NULL;
					}
					else
					{
						$queryarray[$field] = $this->db->idate($this->{$field});
					}
				}
				else if($this->isArray($info))
				{
					$queryarray[$field] = serialize($this->{$field});
				}
				else if($this->isInt($info))
				{
					if ($field == 'entity' && is_null($this->{$field})) $queryarray[$field]=$conf->entity;
					else
					{
						$queryarray[$field] = (int) price2num($this->{$field});
						if (empty($queryarray[$field])) $queryarray[$field]=0;		// May be rest to null later if property 'nullifempty' is on for this field.
					}
				}
				else if($this->isFloat($info))
				{
					$queryarray[$field] = (double) price2num($this->{$field});
					if (empty($queryarray[$field])) $queryarray[$field]=0;
				}
				else
				{
					$queryarray[$field] = $this->{$field};
				}
				if ($info['type'] == 'timestamp' && empty($queryarray[$field])) unset($queryarray[$field]);
				if (! empty($info['nullifempty']) && empty($queryarray[$field])) $queryarray[$field] = null;
			}
			return $queryarray;
		}

		/**
		 * Add quote to field value if necessary
		 *
		 * @param 	string|int	$value			Value to protect
		 * @param	array		$fieldsentry	Properties of field
		 * @return 	string
		 */
		protected function quote($value, $fieldsentry) {
			if (is_null($value)) return 'NULL';
			else if (preg_match('/^(int|double|real)/i', $fieldsentry['type'])) return $this->db->escape("$value");
			else return "'".$this->db->escape($value)."'";
		}
	}

}
else
{
	class SeedObjectDolibarr extends CommonObject
	{
		/**
		* Function test if type is date
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   public function isDate($info)
	   {
		   if (is_callable('parent::isDate')) return parent::isDate($info);

		   if(isset($info['type']) && ($info['type']=='date' || $info['type']=='datetime' || $info['type']=='timestamp')) return true;
		   else return false;
	   }

	   /**
		* Function test if type is array
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   public function isArray($info)
	   {
		   if (is_callable('parent::isArray')) return parent::isArray($info);

		   if(is_array($info))
		   {
			   if(isset($info['type']) && $info['type']=='array') return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if type is null
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   public function isNull($info)
	   {
		   if (is_callable('parent::isNull')) return parent::isNull($info);

		   if(is_array($info))
		   {
			   if(isset($info['type']) && $info['type']=='null') return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if type is integer
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   public function isInt($info)
	   {
		   if (is_callable('parent::isInt')) return parent::isInt($info);

		   if(is_array($info))
		   {
			   if(isset($info['type']) && ($info['type']=='int' || $info['type']=='integer' )) return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if type is float
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   public function isFloat($info)
	   {
		   if (is_callable('parent::isFloat')) return parent::isFloat($info);

		   if(is_array($info))
		   {
			   if (isset($info['type']) && (preg_match('/^(double|real)/i', $info['type']))) return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if type is text
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   public function isText($info)
	   {
		   if (is_callable('parent::isText')) return parent::isText($info);

		   if(is_array($info))
		   {
			   if(isset($info['type']) && $info['type']=='text') return true;
			   else return false;
		   }
		   else return false;
	   }

	   /**
		* Function test if is indexed
		*
		* @param   array   $info   content informations of field
		* @return                  bool
		*/
	   public function isIndex($info)
	   {
		   if (is_callable('parent::isIndex')) return parent::isIndex($info);

		   if(is_array($info))
		   {
			   if(isset($info['index']) && $info['index']==true) return true;
			   else return false;
		   }
		   else return false;
	   }

		/**
		 * Function to concat keys of fields
		 *
		 * @return string
		 */
		public function get_field_list()
		{
			if (method_exists($this, 'getFieldList')) return parent::getFieldList();

			$keys = array_keys($this->fields);
			return implode(',', $keys);
		}

		/**
		 * Function to load data from a SQL pointer into properties of current object $this
		 *
		 * @param   stdClass    $obj    Contain data of object from database
		 */
		protected function set_vars_by_db(&$obj)
		{
			if (method_exists($this, 'setVarsFromFetchObj')) return parent::setVarsFromFetchObj($obj);

			foreach ($this->fields as $field => $info)
			{
				if($this->isDate($info))
				{
					if(empty($obj->{$field}) || $obj->{$field} === '0000-00-00 00:00:00' || $obj->{$field} === '1000-01-01 00:00:00') $this->{$field} = 0;
					else $this->{$field} = strtotime($obj->{$field});
				}
				elseif($this->isArray($info))
				{
					$this->{$field} = @unserialize($obj->{$field});
					// Hack for data not in UTF8
					if($this->{$field } === FALSE) @unserialize(utf8_decode($obj->{$field}));
				}
				elseif($this->isInt($info))
				{
					$this->{$field} = (int) $obj->{$field};
				}
				elseif($this->isFloat($info))
				{
					$this->{$field} = (double) $obj->{$field};
				}
				elseif($this->isNull($info))
				{
					$val = $obj->{$field};
					// zero is not null
					$this->{$field} = (is_null($val) || (empty($val) && $val!==0 && $val!=='0') ? null : $val);
				}
				else
				{
					$this->{$field} = $obj->{$field};
				}

			}
		}

		/**
		 * Function to prepare the values to insert.
		 * Note $this->${field} are set by the page that make the createCommon or the updateCommon.
		 *
		 * @return array
		 */
		protected function set_save_query()
		{
			if (method_exists($this, 'setSaveQuery')) return parent::setSaveQuery();

			global $conf;
			$queryarray=array();
			foreach ($this->fields as $field=>$info)	// Loop on definition of fields
			{
				// Depending on field type ('datetime', ...)
				if($this->isDate($info))
				{
					if(empty($this->{$field}))
					{
						$queryarray[$field] = NULL;
					}
					else
					{
						$queryarray[$field] = $this->db->idate($this->{$field});
					}
				}
				else if($this->isArray($info))
				{
					$queryarray[$field] = serialize($this->{$field});
				}
				else if($this->isInt($info))
				{
					if ($field == 'entity' && is_null($this->{$field})) $queryarray[$field]=$conf->entity;
					else
					{
						$queryarray[$field] = (int) price2num($this->{$field});
						if (empty($queryarray[$field])) $queryarray[$field]=0;		// May be reset to null later if property 'notnull' is -1 for this field.
					}
				}
				else if($this->isFloat($info))
				{
					$queryarray[$field] = (double) price2num($this->{$field});
					if (empty($queryarray[$field])) $queryarray[$field]=0;
				}
				else
				{
					$queryarray[$field] = $this->{$field};
				}
				if ($info['type'] == 'timestamp' && empty($queryarray[$field])) unset($queryarray[$field]);
				if (! empty($info['notnull']) && $info['notnull'] == -1 && empty($queryarray[$field])) $queryarray[$field] = null;
			}
			return $queryarray;
		}
	}
}


class SeedObject extends SeedObjectDolibarr
{

	public $withChild = true;

	/**
	 *  @var array $fields Fields to synchronize with Database
	 */
	public $fields=array();

	public $fk_element='';

	protected $childtables=array();

	/**
	 * @var bool Activer l'affichage d'informations supplémentaires
	 */
	public $debug = false;

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
		$this->date_creation = 0;
		$this->tms = 0;

		if(!isset($this->fields['rowid'])) $this->fields['rowid']=array('type'=>'integer','index'=>true);
		if(!isset($this->fields['date_creation'])) $this->fields['date_creation']=array('type'=>'date');
		if(!isset($this->fields['tms'])) $this->fields['tms']=array('type'=>'date');

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
	 * Function to init fields by db
	 *
	 * @return bool
	 */
	function init_vars_by_db() {
		$res = $this->db->query("SHOW COLUMNS FROM ".MAIN_DB_PREFIX.$this->table_element);
		while($obj = $this->db->fetch_object($res)) {

			if(strpos($obj->Type,'int(')!==false) $type=array('type'=>'integer');
			else if(strpos($obj->Type,'date')!==false || strpos($obj->Type,'timestamp')!==false) $type=array('type'=>'date');
			else $type=array('type'=>'string');

			$this->fields[$obj->Field] = $type;
		}


		return $this->init();
	}


    /**
     * Test type of field
     *
     * @param   string  $field  name of field
     * @param   string  $type   type of field to test
     * @return  bool
     */
    private function checkFieldType($field, $type)
    {
		if (isset($this->fields[$field]) && method_exists($this, 'is' . ucfirst($type)))
		{
			return $this->{'is' . ucfirst($type)}($this->fields[$field]);
		}
		else
        {
            return false;
        }
	}

    /**
     * @param 	User 	$user 		object
	 * @param	bool	$notrigger	false=launch triggers after, true=disable triggers
     * @return  int
     */
    public function cloneObject($user, $notrigger = false)
    {
        $this->clear();

        return $this->create($user, $notrigger);
    }

    /**
     * @return bool
     */
    protected function clear()
    {
        $this->is_clone = true;
        $this->id = 0;
        if(isset($this->fields['date_creation'])) $this->date_creation=time();
        if(isset($this->fields['tms'])) $this->tms=time();

        if (method_exists($this, 'clearUniqueFields')) $this->clearUniqueFields();

        if (!empty($this->childtables) && !empty($this->fk_element))
        {
            foreach ($this->childtables as $childTable => $className)
            {
                if (is_int($className)) $className = $childTable;

                foreach ($this->{'T'.$className} as $i => &$object)
                {
                    $object->{$this->fk_element} = 0;
                    $object->clear();
                }
            }
        }

        return true;
    }

    /**
     *	Get object and children from database
     *
     *	@param      int			$id       		Id of object to load
     * 	@param		bool		$loadChild		used to load children from database
     *  @param      string      $ref            Ref
     *	@return     int         				>0 if OK, <0 if KO, 0 if not found
     */
	public function fetch($id, $loadChild = true, $ref = null)
    {
    	$res = $this->fetchCommon($id, $ref);
    	if($res>0) {
    		if ($loadChild) $this->fetchChild();
    	}

        if(!empty($this->isextrafieldmanaged))
        {
            $this->fetch_optionals();
        }

    	return $res;
	}

    /**
     * @param int   $limit     Limit element returned
     * @param bool  $loadChild used to load children from database
     * @param array $TFilter
     * @return array
     */
    public function fetchAll($limit = 0, $loadChild = true, $TFilter = []) {
        return $this->fetchByArray($limit, $TFilter, $loadChild, false);
    }

    /**
	 *	Get object and children from database on custom field
	 *
	 *	@param      string		$key       		key of object to load
	 *	@param      string		$field       	field of object used to load
	 * 	@param		bool		$loadChild		used to load children from database
	 *	@return     int         				>0 if OK, <0 if KO, 0 if not found
	 */
    public function fetchBy($key, $field, $loadChild = true) {
	    if(empty($this->fields[$field])) return false;

        return $this->fetchByArray(1, array($field => $key), $loadChild);
	}

    /**
     * @param   int     $limit
     * @param   array   $TFilter
     * @param   bool    $loadChild
     * @param   bool    $justFetchIfOnlyOneResult   This parameter affect the function return type only if the query return one result;
     *                                              true : it will fetch $this and return an integer;
     *                                              false : it will return an array with objects inside
     * @return  int|array                           >0 if OK, <0 if KO, 0 if not found or array with all objects inside
     */
    public function fetchByArray($limit = 0, $TFilter = array(), $loadChild = true, $justFetchIfOnlyOneResult = true) {
        $sql = 'SELECT rowid';
        $sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element;
        $sql.= ' WHERE 1 = 1';

        foreach($TFilter as $key => $field) {
            $sql.= ' AND '.$this->db->escape($key).' = '.$this->quote($field, $this->fields[$key]);
        }
        if(! empty($limit)) $sql.= ' LIMIT '.$this->db->escape($limit);

        $resql = $this->db->query($sql);
        if(! $resql) {
            $this->error = $this->db->lasterror();
		    $this->errors[] = $this->error;
            return -1;
        }

        $nbRow = $this->db->num_rows($resql);

        $TRes = array();
        while($obj = $this->db->fetch_object($resql)) {
            if($justFetchIfOnlyOneResult) {
                return $this->fetch($obj->rowid, $loadChild);
            }

            $o = new static($this->db);
            $o->fetch($obj->rowid, $loadChild);
            $TRes[] = $o;
        }

        if($justFetchIfOnlyOneResult) return 0;
        return $TRes;
    }

    /**
     * Function to instantiate a new child
     *
     * @param   string  $className      Class name of child
     * @param   int     $id             If id is given, we try to return his key if exist or load if we try_to_load
     * @param   string  $key            Attribute name of the object id
     * @param   bool    $try_to_load    Force the fetch if an id is given
     * @return                          int
     */
    public function addChild($className, $id=0, $key='id', $try_to_load = false)
    {
		if(!empty($id))
		{
			foreach($this->{'T'.$className} as $k=>&$object)
			{
				if($object->{$key} == $id) return $k;
			}
		}

		$k = count($this->{'T'.$className});

		$this->{'T'.$className}[$k] = new $className($this->db);
		if($id>0 && $key==='id' && $try_to_load)
		{
			$this->{'T'.$className}[$k]->fetch($id);
		}

		return $k;
	}


    /**
     * Function to set a child as to delete
     *
     * @param   User    $user           User object
     * @param   string  $className      Class name of child
     * @param   int     $id             Id of child to set as to delete
     * @param   string  $key            Attribute name of the object id
     * @return                          bool
     */
	public function removeChild(&$user, $className, $id, $key='id')
    {
		foreach ($this->{'T'.$className} as $k=>&$object)
		{
			if ($object->{$key} == $id)
			{
				$object->delete($user);
				unset($this->{'T'.$className}[$k]);
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
			foreach($this->childtables as $childTable => $className)
			{
				if (is_int($className)) {
					$className = $childTable;
					$o=new $className($this->db);
					$childTable = $o->table_element;
				}



                $this->{'T'.$className}=array();

                $sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.$childTable.' WHERE '.$this->fk_element.' = '.$this->id;
                $res = $this->db->query($sql);

                if($res)
                {
                    while($obj = $this->db->fetch_object($res))
                    {
                        $o=new $className($this->db);
                        $o->fetch($obj->rowid);

                        $this->{'T'.$className}[] = $o;
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
     * @param   User    $user   	user object
	 * @param	bool	$notrigger	false=launch triggers after, true=disable triggers
     */
	public function saveChild(User &$user, $notrigger = false)
    {
		if($this->withChild && !empty($this->childtables) && !empty($this->fk_element))
		{
			foreach($this->childtables as $childTable=>$className)
			{
				if (is_int($className)) $className = ucfirst($childTable);

				if(!empty($this->{'T'.$className}))
				{
					foreach($this->{'T'.$className} as $i => &$object)
					{
						$object->{$this->fk_element} = $this->id;

						$object->update($user, $notrigger);
						if($this->unsetChildDeleted && isset($object->to_delete) && $object->to_delete==true) unset($this->{'T'.$className}[$i]);
					}
				}
			}
		}
	}


    /**
     * Function to update object or create or delete if needed
     *
     * @param   User    $user   	user object
	 * @param	bool	$notrigger	false=launch triggers after, true=disable triggers
     * @return  int                 < 0 if ko, > 0 if ok
     */
    public function update(User &$user, $notrigger = false)
    {
		if (empty($this->id)) return $this->create($user, $notrigger); // To test, with that, no need to test on high level object, the core decide it, update just needed
        elseif (isset($this->to_delete) && $this->to_delete==true) return $this->delete($user, $notrigger);

        $error = 0;
        $this->db->begin();

        $res = $this->updateCommon($user, $notrigger);
        if ($res)
        {
           $this->saveChild($user, $notrigger);
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
     * @param   User    $user		user object
	 * @param	bool	$notrigger	false=launch triggers after, true=disable triggers
     * @return  int                 < 0 if ko, > 0 if ok
     */
    public function create(User &$user, $notrigger = false)
    {
		if($this->id > 0) return $this->update($user, $notrigger);

        $error = 0;
        $this->db->begin();

        $res = $this->createCommon($user, $notrigger);
		if($res)
		{

			$this->saveChild($user, $notrigger);
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
     * @param   User    $user   	user object
	 * @param	bool	$notrigger	false=launch triggers after, true=disable triggers
     * @return  int                 < 0 if ko, > 0 if ok
     */
	public function delete(User &$user, $notrigger = false)
    {
		if ($this->id <= 0) return 0;

        $error = 0;
        $this->db->begin();

        if ($this->deleteCommon($user, $notrigger)>0)
        {
            if($this->withChild && !empty($this->childtables))
            {
                foreach($this->childtables as  $childTable=>$className )
                {
					if (is_int($className)) $className = ucfirst($childTable);

                    if (!empty($this->{'T'.$className}))
                    {
                        foreach($this->{'T'.$className} as &$object)
                        {
                            $object->parent = $this;
                            $object->delete($user, $notrigger);
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
	  		$this->{$field} = null;
	  	}
		else
        {
			require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
			$this->{$field} = dol_stringtotime($date, 0);
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
            if($this->fields[$key]['type'] == 'datetime'){
                if(!empty($value)) $value .= ' '. $Tab[$key.'hour'] .':'.$Tab[$key.'min'].':'.$Tab[$key.'sec'];
                $this->setDate($key, $value);
            }
			else if($this->checkFieldType($key, 'date'))
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

        if (! $error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);

            if (! $notrigger) {
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
	 * @param int    $id   		Id object
	 * @param string $ref  		Ref
	 * @param string $morewhere	Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchCommon($id, $ref = null, $morewhere='')
	{
		// method_exists() with key word 'parent' doesn't work
		if (is_callable('parent::fetchCommon')) return parent::fetchCommon($id, $ref, $morewhere);


		if (empty($id) && empty($ref)) return false;

		$sql = 'SELECT '.$this->get_field_list().', date_creation, tms';
		$sql.= ' FROM '.MAIN_DB_PREFIX.$this->table_element;

		if(!empty($id)) $sql.= ' WHERE rowid = '.$id;
		else $sql.= " WHERE ref = ".$this->quote($ref, $this->fields['ref']);
		if ($morewhere) $sql.=$morewhere;

		$res = $this->db->query($sql);
		if ($res)
		{
			$num = $this->db->num_rows($res);

			if(empty($num))
			{
				return 0;
			}

    		if ($obj = $this->db->fetch_object($res))
    		{
                $this->id = $id;
                $this->set_vars_by_db($obj);

                $this->date_creation = $this->db->idate($obj->date_creation);
                $this->tms = $this->db->idate($obj->tms);

                return $this->id;
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

		$tmp = array();
		foreach ($fieldvalues as $k => $v) {
			if (is_array($key)){ // TODO démêler ce sac de noeuds incompréhensible. D'où sort $key ? Qu'est-ce qu'elle représente ? etc. - MdLL, 07/06/2019
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
	 * @param User $user                User that deletes
	 * @param bool $notrigger           false=launch triggers after, true=disable triggers
	 * @param int  $forcechilddeletion  0 = children are not deleted, otherwise they are
	 * @return int                      <0 if KO, >0 if OK
	 */
	public function deleteCommon(User $user, $notrigger = false, $forcechilddeletion = 0)
	{
		// method_exists() with key word 'parent' doesn't work
		if (is_callable('parent::deleteCommon')) return parent::deleteCommon($user, $notrigger, $forcechilddeletion);


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
		$resql = $this->db->query('SHOW FIELDS FROM ' . MAIN_DB_PREFIX . $this->table_element);

		if($resql===false ) {
			var_dump($this->db);exit;

		}

		$Tab = array();
		while ($obj = $this->db->fetch_object($resql))
		{
			$Tab[] = $obj->Field;
		}

		$TChamps = array_merge(array('date_creation' => array('type' => 'date'), 'tms' => array('type' => 'date'),'rowid'=>array('type'=>'integer','index'=>true)), $this->fields);

		foreach ($TChamps as $champs => $info)
		{
			if (!in_array($champs, $Tab))
			{
				if ($this->isInt($info))
				{
                    $sql = 'ALTER TABLE '.MAIN_DB_PREFIX.$this->table_element.' ADD '.$champs.' int(11) '.(! empty($info['notnull']) ? ' NOT NULL' : '').' DEFAULT \''.(! empty($info['default']) && is_int($info['default']) ? $info['default'] : '0')."'";
                    if(array_key_exists('foreignkey', $info) && ! empty($info['foreignkey'])) {
                        $fk = explode('.', $info['foreignkey']);    // fk[0] => tablename, fk[1] => field
                        $sql.= ', ADD CONSTRAINT FOREIGN KEY ('.$champs.') REFERENCES '.$fk[0].'('.$fk[1].')';
                    }
					$this->db->query($sql);
				}
				else if ($this->isDate($info))
				{
					$this->db->query('ALTER TABLE ' . MAIN_DB_PREFIX . $this->table_element . ' ADD ' . $champs . ' datetime NULL');
				}
				else if ($this->isFloat($info))
				{
					$this->db->query('ALTER TABLE ' . MAIN_DB_PREFIX . $this->table_element . ' ADD ' . $champs . ' DOUBLE '.(!empty($info['notnull']) ? ' NOT NULL' : '' ).' DEFAULT \'' . (!empty($info['default']) ? $info['default'] : '0') . '\'');
				}
				else if ($this->isArray($info) || $this->isText($info))
				{
					$this->db->query('ALTER TABLE ' . MAIN_DB_PREFIX . $this->table_element . ' ADD ' . $champs . ' LONGTEXT');
				}
				else
				{
					$this->db->query('ALTER TABLE ' . MAIN_DB_PREFIX . $this->table_element . ' ADD ' . $champs . ' VARCHAR(' . (is_array($info) && !empty($info['length']) ? $info['length'] : 255 ) . ')');
				}

				if ($this->isIndex($info))
				{
					$this->db->query('ALTER TABLE ' . MAIN_DB_PREFIX . $this->table_element . ' ADD INDEX ' . $champs . '(' . $champs . ')');
				}
			}
		}
	}

	function init_db_by_vars()
	{
		global $conf,$dolibarr_main_db_name;

		if(empty($this->table_element))exit('NoDataTableDefined');

		$resql = $this->db->query("SHOW TABLES FROM `" . $dolibarr_main_db_name . "` LIKE '" . MAIN_DB_PREFIX . $this->table_element . "'");
		if($resql === false) {
			var_dump($this->db);exit;

		}

		if ($resql && $this->db->num_rows($resql) == 0)
		{
			/*
			 * La table n'existe pas, on la crée
			 */
			$charset = $conf->db->character_set;

			$sql = "CREATE TABLE " . MAIN_DB_PREFIX . $this->table_element . " (
 				rowid integer AUTO_INCREMENT PRIMARY KEY
 				,date_creation datetime DEFAULT NULL
 				,tms timestamp
 				,KEY date_creation (date_creation)
 				,KEY tms (tms)
 				) ENGINE=InnoDB DEFAULT CHARSET=" . $charset;

            if (!empty($conf->db->dolibarr_main_db_collation)) $sql .= ' COLLATE='.$conf->db->dolibarr_main_db_collation;


            $res = $this->db->query($sql);
			if($res===false) {
				var_dump($this->db);exit;


			}


		}
		else
		{
			// Conversion de l'ancienne table sans auto_increment
			$resql = $this->db->query('DESC '.MAIN_DB_PREFIX . $this->table_element);
			if ($resql)
			{
				while ($desc = $this->db->fetch_object($resql))
				{
					if ($desc->Field == 'rowid')
					{
						if (strpos($desc->Extra, 'auto_increment') === false)
						{
							$this->db->query('ALTER TABLE '.MAIN_DB_PREFIX . $this->table_element.' MODIFY COLUMN rowid INT auto_increment');
						}

						break;
					}
				}
			}
		}

		$this->addFieldsInDb();

		if(!empty($this->isextrafieldmanaged))
        {
            $resql = $this->db->query("SHOW TABLES FROM " . $dolibarr_main_db_name . " LIKE '" . MAIN_DB_PREFIX . $this->table_element . "_extrafields'");
            if($resql === false) {
                var_dump($this->db);exit;
            }

            if ($resql && $this->db->num_rows($resql) == 0)
            {
                /*
                 * La table n'existe pas, on la crée
                 */
                $charset = $conf->db->character_set;

                $sql = "CREATE TABLE " . MAIN_DB_PREFIX . $this->table_element . "_extrafields (
 				rowid integer AUTO_INCREMENT PRIMARY KEY
 				,tms timestamp
 				,fk_object integer
 				,import_key varchar(14)
 				,KEY tms (tms)
 				, UNIQUE fk_object (fk_object)
 				) ENGINE=InnoDB DEFAULT CHARSET=" . $charset;

                if (!empty($conf->db->dolibarr_main_db_collation)) $sql .= ' COLLATE='.$conf->db->dolibarr_main_db_collation;

                $res = $this->db->query($sql);
                if($res===false) {
                    var_dump($this->db);exit;
                }

            }
        }
	}

	function get_date($nom_champ,$format_date='day') {
		if(empty($this->{$nom_champ})) return '';
		elseif($this->{$nom_champ}<=strtotime('1000-01-01 00:00:00')) return '';
		else {
			return dol_print_date($this->{$nom_champ},$format_date);
		}

	}

	function set_date($nom_champ,$date){

		if(empty($date)) {
			$this->{$nom_champ} = 0;//strtotime('0000-00-00 00:00:00');
		}
		else if(strpos($date,'/')===false){
			$this->{$nom_champ} = strtotime($date);
		}
		else {
			list($d,$m,$y) = explode('/',$date);
			$this->{$nom_champ} = mktime(0,0,0,$m,$d,$y);
		}
		return $this->{$nom_champ};
	}


	public function replaceCommon(User $user, $notrigger = false)
	{
		global $langs;

		$error = 0;

		$now=dol_now();

		$fieldvalues = $this->set_save_query();
		if (array_key_exists('date_creation', $fieldvalues) && empty($fieldvalues['date_creation'])) $fieldvalues['date_creation']=$this->db->idate($now);
		if (array_key_exists('fk_user_creat', $fieldvalues) && ! ($fieldvalues['fk_user_creat'] > 0)) $fieldvalues['fk_user_creat']=$user->id;
		//unset($fieldvalues['rowid']);	// The field 'rowid' is reserved field name for autoincrement field so we don't need it into insert.

		$keys=array();
		$values = array();
		foreach ($fieldvalues as $k => $v) {
			$keys[$k] = $k;
			$value = $this->fields[$k];
			$values[$k] = $this->quote($v, $value);
		}

		// Clean and check mandatory
		foreach($keys as $key)
		{
			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && $values[$key] == '-1') $values[$key]='';
			if (! empty($this->fields[$key]['foreignkey']) && $values[$key] == '-1') $values[$key]='';

			//var_dump($key.'-'.$values[$key].'-'.($this->fields[$key]['notnull'] == 1));
			if ($this->fields[$key]['notnull'] == 1 && empty($values[$key]))
			{
				$error++;
				$this->errors[]=$langs->trans("ErrorFieldRequired", $this->fields[$key]['label']);
			}

			// If field is an implicit foreign key field
			if (preg_match('/^integer:/i', $this->fields[$key]['type']) && empty($values[$key])) $values[$key]='null';
			if (! empty($this->fields[$key]['foreignkey']) && empty($values[$key])) $values[$key]='null';
		}

		if ($error) return -1;

		$this->db->begin();

		if (! $error)
		{
			$sql = 'REPLACE INTO '.MAIN_DB_PREFIX.$this->table_element;
			$sql.= ' ('.implode( ", ", $keys ).')';
			$sql.= ' VALUES ('.implode( ", ", $values ).')';

			$res = $this->db->query($sql);
			if ($res===false) {
				$error++;
				$this->errors[] = $this->db->lasterror();
			}
		}

		if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . $this->table_element);
		}

		if (! $error)
		{
			$result=$this->insertExtraFields();
			if ($result < 0) $error++;
		}

		if (! $error && ! $notrigger)
		{
			// Call triggers
			$result=$this->call_trigger(strtoupper(get_class($this)).'_CREATE',$user);
			if ($result < 0) { $error++; }
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
	 * Méthode utilisée par var_dump() pour sélectionner les données à afficher, à partir de PHP 5.6
	 * Ici, pour plus de clarté, on filtre les champs inhérents à la classe et non aux instances - et qui devraient en fait être static... -
	 * et le $db qui prend toute la place en plus de créer des warnings...
	 *
	 * @return array Tableau clé => valeur des données à afficher
	 */
	public function __debugInfo()
	{
		$TKeyVal = get_object_vars($this);

		if(empty($this->debug))
		{
			$TKeysToHide = array(
				'element'
				, 'fk_element'
				, 'table_element'
				, 'table_element_line'
				, 'table_ref_field'
				, 'fields'
				, 'db'
				, 'childtables'
				, 'picto'
				, 'isextrafieldmanaged'
				, 'isentitymanaged'
			);

			$TKeyVal = array_filter(
				$TKeyVal
				, function ($key) use ($TKeysToHide)
				{
					return ! in_array($key, $TKeysToHide);
				}
				, ARRAY_FILTER_USE_KEY
			);
		}

		return $TKeyVal;
	}
}
