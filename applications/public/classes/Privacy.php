<?php
	
	class Privacy {
		
		public static function from_user($uid){
			$m = Model::Factory('user_privacy_settings', true, 0, 'privacy_settings_'.$uid);
			$m->where("user_id='{$uid}'");
			return $m->get();
		}
		
		public static function set($uid, stdClass $data){
			$m = Model::Factory('user_privacy_settings');
			foreach($data as $k => $v)
				$m->{$k} = $v;
				
			$m->where("user_id='{$uid}'");
			return $m->update();
		}
		
	}
