<?php

	require_once("/mnt/nfs_skynerd/skynerd/settings.php");
	#require_once("/Users/Guilherme/Sites/skynerd/settings.php");
	
	$conn = mysql_connect(MASTER_DATABASE_HOST, MASTER_DATABASE_USER, MASTER_DATABASE_PASSWORD);
	if(! $conn)
		throw new Exception("Failed to connect to the database: " . mysql_error());
	
	$sel = mysql_select_db(MASTER_DATABASE_NAME);
	if(! $sel)
		throw new Exception("Failed to select the database");
	
	$query = mysql_query('SELECT * FROM notifications WHERE date < DATE_SUB(NOW(), INTERVAL 15 DAY)');
	while($o = mysql_fetch_object($query)){
		$q = "	INSERT INTO	notifications_massive (id, notify_user_id, took_by_user_id, action_type, action_id, date, readed)
				VALUES ('{$o->id}', '{$o->notify_user_id}', '{$o->took_by_user_id}', '{$o->action_type}', '{$o->action_id}', '{$o->date}', '{$o->readed}')";
				
		echo "{$q} \n";		
				
		mysql_query($q) or die(mysql_error());
		mysql_query("DELETE FROM notifications WHERE id='{$o->id}'");
	
	}
	
