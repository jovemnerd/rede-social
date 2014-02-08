<?php

	class Favorites{
		
		public static function from_user($uid, stdClass $config){
			$where = "";
			if(property_exists($config, 'min'))
				$where .= " AND p.id < '{$config->min}'";
			
			if(property_exists($config, 'max'))
				$where .= " AND p.id < '{$config->max}'";	
				
			Phalanx::loadClasses('Posts', 'PostComments', 'PostCategory');
			$m = Model::Factory('posts p', true, 120);
			$m->fields(
				'DISTINCT p.id 	AS id',
				'p.user_id 		AS user_id',
				'p.content 		AS content',
				'p.date 		AS date',
				'p.title		AS title',
				'p.like_count		AS likes',
				'p.dislike_count	AS dislikes',
				'p.comment_count	AS comments',
				'p.reply_count		AS replies',
				'u.name 		AS name',
				'u.login		AS user',
				'ud.avatar 		AS avatar'
			);
			
			$m->innerJoin('user u', 'u.id = p.user_id');
			$m->leftJoin('user_data ud', 'ud.user_id = p.user_id');
			$m->innerJoin('favorites f', 'f.posts_id = p.id');
			
			$m->order('p.date DESC');
			$m->where("f.user_id='{$uid}' AND wp_posts_ID IS NULL AND p.status=1 {$where}");
			$m->limit(NUMBER_OF_POSTS_LOADED_PER_TIME);	
			
			$data = $m->all();
			$ret = array();
			foreach($data as $v){
				$o = new stdClass;
				$o->date = Date::RelativeTime($v->date);
				$o->id = $v->id;
				$o->name = $v->name;
				$o->title = $v->title;
				$o->avatar = $v->avatar;
				$o->user_id = $v->user_id;
				$o->rating = new stdClass;
				$o->rating->megaboga = $v->likes;
				$o->rating->whatever = $v->dislikes;
				$o->my_rating = Posts::userRating($uid, $v->id);
				$o->content = trim($v->content);
				$o->comments = $v->comments;
				$o->replies = $v->replies;
				$o->user = $v->user;
				$o->categories = PostCategory::from_post($v->id);
				$o->is_favorite = true;
				$ret[] = $o;
			}
			return $ret;
		}
		
		public static function add($uid, $pid){
			$m = Model::Factory('favorites');
			$m->posts_id = $pid;
			$m->user_id = $uid;
			$m->date = date('Y-m-d H:i:s');
			$data = $m->insert();
			return (is_int($data)) ? true : false;
		}
		
		public static function remove($uid, $pid){
			$m = Model::Factory('favorites');
			$m->where("posts_id='{$pid}' AND user_id='{$uid}'");
			return $m->delete();
		}

		public static function is_favorite($uid, $pid){
			$m = Model::Factory('favorites');
			$m->where("posts_id='{$pid}' AND user_id='{$uid}'");
			return ($m->get()) ? true : false;
		}
		
	}