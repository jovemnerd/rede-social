<?php

	class Posts {
		
		public static function em_destaque(){
			$m = Model::Factory('posts p', false);
			$m->fields(
				'p.id',
				'p.date',
				'p.like_count',
				'p.dislike_count',
				'p.comment_count',
				'p.reblog_count',
				'p.title',
				'p.content',
				'u.login'
			);
			$m->innerJoin('user u', 'u.id = p.user_id');
			$m->where("promoted IS NULL AND wp_posts_ID IS NULL AND date > DATE_SUB(NOW(), INTERVAL 1 DAY) AND original_posts_id IS NULL");
			$m->order("like_count DESC, comment_count DESC");
			$m->limit(25);
			return $m->all();
		}
		
		public static function get($pid){
			$m = Model::Factory('posts p', false);
			$m->fields(
				'p.id',
				'p.date',
				'p.like_count',
				'p.dislike_count',
				'p.comment_count',
				'p.reblog_count',
				'p.title',
				'p.content',
				'u.login'
			);
			$m->innerJoin('user u', 'u.id = p.user_id');
			$m->where("p.id='{$pid}' AND promoted IS NULL");
			return $m->get();
		}
		
	}
