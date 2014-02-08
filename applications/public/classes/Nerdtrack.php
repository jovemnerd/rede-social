<?php
	Phalanx::loadClasses('NerdtrackType');

	class Nerdtrack{
		
		public static function get($wpID=null, $uid=null){
			$m = Model::Factory('nerdtrack n');
			$m->fields('n.id', 'n.content', 'nerdtrack_types_id', 'n.like_count', 'n.dislike_count');
			$m->innerJoin('posts p', 'p.id = n.posts_id');
			$m->innerJoin('user u', 'u.id = n.user_id');
			$m->where("p.wp_posts_ID='{$wpID}'");
			$data = $m->all();
			
			if(! $data)
				return false;

			$content = array(
				'songs' 	=> array(),
				'quotes'	=> array()
			);
			
			foreach($data as &$o){
				$o->content = unserialize($o->content);
				$o->my_rating = Model::Factory('rating')->fields('rating')->where("user_id='{$uid}' AND nerdtrack_id='{$o->id}'")->get()->rating;
				
				if($o->nerdtrack_types_id == 1){
					$content['songs'][] = $o;
				} else {
					$content['quotes'][] = $o;
				}
								
				unset($o->nerdtrack_types_id);
			}

			return $content;
		}
		
		public static function addSong($wpID, $uid, $data){
			$m = Model::Factory('nerdtrack');
			$m->user_id = $uid;
			$m->posts_id = Model::Factory('posts')->where("wp_posts_ID='{$wpID}'")->get()->id;
			$m->content = serialize($data);
			$m->nerdtrack_types_id = NerdtrackType::SONG;
			return (Boolean) $m->insert();
		}
		
		public static function addQuote($wpID, $uid, $data){
			$m = Model::Factory('nerdtrack');
			$m->user_id = $uid;
			$m->posts_id = Model::Factory('posts')->where("wp_posts_ID='{$wpID}'")->get()->id;
			$m->content = serialize($data);
			$m->nerdtrack_types_id = NerdtrackType::QUOTE;
			return (Boolean) $m->insert();
		}

	}
