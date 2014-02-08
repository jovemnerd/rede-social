<?php

	class Timeline {

		public static function build($uid, stdClass $config){
			Phalanx::loadClasses('Friendship', 'Posts', 'PostComments', 'PostCategory', 'Favorites', 'Profile');
			$friends_ids = Friendship::get_friend_array($uid);
			$friends_ids = implode(', ', $friends_ids);
			$limit = NUMBER_OF_POSTS_LOADED_PER_TIME;
			
			$where = "";
			$whereRTs = "";
			
			if(property_exists($config, 'min')){
				$where .= " AND p.id < '{$config->min}'";
				$whereRTs .= " AND p.original_posts_id < '{$config->min}'";
			}
				
			
			if(property_exists($config, 'max')){
				$where .= " AND p.id < '{$config->max}'";
				$whereRTs .= " AND p.original_posts_id < '{$config->max}'";
			}
			
			if((! property_exists($config, 'min')) and (! property_exists($config, 'max')))
				$where = "AND p.date > DATE_SUB(NOW(), INTERVAL 2 WEEK)";

			$sql = "SELECT * FROM (
					(
						SELECT 	p.id 						AS id,
								p.original_posts_id 		AS original_id,
								1							AS reblog_count,
								p.user_id 					AS user_id,
								p.content 					AS content,
								p.date 						AS date,
								p.title						AS title,
								p.like_count				AS likes,
								p.dislike_count				AS dislikes,
								p.comment_count				AS comments,
								p.reply_count				AS replies,
								p.reblog_count				AS reblogs,
								p.promoted					AS promoted,
								u.name	 					AS name,
								u.login						AS user,
								ud.avatar 					AS avatar
						FROM	posts p
						INNER JOIN user u
								ON u.id = p.user_id
						INNER JOIN user_data ud
								ON ud.user_id = p.user_id
						WHERE 	p.user_id IN($friends_ids)
						{$where}
						AND u.banned IS NULL
						AND p.status=1
						AND p.original_posts_id IS NULL
						ORDER BY	p.id DESC
						LIMIT	{$limit}
					) UNION (
						SELECT 	p.id 						AS id,
								p.original_posts_id 		AS original_id,
								COUNT(p.original_posts_id)	AS reblog_count,
								p.user_id 					AS user_id,
								p.content 					AS content,
								p.date 						AS date,
								p.title						AS title,
								p.like_count			AS likes,
								p.dislike_count			AS dislikes,
								p.comment_count			AS comments,
								p.reply_count			AS replies,
								p.reblog_count			AS reblogs,
								p.promoted			AS promoted,
								u.name	 			AS name,
								u.login				AS user,
								ud.avatar 			AS avatar
						FROM	posts p
						INNER JOIN user u
								ON u.id = p.user_id
						INNER JOIN user_data ud
								ON ud.user_id = p.user_id
						WHERE 	p.user_id IN($friends_ids)
						{$where}
						-- {$whereRTs}
						AND u.banned IS NULL
						AND p.status=1
						AND p.original_posts_id IS NOT NULL
						GROUP BY	p.original_posts_id
						ORDER BY	p.id DESC
						LIMIT	{$limit}
					)	
				) posts
				ORDER BY id DESC
				LIMIT 10";
			
			$data = Model::ExecuteQuery($sql);
			$ret = array();
			foreach($data as $v){
				$o = new stdClass;
				$o->date = Date::RelativeTime($v->date);
				$o->id = $v->id;
				$o->original_id = $v->original_id;
				$o->reblog_count = $v->reblog_count;
				$o->name = $v->name;
				$o->title = $v->title;
				$o->avatar = $v->avatar;
				$o->user_id = $v->user_id;
				$o->rating = new stdClass;
				$o->rating->megaboga = (int) abs($v->likes);
				$o->rating->whatever = (int) abs($v->dislikes);
				$o->rating->reblog_count = (int) abs($v->reblogs);
				$o->my_rating = Posts::userRating($uid, $v->id);
				$o->is_reblogged = Posts::userHasReblogged($v->id, $uid);
				$o->content = trim($v->content);
				$o->comments = $v->comments;
				$o->replies = $v->replies;
				$o->user = $v->user;
				$o->categories = PostCategory::from_post($v->id);
				$o->is_favorite = Favorites::is_favorite($uid, $v->id);
				$o->user_points = Profile::experience($v->user_id);
				$o->promoted = (bool) $v->promoted;
				
				if(! empty($o->original_id)){
					//Se o post for um reblog, então o conteúdo dele deve ser o do reblogado, mostrando as ações
					$originalPost = Posts::from_user(false, $o->original_id);
					$originalPost = reset($originalPost);
					
					$o->content = $originalPost->content;
					$o->title = $originalPost->title;
					$o->reblogged_from = $originalPost->user;
					$o->original_date = $originalPost->date;
					$o->rating->reblog_count = $originalPost->rating->reblog_count;
					$o->categories = PostCategory::from_post($originalPost->id);
					$o->is_reblogged = Posts::userHasReblogged($originalPost->id, $uid);
				}
				
				$ret[] = $o;
			}
			
			return $ret;
		}
		
		public static function get_public_posts(stdClass $config){
			
			$where = "";
			if(property_exists($config, 'min'))
				$where .= " AND p.id < '{$config->min}'";
			
			if(property_exists($config, 'max'))
				$where .= " AND p.id < '{$config->max}'";
			
				
			Phalanx::loadClasses('Posts', 'PostComments', 'PostCategory', 'Favorites', 'Profile');

			$m = Model::Factory('posts p', false, 0);
			$m->fields(
				'DISTINCT p.id 					AS id',
				'p.original_posts_id			AS	original_id',
				'p.user_id 			AS user_id',
				'p.content 			AS content',
				'p.date 			AS date',
				'p.title			AS title',
				'p.like_count		AS likes',
				'p.dislike_count	AS dislikes',
				'p.comment_count	AS comments',
				'p.reply_count		AS replies',
				'p.promoted			AS promoted',
				'u.name 			AS name',
				'u.login			AS user',
				'ud.avatar 			AS avatar'
			);
			$m->innerJoin('user u', 'u.id = p.user_id');
			$m->innerJoin('user_data ud', 'ud.user_id = p.user_id');
			$m->where("p.public=0 {$where} AND u.banned is null AND p.status=1 AND p.date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
			$m->order('p.id DESC');
			$m->limit(NUMBER_OF_POSTS_LOADED_PER_TIME);	
			$data = $m->all();
			
			$ret = array();
			foreach($data as $v){
				$o = new stdClass;
				$o->date = Date::RelativeTime($v->date);
				$o->id = $v->id;
				$o->original_id = $v->original_id;
				$o->name = $v->name;
				$o->title = $v->title;
				$o->avatar = $v->avatar;
				$o->user_id = $v->user_id;
				$o->rating = new stdClass;
				$o->rating->megaboga = (int) $v->likes;
				$o->rating->whatever = (int) $v->dislikes;
				
				$o->my_rating = Posts::userRating($uid, $v->id);
				$o->content = trim($v->content);
				$o->comments = $v->comments;
				$o->replies = $v->replies;
				$o->user = $v->user;
				$o->categories = PostCategory::from_post($v->id);
				$o->is_favorite = Favorites::is_favorite($uid, $v->id);
				$o->user_points = Profile::experience($v->user_id);
				$o->is_reblogged = Posts::userHasReblogged($v->id, $uid);
				$o->promoted = (bool) $v->promoted;
				if(! empty($o->original_id)){
					//Se o post for um reblog, então o conteúdo dele deve ser o do reblogado, mostrando as ações
					$originalPost = Posts::from_user(false, $o->original_id);
					$originalPost = reset($originalPost);
					
					$o->content = $originalPost->content;
					$o->title = $originalPost->title;
					$o->reblogged_from = $originalPost->user;
					$o->original_date = $originalPost->date;
					$o->rating->reblog_count = $originalPost->rating->reblog_count;
					$o->is_reblogged = Posts::userHasReblogged($originalPost->id, $uid);
				}
				$ret[] = $o;
			}
			
			return $ret;
		}
		
		public static function build_from_list($uid, $list_id, stdClass $config){
			Phalanx::loadClasses('Friendship');
			$friends_ids = Friendship::get_friend_array($uid);
			$friends_ids = implode(', ', $friends_ids);
					
			$where = "";
			if(property_exists($config, 'min'))
				$where .= " AND p.id < '{$config->min}'";
			
			if(property_exists($config, 'max'))
				$where .= " AND p.id < '{$config->max}'";
			
			
			Phalanx::loadClasses('Lists', 'SocialNetwork', 'Facebook', 'Twitter', 'twitteroauth', 'Instagram', 'PostCategory', 'Favorites', 'Profile');
			
			$list = Lists::from_user($uid, $list_id);
			
			$categories = array();
			foreach($list->categories as $category)
				$categories[] = $category->id;
			
			if(sizeof($categories) > 0){
				Phalanx::loadClasses('Posts', 'PostComments');
				
				$categories_ids = implode("', '", $categories);
				$custom_tl = array();
				
				$m = Model::Factory('posts p', false, 0);
				$m->fields(
					'DISTINCT p.id 	AS id',
					'p.original_posts_id	AS original_id',
			#		'COUNT(p.original_posts_id)		AS reblog_count',
					'p.user_id 		AS user_id',
					'p.content 		AS content',
					'p.date 		AS date',
					'p.title		AS title',
					'p.promoted		AS promoted',
					'u.name 		AS name',
					'u.login		AS user',
					'ud.avatar 		AS avatar',
					'p.like_count		AS likes',
					'p.dislike_count	AS dislikes',
					'p.comment_count	AS comments',
					'p.reblog_count		AS reblogs',
					'p.reply_count		AS replies'
				);
				$m->innerJoin('user u', 'u.id = p.user_id');
				$m->innerJoin('user_data ud', 'ud.user_id = p.user_id');
				$m->innerJoin('posts_has_category phc', 'p.id = phc.posts_id');
				$m->where("p.user_id IN($friends_ids)  AND phc.category_id IN ('$categories_ids') {$where} AND u.banned IS NULL AND  p.status=1 AND p.date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
			#	$m->group("p.original_posts_id");
				$m->order('p.id DESC');
				$m->limit(NUMBER_OF_POSTS_LOADED_PER_TIME);	
				
				$skynerd_posts = $m->all();
				
				foreach($skynerd_posts as $each){
					$o = new stdClass;
					$o->date = Date::RelativeTime($each->date);
					$o->id = $each->id;
					$o->user = $each->user;
					$o->name = $each->name;
					$o->title = $each->title;
					$o->avatar = $each->avatar;
					$o->user_id = $each->user_id;
					$o->rating = new stdClass();
					$o->rating->megaboga = $each->likes;
					$o->rating->whatever = $each->dislikes;
					$o->my_rating = Posts::userRating($uid, $each->id);
					$o->content = trim($each->content);
					$o->comments = $v->comments;
					$o->replies = $v->replies;
					$o->categories = PostCategory::from_post($each->id);
					$o->is_reblogged = Posts::userHasReblogged($each->id, $uid);
					$o->is_favorite = Favorites::is_favorite($uid, $each->id);
					$o->user_points = Profile::experience($v->user_id);
					$o->promoted = (bool) $each->promoted;
					
					if(! empty($o->original_id)){
						//Se o post for um reblog, então o conteúdo dele deve ser o do reblogado, mostrando as ações
						$originalPost = Posts::from_user(false, $o->original_id);
						$originalPost = reset($originalPost);
						
						$o->content = $originalPost->content;
						$o->title = $originalPost->title;
						$o->reblogged_from = $originalPost->user;
						$o->original_date = $originalPost->date;
						$o->rating->reblog_count = $originalPost->rating->reblog_count;
						$o->is_reblogged = Posts::userHasReblogged($originalPost->id, $uid);
					}
					
					$custom_tl[] = $o;
				}
			}
			return $custom_tl;
		}
		
		
	}
