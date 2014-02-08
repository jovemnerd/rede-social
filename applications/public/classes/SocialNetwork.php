<?php

	define('TWITTER', 1);
	define('FACEBOOK', 2);
	define('YOUTUBE', 3);
	define('BLOG', 4);
	define('FORUM', 5);
	define('NERDTRACK', 6);
	define('NERDSTORE', 7);
	define('INSTAGRAM', 8);
	define('PSN', 9);
	define('XBOXLIVE', 10);
	
	class SocialNetwork {
		
		const TWITTER = 1;
		const FACEBOOK = 2;
		const YOUTUBE = 3;
		const BLOG = 4;
		const FORUM = 5;
		const NERDTRACK = 6;
		const NERDSTORE = 7;
		const INSTAGRAM = 8;
		
		public static function from_user($uid){
			$m = Model::Factory('social_network sn', true, 0, 'social_networks_'.$uid);
			$m->fields('sn.id', 'sn.name', 'sn.external_link', 'uhsn.access_token', 'uhsn.active', 'sn.can_be_listed');
			$m->leftJoin('user_has_social_network uhsn', "sn.id = uhsn.social_network_id AND uhsn.user_id='{$uid}'");
			$m->where("sn.available=1");
			$m->order("sn.name");
			$data = $m->all();
			
			$r = array();
			foreach($data as $each){
				$o = new stdClass;
				$o->id = $each->id;
				$o->name = $each->name;
				$o->access_token = str_replace('/', '\/', $each->access_token); #não faço idéia do porque, mas assim funciona.
				$o->external_link = $each->external_link;
				$o->active = $each->active;
				$o->can_be_listed = $each->can_be_listed;
				$o->options = self::getOptions($uid, $each->id);
				$r[$each->id] = $o;
			}
			
			return $r;
		}
		
		public static function get_access_token($uid, $sid){
			$m = Model::Factory('user_has_social_network');
			$m->fields('access_token');
			$m->where("user_id='{$uid}' AND social_network_id='{$sid}' AND active=1");
			$resultset = $m->get();
			
			if(! $resultset)
				return false;
			
			return ($data = unserialize(stripslashes($resultset->access_token))) ? $data : stripslashes($resultset->access_token);	
		}
		
		public static function link_account($uid, $sid, $token, $active=1){
			PhxMemcache::delete('social_networks_'.$uid);
			
			$m = Model::Factory('user_has_social_network');
			$m->user_id = $uid;
			$m->social_network_id = $sid;
			$m->access_token = preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i', $token) ? $token : serialize($token);
			$m->active = $active;
			return $m->replace();
		}
		
		public static function unlink_account($uid, $sid){
			PhxMemcache::delete('social_networks_'.$uid);			
			
			$m = Model::Factory('user_has_social_network');
			$m->where("user_id='{$uid}' AND social_network_id='{$sid}'");
			return $m->delete();
		}
		
		public static function activate_account($uid, $sid){
			$m = Model::Factory('user_has_social_network');
			$m->active = 1;
			$m->where("user_id='{$uid}' AND social_network_id='{$sid}'");
			return $m->update();
		}
		
		public static function saveOptions($uid, $sid, $options){
			$m = Model::Factory('user_has_social_network');
			$m->options = serialize($options);
			$m->where("user_id='{$uid}' AND social_network_id='{$sid}'");
			return $m->update();
		}
		
		public static function getOptions($uid, $sid){
			$data = Model::Factory('user_has_social_network')->where("user_id='{$uid}' AND social_network_id='{$sid}'")->get();
			if(! $data or $data->options == '') return new stdClass;
			return unserialize($data->options);
		}
		
	}