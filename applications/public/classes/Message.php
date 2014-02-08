<?php

	class Message {
		
		public static function get($uid){
			
			if(empty($uid)) return;
			
			$m = Model::Factory('messages m');
			$m->fields('m.id', 'u.id as user_id', '.u.login', 'm.title', 'm.message', 'm.status', 'm.date', 'm.in_reply_to', 'ud.avatar');
			$m->innerJoin('user u', 'u.id = m.from_user_id');
			$m->innerJoin('user_data ud', 'ud.user_id = u.id');
			$m->where("m.to_user_id='{$uid}' AND m.status <> 'D' AND m.date > date_sub(now(), interval 1 month)");
			$m->order('m.id DESC');
			$data = $m->all();
			
			foreach($data as $k => &$v){
				$v->date = Date::RelativeTime($v->date);
				$v->message = nl2br($v->message);
			}
				
			
			return $data;
		}
		
	}
