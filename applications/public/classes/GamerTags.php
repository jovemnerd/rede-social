<?php
	class GamerTags {
		
		public static function from_user($uid){
			if(empty($uid)) return;
			
			$m = Model::Factory('user_gamertags');
			$m->where("user_id='{$uid}'");
			return $m->get();
		}

	}