<?php
	class NotificationSettings {
		
		public static function from_user($uid){
			if(empty($uid)) return;
			
			$m = Model::Factory('user_notification_settings', true, 180);
			$m->where("user_id='{$uid}'");
			$data = $m->get();
						
			if(! $data) return false;
			return unserialize($data->action_type_ids);
		}

	}