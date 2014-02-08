<?php
class Model{
	protected $_vars = array();
	protected $_db = null;
	protected $_whereStr = null;
	
	
	#memcache settings
	protected $use_memcache;
	protected $memcache_seconds;
	protected $memcache_custom_key;
	
	public function __construct($table_name, $use_memcache=USE_MEMCACHE, $memcache_seconds=MEMCACHE_SECONDS, $memcache_custom_key=NULL){
		$this->use_memcache = $use_memcache;
		$this->memcache_seconds = $memcache_seconds;
		$this->memcache_custom_key = $memcache_custom_key;
		$this->init($table_name);
	}
	
	public static function Factory($table_name, $use_memcache=USE_MEMCACHE, $memcache_seconds=MEMCACHE_SECONDS, $memcache_custom_key=NULL){
		return new self($table_name, $use_memcache, $memcache_seconds, $memcache_custom_key);	
	}

	private function init($table_name){
		$this->_db = MDB::Cursor($table_name);
	}
	
	public function __set($k,$v){
		$this->_vars[$k] = $v;
	}
	
	public function __get($k){
		return isSet($this->_vars[$k])?$this->_vars[$k]:null;
	}
	
	public function order($string){
		$this->_db->order($string);
		return $this;
	}
	
	public function group($string){
		$this->_db->group($string);
		return $this;
	}
	
	public function limit($string){
		$this->_db->limit($string);
		return $this;
	}
	
	/**
	 * @return array
	 * @desc returns array with the first element that match the query runned or false if no resultset 
	 */
	public function get(){
		if(!is_null($this->_whereStr))
			$this->_db->where($this->_whereStr);	
		
		return $this->_db->fetchOne($this->use_memcache, $this->memcache_seconds, $this->memcache_custom_key);
	}
	
	public function getSQL(){
		return $this->_db->getSQL();
	}
	
	public function all(){
		if(!is_null($this->_whereStr))
			$this->_db->where($this->_whereStr);
		$data = $this->_db->fetchAll($this->use_memcache, $this->memcache_seconds, $this->memcache_custom_key);
		return ($data) ? $data : false;
	}

	public function delete(){
		if(!is_null($this->_whereStr))
			$this->_db->where($this->_whereStr);
			
		return $this->_db->delete();
	}
	
	public function where($data){
		if(is_array($data)){
			foreach($data as $d=>$v){
				if(is_numeric($v))
					$whereArray[] = "{$d}={$v}" ;
				else if(is_string($v))
					$whereArray[] = "{$d}='{$v}'" ;
			}
			$this->_whereStr = implode(" AND ", $whereArray);
		} else if(is_string($data)){
			$this->_whereStr = $data;
		}
		return $this;
	} 
	
	public function replace(){
		$data = (Array) $this->_vars;
		if(sizeof($data) == 0)
			throw new Exception("ERROR ON REPLACE: No field set");
		return $this->_db->replace($data);
	}
	
	public function insert(){
		$data = (Array) $this->_vars;
		
		if(sizeof($data) == 0)
			throw new Exception("ERROR ON INSERT: No field set");
		return $this->_db->insert($data);
	}
	
	public function update($force=false){
		$data = (Array) $this->_vars;
		if(sizeof($data) == 0)
			throw new Exception("ERROR ON UPDATE: No field set");
		$r = $this->_db->where($this->_whereStr)->update($data);
		if($force == false){
			if(! $r){
				return $this->insert();
			}
		}
		return $r;
	}
	
	public function having($having){
		$this->_db->having($having);
		return $this;
	}
	
	public function innerJoin($table, $on){
		$this->_db->join($table, $on, 'INNER');
		return $this;
	}
	
	public function leftJoin($table, $on){
		$this->_db->join($table, $on, 'LEFT');
		return $this;
	}
	
	public function rightJoin($table, $on){
		$this->_db->join($table, $on, 'RIGHT');
		return $this;
	}
	
	public function error(){
		return $this->_db->error();
	}
	
	public function fields(){
		$fields = func_get_args();
		$this->_db->fields(implode(", ", $fields));
		return $this;
	}
	
	public static function callProcedure($procname, $args=null){
		$connection = DBConnectionManager::getProperConnection("CALL {$procname}");
		$db = MDB::Cursor($connection, $procname);
		$data = $db->call($procname, $args);
		return $data;
	}
	
	public static function ExecuteQuery($sql, $use_memcache=USE_MEMCACHE, $memcache_seconds=MEMCACHE_SECONDS, $memcache_custom_key=NULL){
		$db = MDB::cursor('');
		$db->SetConnection(DBConnectionManager::getProperConnection($sql));
		return $db->query($sql, $use_memcache, $memcache_seconds, $memcache_custom_key);
	}
}