<?php

	class Friendship{
		
		public static function from_user($uid, $limit=false, $offset=false){
			$friends_ids = self::get_friend_array($uid, false);
			$friends_ids = implode(', ', $friends_ids);
			
			$m = Model::Factory('user u', true, 1800);
			$m->fields('u.id AS id', 'u.name AS name', 'u.login AS login', 'ud.avatar AS avatar', 'u.last_login');
			$m->innerJoin('user_data ud', 'ud.user_id = u.id');
			
			if($limit){
				$m->where("u.id IN($friends_ids) AND u.id<>'{$uid}'");
				if($offset === false){
					$m->order('u.last_login DESC');
					$m->limit($limit);
				} else {
					$offset *= $limit;
					$m->limit("{$offset}, {$limit}");
					$m->order('u.login');
				}
			} else {
				$m->where("u.id IN($friends_ids) AND u.id<>'{$uid}'");
				$m->order('u.login');
			}
			
			return $m->all();
		}
		
		public static function get_friend_array($uid, $only_active_in_the_last_week=true){
			$friend_array = array($uid);	
				
			$m = Model::Factory('friendship f', true, 1800);
			$m->fields('f.friend_id');
			if($only_active_in_the_last_week){
				$m->innerJoin('user u', 'u.id = f.friend_id');
				$where = " AND u.last_login > DATE_SUB(NOW(), INTERVAL 1 WEEK)";
			}
			$m->where("f.user_id='{$uid}' AND f.status=1 {$where}");
			$data = $m->all();
			foreach($data as $row){
				$friend_array[] = $row->friend_id;
			}

			$m = Model::Factory('friendship f', true, 1800);
			$m->fields('f.user_id');
			if($only_active_in_the_last_week){
				$m->innerJoin('user u', 'u.id = f.user_id');
				$where = " AND u.last_login > DATE_SUB(NOW(), INTERVAL 1 WEEK)";	
			}
			
			$m->where("f.friend_id='{$uid}' AND f.status=1 {$where}");
			$data = $m->all();
			foreach($data as $row){
				$friend_array[] = $row->user_id;
			}
			
			return $friend_array;
		}
		
		public static function pending($uid){
			$m = Model::Factory('friendship f', false);
			$m->fields('u.id AS id', 'u.name AS name', 'u.login AS login', 'ud.avatar AS avatar');
			$m->innerJoin('user u', 'u.id = f.user_id');
			$m->innerJoin('user_data ud', 'ud.user_id = u.id');
			$m->where("f.friend_id='{$uid}' AND status=0");
			return $m->all();
		}
		
		public static function approve($uid, $fid){
			$m = Model::Factory('friendship');
			$m->status = 1;
			$m->where("user_id='{$uid}' AND friend_id='{$fid}'");
			$data = $m->update();
			if($data){
				Model::ExecuteQuery("UPDATE user_data SET friend_count=friend_count+1 WHERE user_id='{$uid}' OR user_id='{$fid}'");
		
				Phalanx::loadClasses('Notification');
				$n = new Notification(Notification::BEFRIENDED, $fid, $fid, $uid);
				return true;
			}
			
			return false;
		}

		public static function block($uid, $fid){
			$m = Model::Factory('friendship');
			$m->status = 2;
			$m->where("user_id='{$uid}' AND friend_id='{$fid}'");
			return $m->update();
		}
		
		public static function add($uid, $fid){
			$m = Model::Factory('friendship');
			$m->user_id = $uid;
			$m->friend_id = $fid;
			$m->status = 0;
			return $m->insert();
		}
		
		public static function remove($uid, $fid){
			Model::ExecuteQuery("UPDATE user_data SET friend_count=friend_count-1 WHERE user_id='{$uid}' OR user_id='{$fid}'");
	
			$m = Model::Factory('friendship');
			$m->where("(user_id='{$uid}' AND friend_id='{$fid}') OR (user_id='{$fid}' AND friend_id='{$uid}')");
			return $m->delete();
		}
		
		public static function invite($uid, stdClass $data){
			$m = Model::Factory('invites');
			$m->user_id = $uid;
			$m->invited_user_email = $data->email;
			$m->message = $data->message;
			$m->date = date('Y-m-d H:i:s');
			$m->hash = md5($uid . '_' . $data->email);
			$m->insert();
		}
		
		public static function get_status($uid, $fid){
			if($uid == $fid) return false;
			
			$m = Model::Factory('friendship');
			$m->fields('status');
			$m->where("(user_id='{$uid}' AND friend_id='{$fid}') OR (user_id='{$fid}' AND friend_id='{$uid}')");
			return ($data = $m->get()) ? $data->status : false;
		}
		
		public static function getBlockedUsers($uid){
			$m = Model::Factory('friendship f', false);
			$m->fields('u.id AS id', 'u.name AS name', 'u.login AS login', 'ud.avatar AS avatar');
			$m->innerJoin('user u', 'u.id = f.user_id');
			$m->innerJoin('user_data ud', 'ud.user_id = u.id');
			$m->where("f.friend_id='{$uid}' AND status=2");
			return $m->all();	
		}
	}
