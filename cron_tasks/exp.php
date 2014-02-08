<?php

	require_once("/mnt/nfs_skynerd/skynerd/settings.php");
	#require_once("/Users/Guilherme/Sites/skynerd/settings.php");

	$filename = SERVER_DIRECTORY . date('YmdHis') . '_exp.txt';

	$toplevel_uid = 0;
	$toplevel_exp = 0;

	$comeco = date('YmdHis');
	file_put_contents($filename, "Começando o processamento em {$comeco}\r\n", FILE_APPEND);

	try{
		
		$conn = mysql_connect(MASTER_DATABASE_HOST, MASTER_DATABASE_USER, MASTER_DATABASE_PASSWORD);
		if(! $conn)
			throw new Exception("Failed to connect to the database: " . mysql_error());
		
		$sel = mysql_select_db(MASTER_DATABASE_NAME);
		if(! $sel)
			throw new Exception("Failed to select the database");
		
		
		
		$experience = array();
		$experience_query = mysql_query('SELECT * FROM experience');
		while($o = mysql_fetch_object($experience_query)){
			$experience[$o->level] = $o->exp_needed;
		}
		
		
		$user_points = array();
		$user_qry = mysql_query('SELECT id FROM user WHERE id <> 0 AND last_login > DATE_SUB(NOW(), INTERVAL 5 DAY)');
		while($o = mysql_fetch_object($user_qry)){
			$user_points[$o->id] = 0;
		}
			
			
		$rating_count_qry = mysql_query('SELECT user_id, COUNT(*) AS qtty FROM rating WHERE user_id <> 0 GROUP BY user_id');
		while($o = mysql_fetch_object($rating_count_qry)){
			$bonus = 0;
			if($o->qtty >= 10){
				$bonus += 300;
			}
			if($o->qtty >= 25){
				$bonus += 500;
			}
			if($o->qtty >= 50){
				$bonus += 750;
			}
			if($o->qtty >= 75){
				$bonus += 1000;
			}
			if($o->qtty >= 100){
				$bonus += 1500;
			}
			if($o->qtty >= 150){
				$bonus += 2000;
			}
			if($o->qtty >= 300){
				$bonus += 3500;
			}
			if($o->qtty >= 500){
				$bonus += 5000;
			}
			if($o->qtty >= 1000){
				$bonus += 7500;
			}
			$user_points[$o->user_id] += ($o->qtty * 100);
			$user_points[$o->user_id] += $bonus;
		}
		
		$favorites_count_qry = mysql_query('SELECT user_id, COUNT(*) AS qtty FROM favorites WHERE user_id <> 0 GROUP BY user_id');
		while($o = mysql_fetch_object($favorites_count_qry)){
			$bonus = 0;
			if($o->qtty >= 10){
				$bonus += 300;
			}
			if($o->qtty >= 25){
				$bonus += 500;
			}
			if($o->qtty >= 50){
				$bonus += 750;
			}
			if($o->qtty >= 75){
				$bonus += 1000;
			}
			if($o->qtty >= 100){
				$bonus += 1500;
			}
			if($o->qtty >= 150){
				$bonus += 2000;
			}
			if($o->qtty >= 300){
				$bonus += 3500;
			}
			if($o->qtty >= 500){
				$bonus += 5000;
			}
			if($o->qtty >= 1000){
				$bonus += 7500;
			}
			$user_points[$o->user_id] += $o->qtty * 150;
			$user_points[$o->user_id] += $bonus;
		}
			
		
		$comments_count_qry = mysql_query('SELECT user_id, COUNT(*) AS qtty FROM comment WHERE user_id <> 0 GROUP BY user_id');
		while($o = mysql_fetch_object($comments_count_qry)){
			$bonus = 0;
			if($o->qtty >= 10){
				$bonus += 500;
			}
			if($o->qtty >= 25){
				$bonus += 1000;
			}
			if($o->qtty >= 50){
				$bonus += 1500;
			}
			if($o->qtty >= 75){
				$bonus += 2000;
			}
			if($o->qtty >= 100){
				$bonus += 2500;
			}
			if($o->qtty >= 150){
				$bonus += 3500;
			}
			if($o->qtty >= 300){
				$bonus += 5000;
			}
			if($o->qtty >= 500){
				$bonus += 7500;
			}
			if($o->qtty >= 1000){
				$bonus += 10000;
			}
			$user_points[$o->user_id] += $o->qtty * 200;
			$user_points[$o->user_id] += $bonus;
		}
			
			
		$posts_count_qry = mysql_query('SELECT user_id, COUNT(*) AS qtty FROM posts WHERE user_id <> 0 GROUP BY user_id');
		while($o = mysql_fetch_object($posts_count_qry)){
			$bonus = 0;
			if($o->qtty >= 10){
				$bonus += 2000;
			}
			if($o->qtty >= 25){
				$bonus += 3000;
			}
			if($o->qtty >= 50){
				$bonus += 5000;
			}
			if($o->qtty >= 75){
				$bonus += 7500;
			}
			if($o->qtty >= 100){
				$bonus += 10000;
			}
			if($o->qtty >= 150){
				$bonus += 12000;
			}
			if($o->qtty >= 300){
				$bonus += 20000;
			}
			if($o->qtty >= 500){
				$bonus += 30000;
			}
			if($o->qtty >= 1000){
				$bonus += 50000;
			}
			$user_points[$o->user_id] += $o->qtty * 500;
			$user_points[$o->user_id] += $bonus;
		}
		
		
		$my_posts_that_have_been_favorited_query = mysql_query('SELECT	u.id AS user_id, COUNT(f.posts_id) AS qtty FROM favorites f INNER JOIN posts p ON p.id = f.posts_id INNER JOIN user u ON u.id = p.user_id WHERE f.user_id <> 0 AND u.id <> 0 GROUP BY u.id');
		while($o = mysql_fetch_object($my_posts_that_have_been_favorited_query)){
			$bonus = 0;
			if($o->qtty >= 10){
				$bonus += 300;
			}
			if($o->qtty >= 25){
				$bonus += 500;
			}
			if($o->qtty >= 50){
				$bonus += 750;
			}
			if($o->qtty >= 75){
				$bonus += 1000;
			}
			if($o->qtty >= 100){
				$bonus += 1500;
			}
			if($o->qtty >= 150){
				$bonus += 2000;
			}
			if($o->qtty >= 300){
				$bonus += 3500;
			}
			if($o->qtty >= 500){
				$bonus += 5000;
			}
			if($o->qtty >= 1000){
				$bonus += 7500;
			}
			$user_points[$o->user_id] += $bonus;
		}
		
		
		
		$sql = "
		SELECT	user_id,
				SUM(qtty) AS qtty
		FROM (
			-- Puxa os posts com avaliação positiva
			SELECT	u.id 		AS user_id,
			COUNT(r.posts_id) 	AS qtty
			FROM	rating r
				INNER JOIN posts p
						ON p.id = r.posts_id
				INNER JOIN user u
						ON u.id = p.user_id
			WHERE	r.user_id <> 0
			AND		u.id <> 0
			AND		r.rating=1
			GROUP BY	u.id
			
			UNION
			
			-- Puxa os comentários com avaliação positiva
			SELECT	u.id				AS user_id,
					COUNT(r.comment_id) AS qtty
			FROM	rating r
				INNER JOIN comment c
						ON c.id = r.comment_id
				INNER JOIN user u ON u.id = c.user_id
			WHERE	r.user_id <> 0
			AND		u.id <> 0
			AND		r.rating=1
			GROUP BY	u.id

		) derivada_1
		GROUP BY	user_id";
		
		$posts_n_comments_rating_query = mysql_query($sql);
		while($o = mysql_fetch_object($posts_n_comments_rating_query)){
			$bonus = 0;
			if($o->qtty >= 10){
				$bonus += 300;
			}
			if($o->qtty >= 25){
				$bonus += 500;
			}
			if($o->qtty >= 50){
				$bonus += 750;
			}
			if($o->qtty >= 75){
				$bonus += 1000;
			}
			if($o->qtty >= 100){
				$bonus += 1500;
			}
			if($o->qtty >= 150){
				$bonus += 2000;
			}
			if($o->qtty >= 300){
				$bonus += 3500;
			}
			if($o->qtty >= 500){
				$bonus += 5000;
			}
			if($o->qtty >= 1000){
				$bonus += 7500;
			}
			$user_points[$o->user_id] += $bonus;
		}
	
	
		$nerdstore_count_qry = mysql_query('SELECT uhsn.user_id, nu.money_spent, nu.quantity FROM user_has_social_network uhsn INNER JOIN nerdstore_users nu ON nu.email = uhsn.access_token WHERE uhsn.social_network_id=7');
		while($o = mysql_fetch_object($nerdstore_count_qry)){
			$bonus = 0;
			
			if($o->quantity >= 10){
				$bonus += 10000;
			}
			if($o->quantity >= 25){
				$bonus += 12000;
			}
			if($o->quantity >= 50){
				$bonus += 20000;
			}
			if($o->quantity >= 75){
				$bonus += 30000;
			}
			if($o->quantity >= 100){
				$bonus += 50000;
			}
			
			
			$user_points[$o->user_id] += ceil($o->money_spent * 10);
			$user_points[$o->user_id] += $bonus;
		}
		
		
		
		$promoted_posts_query = mysql_query('SELECT user_id, COUNT(user_id) AS qtty FROM posts p WHERE p.promoted=1 GROUP BY user_id');
		while($o = mysql_fetch_object($promoted_posts_query)){
			$user_points[$o->user_id] += $o->qtty * 1000;
		}
		
		foreach($user_points as $user_id => $user_experience){
			foreach($experience as $level => $exp_needed){
				
				mysql_query("DELETE FROM user_points WHERE user_id='{$user_id}'");
				
				if($exp_needed > $user_experience){
					$current_level = $level-1;
					$xp_to_current_level = $experience[$level - 1];
					$sql = "REPLACE INTO user_points SET hp=10, gold=0, exp='{$user_experience}', current_level='{$current_level}', exp_needed='{$xp_to_current_level}', exp_to_next_level='{$exp_needed}', user_id='{$user_id}'";
					break;
				} else {
					$current_level = $level;
					$xp_to_current_level = $experience[$level];
					$xp_to_next_level = $experience[$level + 1];
					$sql = "REPLACE INTO user_points SET hp=10, gold=0, exp='{$user_experience}', current_level='{$current_level}', exp_needed='{$xp_to_current_level}', exp_to_next_level='{$xp_to_next_level}', user_id='{$user_id}'";
				}
				
				# Atualização p/ dar o badge de toplevel automaticamente
				if($user_experience > $toplevel_exp){
					$toplevel_exp = $user_experience;
					$toplevel_uid = $user_id;
				}
				
			}
			
			mysql_query($sql);
			echo "$sql\r\n";
			file_put_contents($filename, "{$sql}\r\n", FILE_APPEND);
		}	
	
		echo "\n\nO TOP LEVEL É $toplevel_uid\n\n";
		mysql_query("INSERT INTO user_has_badge (badge_id, user_id) VALUES (1, $toplevel_uid)");
	
		$final = date('YmdHis');
		
	} catch (Exception $e) {
		echo "<h3>Fatal error</h3>";
		echo "<p>{$e->getMessage()}</p>";
		$final = date('YmdHis');
		
		file_put_contents($filename, "\r\n\r\n\r\nException: }{$e->getMessage()}\r\n\r\n\r\n", FILE_APPEND);
		file_put_contents($filename, "Processamento finalizado COM ERRROS em {$final}\r\n\r\n\r\n", FILE_APPEND);
		die();	
	}

	file_put_contents($filename, "Processamento finalizado com sucesso em {$final}\r\n\r\n\r\n", FILE_APPEND);
