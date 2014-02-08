<?php

	final class Twitter {
		private static $calls = 0;
		private static $friends = array();

		private function __construct(){}
				
		public static function getFriends($connection){
			self::generateFriendList($connection);
			return self::$friends;
		}
		
		private function generateFriendList($connection, $next_cursor=false){
			if(!($connection instanceof TwitterOAuth or $connection->http_code != 200)){
				throw new PhxException('Not valid parameter $connection');
			}
			
			self::$calls += 1;
			
			if($next_cursor === false)
				$friends = $connection->get('statuses/friends', array('cursor' => '-1'));
			else
				$friends = $connection->get('statuses/friends', array('cursor' => $next_cursor));
			
			foreach($friends->users as $friend_idx => $friend)
				self::$friends[] = $friend->screen_name;
			
			if(isset($friends->next_cursor_str) && $friends->next_cursor_str != 0)
				self::generateFriendList($connection, $friends->next_cursor_str);
			
		}
		
		public static function post(TwitterOAuth $connection, $text){
			$connection->post('statuses/update', array('status' => $text));
		}
		
		public static function timeline(TwitterOAuth $connection){
			
			
		}
	}
