<?php

	class PostCategory {
		
		public static function get($category=null){
			if(is_null($category)){
				$c = self::getAvailableCategories();
				asort($c);
				
				$categories = array();
				foreach($c as $id => $category){
					$o = new stdClass;
					$o->id = $id;
					$o->name = $category;
					$categories[] = $o;
				}
				
				return $categories;
			} else {
				$c = Model::Factory("category", false)->where("name='{$category}'")->get();
				if($c)
					return $c->id;
				
				$m = Model::Factory('category');
				$m->name = $category;
				return $m->insert();
			}
		}
		
		
		
		public static function from_post($post_id){
			
			$c = self::getAvailableCategories();
			 			
			$m = Model::Factory('posts_has_category phc', true, 3600);
			$m->fields('category_id');
			$m->where("phc.posts_id='{$post_id}'");
			$post_categories = $m->all();
			
			
			$return = array();
			
			foreach($post_categories as $each){
				$o = new stdClass();	
				$o->name = $c[$each->category_id];
				
				$return[] = $o; 
			}
			
			return $return;
		}
		
		public static function translate($id){
			return Model::Factory("category", false)->where("id='{$id}'")->get();
		}
		
		private static function getAvailableCategories(){
			$m = Model::Factory('category');
			$m->limit(50);
			$data = $m->all();
			
			$categories = array();
			foreach($data as $each)
				$categories[$each->id] = $each->name;

			return $categories;
		}
		
	}
