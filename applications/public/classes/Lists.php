<?php

	class Lists {
		
		public static function from_user($uid, $list_id=false){
			$m = Model::Factory('user_lists', true, 0);
			
			if($list_id === false){
				$m = Model::Factory('user_lists', true, 0, 'lists_'.$uid);	
				$m->where("user_id='{$uid}'");
			} else {
				$m->where("user_id='{$uid}' AND id='{$list_id}'");
			}
			
			$lists = $m->all();
			
			$return = array();
			if(is_array($lists)){
				foreach($lists as $list){
					$o = new stdClass;
					$o->id = $list->id;
					$o->name = $list->name;
					$c = Model::Factory('user_lists_has_category ulhc')->fields('ulhc.category_id AS id')->where("ulhc.user_lists_id='{$list->id}'")->all();
					if($c){
						Phalanx::loadClasses('PostCategory');
						$categories = array();
						foreach($c as $category){
							$categories[] = PostCategory::translate($category->id);
						}
						$o->categories = $categories;
					}
					
					$o->social_networks = Model::Factory('user_lists_has_social_network ulhsn')->fields('sn.id AS id', 'sn.name')->innerJoin('social_network sn', 'ulhsn.social_network_id = sn.id')->where("ulhsn.user_lists_id='{$list->id}'")->all();
					$return[] = $o;
				}	
			}
			
			
			if($list_id)
				return $return[0];
			else
				return $return;
		}
		
		public static function add($uid, stdClass $data){
			$m = Model::Factory('user_lists');
			$m->name = $data->name;
			$m->user_id = $uid;
			$list_id = $m->insert();
			
			if(isset($data->social_networks)){
				foreach($data->social_networks as $social_network){
					$m = Model::Factory('user_lists_has_social_network');
					$m->user_lists_id = $list_id;
					$m->social_network_id = $social_network;
					$m->insert();
				}
			}
			
			if(isset($data->categories)){
				foreach($data->categories as $category){
					$m = Model::Factory('user_lists_has_category');
					$m->user_lists_id = $list_id;
					$m->category_id = $category;
					$m->insert(); 
				}
			}
			
			PhxMemcache::delete('lists_'.$this->session->user->id);
			
			return $list_id;
		}
		
		public static function remove($uid, $list_id){
			Model::Factory('user_lists_has_social_network')->where("user_lists_id='{$list_id}'")->delete();
			Model::Factory('user_lists_has_category')->where("user_lists_id='{$list_id}'")->delete();
			Model::Factory('user_lists')->where("user_id='{$uid}' AND id='{$list_id}'")->delete();
			
			PhxMemcache::delete('lists_'.$this->session->user->id);
		}
		
	}