<?php 

interface Driver{			
	public function __construct($DATABASE_HOST=null,$DATABASE_USER=null,$DATABASE_PASSWORD=null,$DATABASE_NAME=null);	
	public function connect($DATABASE_HOST,$DATABASE_USER,$DATABASE_PASSWORD,$DATABASE_NAME);
	public function fields();
	public function from($table);
	public function where($cmd);
	public function join($table,$params,$type='INNER');
	public function group();
	public function order();
	public function limit($min,$max=null);
	public function fetchOne();
	public function fetchAll();
	public function insert($data);
	public function replace($data);
	public function update($data);
	public function delete();
	public function query($sql);
	public function fetchObject($res);
	public function fetchArray($res);
	public function numRows();
	public function insertID();
	public function escapeString($string);
	
	public function __toString();
	public function call($str,$param='');
	public function startTransaction();
	public function commit();
	public function rollback();
	
	public function __destruct();
}