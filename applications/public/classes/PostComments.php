<?php

	class PostComments{
		
		public static function get($post_id, $cache_time=0, $sort=null){
			
			Phalanx::loadClasses('public.Profile', 'public.Badges');
			
			$cache_time = ($cache_time) ? $cache_time : MEMCACHE_SECONDS;
			$m = Model::Factory('comment c', true, $cache_time);
			$m->fields('c.id	AS id', 
			'u.id			AS user_id', 
			'c.comment			AS comment', 
			'c.date				AS date',
			'c.in_reply_to		AS in_reply_to',
			'c.like_count		AS likes',
			'c.dislike_count	AS dislikes', 
			'u.login			AS user',
			'ud.avatar			AS avatar',
			'c.wp_comment_author		AS wp_comment_author',
			'c.wp_comment_author_email	AS wp_comment_author_email');
			$m->leftJoin('user u', 'u.id = c.user_id');
			$m->leftJoin('user_data ud', 'ud.user_id = u.id');
			$m->where("posts_id='{$post_id}' AND c.status=1 AND u.banned IS NULL");
			
			if(is_null($sort))
				$m->order("c.id ASC");
			elseif($sort == 'like')
				$m->order("c.like_count DESC");
				
			$data = $m->all();
			
			$comments = array();
			$Session = new Session;
			
			if(is_array($data)){
				foreach($data as $each){
					$o = new stdClass;
					$o->id = $each->id;
					$o->comment = $each->comment;
					$o->date = Date::RelativeTime($each->date);
					$o->rating = new stdClass();
					$o->rating->megaboga = (int) $each->likes;
					$o->rating->whatever = (int) $each->dislikes;
					
					$o->my_rating = self::userRating($Session->user->id, $each->id);
					$o->user = new stdClass;
					$o->create_links = ($each->user_id == 0) ? false : true; 
					$o->user->login = ($each->user_id == 0) ? $each->wp_comment_author : $each->user;
					$o->user->avatar = ($each->user_id == 0) ? "http://www.gravatar.com/avatar/" . md5(strtolower(trim($each->wp_comment_author_email))) . "?d=" . urlencode(MEDIA_DIR.'images/avatar/square/default.jpg') . "&s=44" : $each->avatar;
					 
					$o->user->id = $each->user_id;
					if($each->user_id != 0){
						$o->user->experience = Profile::experience($each->user_id);
						$o->user->badges = Badges::from_user($each->user_id, 4);
					}
					
					if($each->in_reply_to == '' || $each->in_reply_to == '0'){
						$o->replies = is_array($comments[$each->id]->replies) ? $comments[$each->id]->replies : array();
						$comments[$each->id] = $o;
					} else {
						$comments[$each->in_reply_to]->replies[] = $o;
					}
				}
			}

			return $comments;
		}

		public static function userRating($uid, $cid){
			if(empty($uid))
				return false;
			
			
			$m = Model::Factory('rating');
			$m->fields('rating');
			$m->where("user_id='{$uid}' AND comment_id='{$cid}'");
			$data = $m->get();
			return ($data) ? $data->rating : false;
		}
		
	}
