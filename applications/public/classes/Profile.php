<?php

	final class Profile{
		
		public static function login($u, $p){
			//O login pode ser feito utilizando o nome de usuário ou email
			//Para isso, utilizo a regexp abaixo.
			$field = 'login';
			if(preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $u))
				$field = 'email';
			
			$m = Model::Factory('user', false, false);
			$m->where("{$field}='{$u}' AND password='{$p}'");
			$user = $m->get();
			
			//Se o usuário conseguiu fazer o login, salvamos quando foi a última vez que ele entrou no sistema
			if($user){
				$m = Model::Factory('login_history');
				$m->user_id = $user->id;
				$m->date = date('Y-m-d H:i:s');
				$m->ip = REQUEST_IP;
				$m->insert();
				$user->other_data = self::other_data($user->id);
				
				
				$m = Model::Factory('user');
				$m->last_login = date('Y-m-d H:i:s');
				$m->where("id='{$user->id}'");
				$m->update();
				
				
				return $user;
			}
			
			return false;
		}
				
		public static function get_profile($u, $privacy=true, $badges=true, $social_networks=true, $friends=true, $exp=true, $aditional_info=true, $gamertags=true){
			Phalanx::loadClasses('Privacy', 'Badges', 'Friendship', 'SocialNetwork', 'Posts', 'GamerTags');
			
			$m = Model::Factory('user u');
			$m->where("login='{$u}'");
			
			$user = $m->get();
			if(! $user)
				return false;
			
			# Em alguns casos, não é necessário utilizarmos todos os dados do usuário
			if($privacy) $user->privacy = Privacy::from_user($user->id);
			if($badges) $user->badges = Badges::from_user($user->id);
			if($social_networks) $user->social_networks = SocialNetwork::from_user($user->id);
			if($friends) $user->friends = Friendship::from_user($user->id, 12);
			if($exp) $user->experience = self::experience($user->id);
			if($aditional_info) $user->aditional_info = self::other_data($user->id);
			if($gamertags) $user->gamertags = GamerTags::from_user($user->id);
			
			return $user;
		}
		
		public static function get_user_info($u){
			$m = Model::Factory('user u', true, 3600);
			$m->where("login='{$u}'");
			return $m->get();
		}
		
		public static function favorites($uid){
			$m = Model::Factory('favorites');
			$m->where("user_id='{$uid}'");
			return $m->all();
		}
		
		public static function experience($uid){
			$m = Model::Factory('user_points', true, 3600);
			$m->where("user_id='$uid'");
			$exp_data = $m->get();
			$exp_data->exp_percent = floor((($exp_data->exp - $exp_data->exp_needed) * 100) / ($exp_data->exp_to_next_level - $exp_data->exp_needed));
			
			if(! $exp_data){
				$exp_data = new stdClass;
				$exp_data->exp = 0;
				$exp_data->hp = 10;
				$exp_data->gold = 0;
				$exp_data->current_level = 1;
				$exp_data->exp_needed = 0;
				$exp_data->exp_to_next_level = 600;
			}
			
			return $exp_data;
		}
	
		public static function other_data($uid){
			$m = Model::Factory('user_data');
			$m->where("user_id='{$uid}'");
			return $m->get();
		}
		
		public static function acceptNSFW($uid){
			return Model::Factory('user_data', true, 0, "nsfw_settings_{$uid}")->fields("show_nsfw")->where("user_id='{$uid}'");
		} 
		
	}