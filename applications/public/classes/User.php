<?php

	final class User{
		
		public static function get_id($username){
			$m = Model::Factory('user');
			$m->fields('id');
			$m->where("login='{$username}'");
			return ($data = $m->get()) ? $data->id : false;
		}
		
		public static function has_social_network($uid, $sid){
			$m = Model::Factory('user_has_social_network');
			$m->where("user_id='{$uid}' AND social_network_id='{$sid}'");
			return ($m->get()) ? true : false;
		}
		
		
		
	}
