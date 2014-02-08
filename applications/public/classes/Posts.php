<?php

	class Posts {
		
		public static function from_user($uid=false, $pid=false, $minID=0, $maxID=0){
			Phalanx::loadClasses('PostCategory', 'PostComments', 'Favorites');
			
			$m = Model::Factory('posts p');
			$m->fields(
				'p.id 					AS id',
				'p.original_posts_id	AS original_id',
				'p.title		AS title',
				'p.user_id 		AS user_id',
				'p.content 		AS content',
				'p.date 		AS date',
				'p.public		AS privacy',
				'p.like_count		AS likes',
				'p.dislike_count	AS dislikes',
				'p.reblog_count		AS reblogs',
				'p.comment_count	AS comments',
				'p.reply_count		AS replies',
				'p.promoted			AS promoted',
				'u.name 		AS name',
				'u.login		AS user',
				'ud.avatar 		AS avatar'
			);
			$m->innerJoin('user u', 'u.id = p.user_id');
			$m->innerJoin('user_data ud', 'ud.user_id = p.user_id');
			$m->order('p.id DESC');
			
			if($minID and $maxID){
				$m->where("p.user_id = '{$uid}' AND p.id<{$minID} AND p.id<{$maxID} AND p.status=1");
			} else {
				$where = array();
				$where[] = "p.status=1";
				if($pid) $where[] = "p.id='{$pid}'";
				if($uid) $where[] = "p.user_id = '{$uid}'";
				$where = implode(" AND ", $where);
				$m->where($where);
			}
			
			$m->limit(NUMBER_OF_POSTS_LOADED_PER_TIME);
			
			$data = $m->all();
			$ret = array();
			
			$Session = new Session;
			
			foreach($data as $v){
				$o = new stdClass;
				$o->date = Date::RelativeTime($v->date);
				$o->id = $v->id;
				$o->original_id = $v->original_id;
				$o->title = $v->title;
				$o->user = $v->user;
				$o->name = $v->name;
				$o->avatar = $v->avatar;
				$o->user_id = $v->user_id;
				$o->content = trim($v->content);
				$o->rating = new stdClass;
				$o->rating->whatever = (int) abs($v->dislikes);
				$o->rating->megaboga = (int) abs($v->likes);
				$o->rating->reblog_count = (int) abs($v->reblogs);
				$o->my_rating = self::userRating($Session->user->id, $v->id);
				$o->is_reblogged = self::userHasReblogged($o->original_id, $Session->user->id);
				$o->categories = PostCategory::from_post($v->id);
				$o->comments = $v->comments;
				$o->replies = $v->replies;
				$o->privacy = $v->privacy;
				$o->promoted = $v->promoted;
				$o->is_favorite = Favorites::is_favorite($uid, $v->id);
				$o->user_points= Profile::experience($v->user_id);
				
				if(! empty($o->original_id)){
					//Se o post for um reblog, então o conteúdo dele deve ser o do reblogado, mostrando as ações
					$originalPost = self::from_user(false, $o->original_id);
					$originalPost = reset($originalPost);
					
					$o->content = $originalPost->content;
					$o->title = $originalPost->title;
					$o->reblogged_from = $originalPost->user;
					$o->original_date = $originalPost->date;
					$o->rating->reblog_count = $originalPost->rating->reblog_count;
				}
				
				$ret[] = $o;
			}
			
			return $ret;
		}


		public static function exportFromUser($uid){
			Phalanx::loadClasses('PostCategory', 'PostComments', 'Favorites');
			
			$m = Model::Factory('posts p', 0);
			$m->fields(
				'p.id 					AS id',
				'p.original_posts_id	AS original_id',
				'p.title		AS title',
				'p.user_id 		AS user_id',
				'p.content 		AS content',
				'p.date 		AS date',
				'p.public		AS privacy',
				'p.like_count		AS likes',
				'p.dislike_count	AS dislikes',
				'p.reblog_count		AS reblogs',
				'p.comment_count	AS comments',
				'p.reply_count		AS replies',
				'p.promoted			AS promoted',
				'u.name 		AS name',
				'u.login		AS user',
				'ud.avatar 		AS avatar'
			);
			$m->innerJoin('user u', 'u.id = p.user_id');
			$m->innerJoin('user_data ud', 'ud.user_id = p.user_id');
			$m->order('p.id DESC');
			
			
			$where = array();
			$where[] = "p.status=1";
			$where[] = "p.original_posts_id IS NULL";
			if($uid) $where[] = "p.user_id = '{$uid}'";
			$where = implode(" AND ", $where);
			$m->where($where);
			
			$data = $m->all();
			$ret = array();
			
			$Session = new Session;
			
			foreach($data as $v){
				$o = new stdClass;
				$o->date = Date::RelativeTime($v->date);
				$o->id = $v->id;
				$o->original_id = $v->original_id;
				$o->title = $v->title;
				$o->user = $v->user;
				$o->name = $v->name;
				$o->avatar = $v->avatar;
				$o->user_id = $v->user_id;
				$o->content = trim($v->content);
				$o->rating = new stdClass;
				$o->rating->whatever = (int) abs($v->dislikes);
				$o->rating->megaboga = (int) abs($v->likes);
				$o->rating->reblog_count = (int) abs($v->reblogs);
				$o->my_rating = self::userRating($Session->user->id, $v->id);
				$o->is_reblogged = self::userHasReblogged($o->original_id, $Session->user->id);
				$o->categories = PostCategory::from_post($v->id);
				$o->comments = $v->comments;
				$o->comments_array = PostComments::get($v->id);
				$o->replies = $v->replies;
				$o->privacy = $v->privacy;
				$o->promoted = $v->promoted;
				$o->is_favorite = Favorites::is_favorite($uid, $v->id);
				$o->user_points= Profile::experience($v->user_id);
				
				if(! empty($o->original_id)){
					//Se o post for um reblog, então o conteúdo dele deve ser o do reblogado, mostrando as ações
					$originalPost = self::from_user(false, $o->original_id);
					$originalPost = reset($originalPost);
					
					$o->content = $originalPost->content;
					$o->title = $originalPost->title;
					$o->reblogged_from = $originalPost->user;
					$o->original_date = $originalPost->date;
					$o->rating->reblog_count = $originalPost->rating->reblog_count;
				}
				
				$ret[] = $o;
			}
			
			return $ret;
		}



				
		public static function userRating($uid, $pid){
			if(empty($uid))
				return false;
			
			$m = Model::Factory('rating');
			$m->fields('rating');
			$m->where("user_id='{$uid}' AND posts_id='{$pid}'");
			return ($data = $m->get()) ? $data->rating : false;
		}
		
		public static function userHasReblogged($postID, $userID){
			if(empty($userID))	return false;
			return (Boolean) Model::Factory('posts')->where("original_posts_id='{$postID}' AND user_id='{$userID}'")->get();
		}
		
		public static function GetWPPostData($wp_id, $uid=false, $content=false){
			Phalanx::loadClasses('Favorites');
			
			$m = Model::Factory('posts');
			$m->where("wp_posts_ID='{$wp_id}'");
			$post = $m->get();
			
			
			if(! $post){
				return self::CreateWPPostReference($wp_id, $uid);
			}
			
			$data = new stdClass;
			$data->post_id = $post->id; 
			$data->rating = new stdClass();
			$data->rating->megaboga = (int) abs($post->like_count);
			$data->rating->whatever = (int) abs($post->dislike_count);
			$data->rating->my_rating = ($uid) ? self::userRating($uid, $post->id) : false;
			$data->rating->favorite = ($uid) ? Favorites::is_favorite($uid, $post->id) : false;
			$data->comments = $post->comment_count;
			$data->logged_in = (bool) $uid;
			$data->content = ($content) ? Model::Factory("jovemnerdv5_wordpress.v5_posts")->where("id='{$wp_id}'")->get() : false;
			
			return $data;
		}
		
		private static function CreateWPPostReference($wp_id, $uid){
			$WPpostM = Model::Factory('posts');
			$WPpostM->wp_posts_ID = $wp_id;
			$WPpostM->user_id = 0;
			$post_id = $WPpostM->insert();
			
			$data = new stdClass;
			$data->post_id = $post_id; 
			$data->rating = new stdClass();
			$data->rating->megaboga = 0;
			$data->rating->whatever = 0;
			$data->rating->my_rating = false;
			$data->rating->favorite = false;
			$data->logged_in = (bool) $uid;
			return $data;
		}
		
		public static function GetWPPost($wp_id){
			return Model::Factory('posts')->where("wp_posts_ID='{$wp_id}'")->get();
		}
		
		public static function Reblog($postID, $userID){
			$reblogged = self::userHasReblogged($postID, $userID);
			if($reblogged)
				return false;
			
			$m = Model::Factory('posts');
			$m->original_posts_id = $postID;
			$m->user_id = $userID;
			$m->date = date('Y-m-d H:i:s');
			$status = $m->insert();
			
			if($status){
				Model::ExecuteQuery("UPDATE posts SET reblog_count=reblog_count+1 WHERE id='{$postID}'");
				return true;
			}
			
			return false;
		}
		
		public static function Unblog($postID, $userID){
			$reblogged = self::userHasReblogged($postID, $userID);
			if(! $reblogged)
				return false;
			
			$m = Model::Factory('posts')->where("original_posts_id='{$postID}' AND user_id='{$userID}'")->delete();
			
			Model::ExecuteQuery("UPDATE posts SET reblog_count=reblog_count-1 WHERE id='{$postID}'");
			return true;
		}
	}
