<?php 

class Mysql{
	private static	$_conn;
	private static	$connected_host,
					$connected_user,
					$connected_password,
					$connected_db;
	
	private $_transactionStarted = false;
	
	private $_fields= 	'*',
			$_from	=	'',
			$_where	=	'',
			$_join	=	'',
			$_group	=	'',
			$_order	=	'',
			$_offset =	'',
			$_having = 	'',
			$switch_to_next_result = false;
	
			
	public function __construct($conn=NULL){
		
	}
	
	public function SetConnection($conn){
		$this->conn = $conn;
	}
	
	public function fields(){
		$args = func_get_args();
		$this->_fields = implode(', ',$args);
		return $this;
	}

	public function clear(){
		$this->_fields = '*';
		$this->_where = '';
		$this->_join = '';
		$this->_group = '';
		$this->_order = '';
		$this->_offset = '';
		$this->_having = '';
	}

	public function from($table){
		$this->_fields = '*';
		$this->_where = '';
		$this->_join = '';
		$this->_group = '';
		$this->_order = '';
		$this->_offset = '';
		$this->_having = '';
		
		$this->_from = $table;
		return $this;
	}
	
	public function where($cmd){
		if(empty($cmd))
			return $this;	
			
		if(!is_string($cmd))
			 throw new Exception('Not valid string sent to method Mysql::where' .  "\n" . print_r(debug_backtrace(), true));
			 
		$this->_where = sprintf("WHERE %s",$cmd);
		return $this;
	}
	
	public function having($cmd){
		if(!is_string($cmd))
			 throw new Exception('Not valid string sent to method having');
		
		if(empty($cmd))
			return $this;	 
			 
		$this->_having = sprintf("HAVING %s",$cmd);
		return $this;
	}
	
	public function join($table,$params,$type='INNER'){
		$this->_join .= sprintf("%s JOIN %s ON(%s)",$type,$table,$params);
		return $this;
	}
	
	public function group(){
		$args = func_get_args();
		$cmd = implode(', ', $args);
		$this->_group = sprintf("GROUP BY %s",$cmd);
		return $this;
	}
	
	public function order(){
		$args = func_get_args();
		$cmd = implode(', ', $args);
		$this->_order = sprintf("ORDER BY %s",$cmd);
		return $this;
	}
	
	public function limit($min,$max=null){
		
		$cmd = (string)$min.((!is_null($max))?(', '.$max):(''));
		$this->_offset = sprintf("LIMIT %s",$cmd);
		return $this;
	}
	
	public function fetchOne($use_memcache, $memcache_seconds, $memcache_key){
		$sql = trim("SELECT {$this->_fields} FROM {$this->_from} {$this->_join} {$this->_where} {$this->_group} {$this->_having}  {$this->_order} LIMIT 1");
		$res = $this->query($sql, $use_memcache, $memcache_seconds, $memcache_key);
		
		
		if(is_array($res) and array_key_exists("0", $res)){
			return $res[0];
		} else {
			$o = $this->fetchObject($res);
			return (isset($o[0])) ? $o[0] : false;		
		}
	}
	
	public function getSQL(){
		return trim("SELECT {$this->_fields} FROM {$this->_from} {$this->_join} {$this->_where} {$this->_group} {$this->_having} {$this->_order} {$this->_offset}");
	}
	
	public function fetchAll($use_memcache, $memcache_seconds, $memcache_key){
		$sql = trim("SELECT {$this->_fields} FROM {$this->_from} {$this->_join} {$this->_where} {$this->_group} {$this->_having} {$this->_order} {$this->_offset}");
		$res = $this->query($sql, $use_memcache, $memcache_seconds, $memcache_key);
		
		if(is_array($res)){
			$data = $res;
		} else {
			if($res)
				$data = $this->fetchObject($res);
			else
				$data = array();
		}
		
		return $data;
	}
	
	public function insert($data){
		$f = $s = $ks = null;
		foreach($data as $k => $v)
		{
			$val = (is_numeric($v)?$v:"'".$this->escapeString($v)."'");
			$f .= $s.$val;
			$ks 	.= $s.$k;
			$s 	= ', ';		
		}	
		$sql = trim('INSERT INTO '.$this->_from.'('.$ks.') VALUES('.($f).')');
		$status = $this->query($sql);
		
		if($status && ($id = $this->insertID())){
			return $id;
		} else {
			return (Boolean) $status;
		}
	}
	
	public function replace($data){
		$f = $s = $ks = null;
		foreach($data as $k => $v){
			$val = (is_numeric($v)?$v:"'".$this->escapeString($v)."'");
			$f .= $s.$val;
			$ks 	.= $s.$k;
			$s 	= ', ';		
		}	
		$sql = trim('REPLACE INTO '.$this->_from.'('.$ks.') VALUES('.($f).')');
		return $this->query($sql); 
	}
	
	public function update($data){
		$f= $s = null;
		foreach($data as $k=>$v){
			if(is_null($v))
				$f 	.= $s."$k=NULL";
			else 
				$f 	.= $s."$k='".$this->escapeString($v)."'";
			
			$s = ',';
		}		
		$sql = trim('UPDATE '.$this->_from.' SET '.$f.' '.$this->_where);
		return $this->query($sql);
	}
	
	public function delete(){
		return $this->query('DELETE FROM '.$this->_from.'  '.$this->_where);
	}
	
	public function query($sql, $use_memcache, $seconds, $memcache_custom_key){
		$connection = DBConnectionManager::getProperConnection($sql);
		$this->SetConnection($connection);
		
		#Hack to cache only the SELECTS
		$isSelectQuery = preg_match('/^SELECT/i', $sql);
		if($use_memcache && !$isSelectQuery or USE_MEMCACHE==false)
			$use_memcache = false;
		
		if($use_memcache){
			$memcache_key = is_null($memcache_custom_key) ? md5($sql) : $memcache_custom_key;
			if($data = PhxMemcache::get($memcache_key)){
					return $data;
				} else {
					$data = mysqli_query($this->conn, $sql);
					$this->log($sql, $data);
					$data = $this->fetchObject($data);
					PhxMemcache::set($memcache_key, $data, $seconds);
				}
		} else {
			$data = mysqli_query($this->conn, $sql);
			if($isSelectQuery)
				$data = $this->fetchObject($data);
			
			$this->log($sql, $data);
			if(preg_match('/^CALL/i', $sql))
				$this->switch_to_next_result = true;
		}
		
		if(! $data && mysqli_error($this->conn) != ''){
			$logger = Logger::getInstance('txt');
			$logger->file	= 'query_error_.'.date('ymd').'.txt';
			$logger->message(mysqli_error($this->conn) ."\n\n". print_r($sql, true));
		}
		
		return $data;
	}
	
	
	public function fetchObject($res){
		$oarray = array();
		
		while($o = mysqli_fetch_object($res)){
			$auxObject = new stdClass;
			foreach($o as $property => $value)
				$auxObject->{$property} = stripslashes(str_replace(array('\\r', '\\n'), array("\r", "\n"), $value));
							
			$oarray[] = $auxObject;
		}
		
		if($this->switch_to_next_result){
			$this->conn->next_result();
			$this->switch_to_next_result = false;
		}
		
		$this->freeResult($res);
		return $oarray;
		
	}
	
	protected function fetchArray($res){
		$oarray = array();
		while($o = mysqli_fetch_array($res,$type))
			$oarray[] = $o; 
		$this->freeResult($res);
		return $oarray;
		
	}
	
	public function numRows($res=null){
		if(is_null($res)){
			$sql = trim("SELECT {$this->_fields} FROM {$this->_from} {$this->_join} {$this->_where} {$this->_group} {$this->_order} {$this->_offset}");
			$res = $this->query($sql);
			return $res; 
		}
		return (int) mysqli_num_rows($res);
	}
	
	public function insertID(){
		return (int) mysqli_insert_id($this->conn);
	}

	public function escapeString($string){
		#Para poder executar o realEscapeString, precisamos ter uma conexão aberta.
		if(! $this->conn)
			$this->SetConnection(DBConnectionManager::getMasterConnection());
		
			
		return mysqli_real_escape_string($this->conn, $string);
	}
	
	public function freeResult($res=null){
		$this->_join	= null;	
		$this->_where	= null;
		$this->_order_by= null;
		$this->_group_by= null;
		$this->_limit	= null;
		$this->_sql		= null;
		$this->_having = null;
	}
	
	public function __toString(){
		return print_r($this, true); ;
	}
	
	
	public function call($str,$param=''){
		return $this->fetchObject($this->query("CALL $str($param)")); 
	}
	
	public function startTransaction(){
		$this->query("SET AUTOCOMMIT=0");
		$this->query("START TRANSACTION");
		return $this->transactionStarted = true;
	}
	
	public function commit(){
		if($this->_transactionStarted === true)
			return $this->query("COMMIT");
	}
	
	public function rollback(){
		if($this->_transactionStarted === true)
			return $this->query("ROLLBACK");
	}
		
	
	public function close(){
		return @mysqli_close($this->conn);
	}
	
	public function error(){
		return mysqli_error($this->conn);
	}
	
	private function log($sql, $resource){
		if(LOG_DB_QUERIES == 0)
			return;
		
		if(LOG_ONLY_DB_ERRORS == 1 and $resource != false){
			return;
		}
		
		
		
		$str = '';
		if((int) $resource > 0)	$str .= "Resultado: Query executada com sucesso"."\n";
		else $str .= 'Falha ao executar : ' . $this->error()."\n";
		$str .= $sql;
		
		$log = Logger::getInstance('txt');
		$log->file	= 'mdb_mysqli_log.'.date('ymd').'.log';
		$log->message($str);
	}
	
	public function CallProcedure($procname, $args=null){
		$data = $this->query("CALL {$procname}({$args})");
		return $this->fetchObject($data);
	}
	
	
	public function __destruct(){
		$this->close(); 
		unset($this);
	}
	
}