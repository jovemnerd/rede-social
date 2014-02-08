<?php

	class Badges{
		
		public static function from_user($uid, $limit=6){
			$m = Model::Factory('user_has_badge uhb', true, 7200);
			$m->innerJoin('badge b', 'b.id = uhb.badge_id');
			$m->where("uhb.user_id = '{$uid}'");
			if($limit !== false){
				$m->order('uhb.date DESC');
				$m->limit($limit);
			}
			
			return $m->all();
		}
		
		public static function get($id=null){
			$m = Model::Factory('badge', true, 3600);
			if(! is_null($id)){
				$m->where("id='{$id}'");
				return $m->get();
			}
			
			return $m->all();
		}
		
		public static function grant($uid, $bid){
			$m = Model::Factory('user_has_badge uhb');
			$m->user_id = $uid;
			$m->badge_id = $bid;
			$m->date = date('Y-m-d H:i:s');
			return $m->insert();
		}
		
	}