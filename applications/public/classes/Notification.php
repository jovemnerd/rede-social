<?php

	class Notification {
		
		const WON_A_BADGE = 1;
		const LIKED_POST = 2;
		const DISLIKED_POST = 3;
		const LIKED_COMMENT = 4;
		const DISLIKED_COMMENT = 5;
		const COMMENTED_POST = 6;
		const REPLYED_COMMENT = 7;
		const LEVELED_UP = 8;
		const CHANGED_AVATAR = 9;
		const BEFRIENDED = 10;
		const FAVORITED_A_POST = 11;
		const TAGGED_IN_A_POST = 12;
		const TAGGED_IN_A_COMMENT = 13;
		
		const REBLOGGED_POST = 14;
		
		public function __construct($action_type, $taken_by, $action_id, $notify_uid=null){
			Notification::send($action_type, $taken_by, $action_id, $notify_uid);
		}
		
		private function send($action_type, $taken_by, $action_id, $notify_uid){
			//Monta o model de notifications, com os valores padrão.
			$m = Model::Factory('notifications');
			$m->action_id = $action_id;
			$m->action_type = $action_type;
			$m->date = date('Y-m-d H:i:s');
			$m->readed = 0;
			
			switch($action_type){
				case Notification::WON_A_BADGE:
					$notify_user_id = $taken_by;
					break;
				
				case Notification::FAVORITED_A_POST:
				case Notification::DISLIKED_POST:
				case Notification::LIKED_POST:
				case Notification::REBLOGGED_POST:
					//Cria um model auxiliar para descobrir quem é o dono do post
					$aux_m = Model::Factory('posts');
					$aux_m->where("id='{$action_id}'");
					
					$m->notify_user_id = $aux_m->get()->user_id;
					$m->took_by_user_id = $taken_by;
					break;
					
				case Notification::LIKED_COMMENT:
				case Notification::DISLIKED_COMMENT:
					//Cria um model auxiliar para descobrir quem é o dono do comment
					$aux_m = Model::Factory('comment');
					$aux_m->where("id='{$action_id}'");
					
					$m->notify_user_id = $aux_m->get()->user_id;
					$m->took_by_user_id = $taken_by;
					break;
					
				case Notification::COMMENTED_POST:
					//Cria um model auxiliar para descobrir quem é o dono do post
					$postModel = Model::Factory('posts');
					$postModel->where("id='{$action_id}'");
					$post = $postModel->get();
					
					$m->notify_user_id = $post->user_id;
					$m->took_by_user_id = $taken_by;
					break;
					
				case Notification::REPLYED_COMMENT:
					//Cria um model auxiliar para descobrir quem é o dono do comment
					$aux_m = Model::Factory('comment');
					$aux_m->where("id='{$action_id}'");
					
					$m->notify_user_id = $aux_m->get()->user_id;
					$m->took_by_user_id = $taken_by;
					break;
					
				case Notification::LEVELED_UP:
					$m->notify_user_id = $taken_by;
					break;
				
				case Notification::BEFRIENDED:
				case Notification::TAGGED_IN_A_COMMENT:
				case Notification::TAGGED_IN_A_POST:
					$m->notify_user_id = $notify_uid;
					$m->took_by_user_id = $taken_by;
					$m->action_id = $action_id;
					break;
					
				case Notification::CHANGED_AVATAR:
					Phalanx::loadClasses('Friendship');
					$friends = Friendship::from_user($taken_by);
					$date = date('Y-m-d H:i:s');
					
					foreach($friends as $friend){
						$m = Model::Factory('notifications');
						$m->action_id = $action_id;
						$m->action_type = $action_type;
						$m->date = $date;
						$m->readed = 0;
						$m->notify_user_id = $friend->id;
						$m->took_by_user_id = $taken_by;
						
						if($m->took_by_user_id == $m->notify_user_id)
							continue;
						
						$m->insert();
					}
					break;
			}
			
			if($m->took_by_user_id == $m->notify_user_id){
				return;	
			}
			
			foreach($m as $k => $v){
				$notify = array();
				$notify[$k] = $v;
			}
			
			return $m->insert();
		}
		
		public static function mark_as_readed($nid){
			$m = Model::Factory('notifications');
			$m->readed = 1;
			$m->where("id='{$nid}'");
			return $m->update();
		}
		
		public static function from_user($uid, $limit=15){
			if($uid=='')
				return;
			
			Phalanx::loadClasses('NotificationSettings');
			$action_ids = NotificationSettings::from_user($uid);
			if($action_ids){
				$action_ids = implode(", ", $action_ids);
				$where = " AND action_type IN ({$action_ids})";
			}
			$query = "SELECT	d1.*,
								u.login,
								ud.avatar
						FROM	(
							SELECT	@took_by_uid:=(
								SELECT	took_by_user_id
								FROM	notifications NI USE INDEX (fk_notifications_user1, idx_notifications_uid_date)
								WHERE	NI.notify_user_id = {$uid}
								AND		NI.action_type = n.action_type
								AND		NI.action_id = n.action_id
								ORDER BY id DESC
								LIMIT 1
							  )	AS	took_by_user_id,
								COUNT(took_by_user_id) AS qtty,
								action_id,
								action_type,
								(
									SELECT	date
									FROM	notifications NI USE INDEX (fk_notifications_user1, idx_notifications_uid_date)
									WHERE	NI.notify_user_id = {$uid}
									AND		NI.action_type = n.action_type
									AND		NI.action_id = n.action_id
									ORDER BY id DESC
									LIMIT 1
								)	AS	date,
								readed,
								id
							FROM	notifications n USE INDEX (fk_notifications_user1, idx_notifications_uid_date)
							WHERE	notify_user_id = {$uid}
							AND		date > DATE_SUB(NOW(), INTERVAL 2 WEEK)
							{$where}
							GROUP BY action_id, action_type
							ORDER BY n.id DESC LIMIT {$limit}
						) d1
						INNER JOIN	user u
								ON	u.id = d1.took_by_user_id
						INNER JOIN	user_data ud USE INDEX (fk_user_data_user1)
								ON	ud.user_id = u.id
						ORDER BY	date DESC";
			
			$data = Model::ExecuteQuery($query);
			$return = array();
			$Session = new Session();
			
			Phalanx::loadClasses('Profile');
			
			foreach($data as $each){
				
				$o = new stdClass;
				$o->id = $each->id;
				$o->readed = $each->readed;
				$o->when = Date::RelativeTime($each->date);
				$o->image = AVATAR_DIR . 'square/' . $each->avatar;
				$o->classname = '';
				
				switch($each->action_type){
					case Notification::WON_A_BADGE:
						$b = Model::Factory('badge')->where("id='{$each->action_id}'")->get();
							
						$o->description = "Parabéns! Você ganhou o badge <b>" . $b->name . "</b>";
						$o->image = MEDIA_DIR.'images/badges/'.$b->icon_url;
						$o->link = 'meu-perfil/badges';
						$o->classname = 'badge';
						break;
					
					case Notification::LIKED_POST:
					case Notification::DISLIKED_POST:
						$post_title = Model::Factory('posts')->where("id='{$each->action_id}'")->get()->title;
						$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $post_title)));
						
						if($each->qtty > 1){
							$quantity = $each->qtty - 1;
							$quantity = ($quantity == 1) ? "e outro nerd marcaram" : "e outros {$quantity} nerds marcaram";
							
							if($each->action_type == Notification::LIKED_POST)
								$o->description = "<b>{$each->login}</b> {$quantity} seu post como MEGABOGA";
							else if($each->action_type == Notification::DISLIKED_POST)
								$o->description = "<b>{$each->login}</b> {$quantity} seu post como WHATEVER";
						} else {
							if($each->action_type == Notification::LIKED_POST)
								$o->description = "<b>{$each->login}</b> marcou seu post como MEGABOGA";
							else if($each->action_type == Notification::DISLIKED_POST)
								$o->description = "<b>{$each->login}</b> marcou seu post como WHATEVER";
						}
						
						$o->link = 'perfil/' . $Session->user->login . '/post/' . $each->action_id . '-' . $safe_url;
						break;
						
					case Notification::REBLOGGED_POST:
						$post_title = Model::Factory('posts')->where("id='{$each->action_id}'")->get()->title;
						$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $post_title)));
						
						if($each->qtty > 1){
							$quantity = $each->qtty - 1;
							$o->description = ($quantity == 1) ? "e outro nerd reblogaram seu post" : "e outros {$quantity} nerds reblogaram seu post";
						} else {
							$o->description = "<b>{$each->login}</b> reblogou seu post";
						}
						
						$o->link = 'perfil/' . $Session->user->login . '/post/' . $each->action_id . '-' . $safe_url;
						break;
						
					case Notification::FAVORITED_A_POST:
						$post_title = Model::Factory('posts')->where("id='{$each->action_id}'")->get()->title;
						$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $post_title)));
						
						if($each->qtty > 1){
							$quantity = $each->qtty - 1;
							$quantity = ($quantity == 1) ? "e outro nerd FAVORITARAM" : "e outros {$quantity} nerds FAVORITARAM";
						} else {
							$quantity = " FAVORITOU";
						}
						
						$o->description = "<b>{$each->login}</b> {$quantity} seu post";
						
						$o->link = 'perfil/' . $Session->user->login . '/post/' . $each->action_id . '-' . $safe_url;
						break;
					
					case Notification::DISLIKED_COMMENT:	
					case Notification::LIKED_COMMENT:
						$comment = Model::Factory('comment', true, 3600)->where("id='{$each->action_id}'")->get();
						$post = Model::Factory('posts', true, 3600)->where("id='{$comment->posts_id}'")->get();
						$post_owner = Model::Factory('user u', true, 3600)->where("u.id='{$post->user_id}'")->get();
						
						$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $post->title)));
						
						if($each->qtty > 1){
							$quantity = $each->qtty - 1;
							$quantity = ($quantity == 1) ? "e outro nerd marcaram" : "e outros {$quantity} nerds marcaram";
							
							if($each->action_type == Notification::LIKED_COMMENT)
								$o->description = "<b>{$each->login}</b> {$quantity} seu comentário como MEGABOGA";
							else if($each->action_type == Notification::DISLIKED_COMMENT)
								$o->description = "<b>{$each->login}</b> {$quantity} seu comentário como WHATEVER";
						} else {
							if($each->action_type == Notification::LIKED_COMMENT)
								$o->description = "<b>{$each->login}</b> marcou seu comentário como MEGABOGA";
							else if($each->action_type == Notification::DISLIKED_COMMENT)
								$o->description = "<b>{$each->login}</b> marcou seu comentário como WHATEVER";
						}
						
						if($post_owner->id == 0){
							$o->link = "site/post/{$post->wp_posts_ID}-";
						} else {
							$o->link = 'perfil/' . $post_owner->login . '/post/' . $post->id . '-' . $safe_url;
						}
						
						
						break;
						
					case Notification::COMMENTED_POST:
						$post = Model::Factory('posts')->where("id='{$each->action_id}'")->get();
												
						$post_title = $post->title;
						$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $post->title)));
						
						if($each->qtty > 1){
							$quantity = $each->qtty - 1;
							if($quantity == 1){
								$o->description = "<b>{$each->login}</b> e outro nerd comentaram seu post";	
							} else {
								$o->description = "<b>{$each->login}</b> e outros {$quantity} nerds comentaram seu post";
							}
						} else {
							$o->description = "<b>{$each->login}</b> comentou seu post";
						}
						
												
						$o->link = 'perfil/' . $Session->user->login . '/post/' . $each->action_id . '-' . $safe_url;
						break;
						
					case Notification::REPLYED_COMMENT:
						$comment = Model::Factory('comment')->where("id='{$each->action_id}'")->get();
						$post = Model::Factory('posts')->where("id='{$comment->posts_id}'")->get();
						
						
						$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $post->title)));
						
						$m = Model::Factory('user');
						$m->where("id='{$post->user_id}'");
						$post_owner = $m->get();
						
						if($each->qtty > 1){
							$quantity = $each->qtty - 1;
							if($quantity == 1){
								$o->description = "<b>{$each->login}</b> e outro nerd  responderam seu comentário";	
							} else {
								$o->description = "<b>{$each->login}</b> e outros {$quantity} nerds responderam seu comentário";
							}
						} else {
							$o->description = "<b>{$each->login}</b> respondeu seu comentário";
						}
						
						if($post_owner->login != $Session->user->login){
							if($post_owner->login == $each->login){
								$o->description .= " no post dele";
							} else {
								if($post_owner->id != 0){
									$o->description .= " no post de <b>{$post_owner->login}</b>";
								}
							}
						}
						
						if($post_owner->id == 0){
							$o->link = "site/post/{$post->wp_posts_ID}-";
						} else {
							$o->link = 'perfil/' . $post_owner->login . '/post/' . $comment->posts_id . '-'. $safe_url;
						}
						
						break;
						
					case Notification::LEVELED_UP:
						$o->description = "Parabéns <b>{$Session->user->login}</b>, agora você está no <b>Level {$each->action_id}</b>!";
						$o->link = "meu-perfil";
						$o->image = '';
						break;
						
					case Notification::CHANGED_AVATAR:
						$m = Model::Factory('user');
						$m->where("id='{$each->took_by_user_id}'");
						$user = $m->get();
					
						$o->description = ''. $user->login . " trocou o avatar";
						$o->link = 'perfil/' . $user->login;
						break;
						
					case Notification::BEFRIENDED:
						$u = Model::Factory('user')->where("id='{$each->took_by_user_id}'")->get(); 
						$o->description =  "<b>{$u->login}</b> aceitou seu pedido de amizade";
						$o->link = 'perfil/' . $u->login;
						break;
						
					case Notification::TAGGED_IN_A_POST:
						$u = Model::Factory('user')->where("id='{$each->took_by_user_id}'")->get();
						$od = Profile::other_data($each->took_by_user_id);
						$p = Model::Factory('posts')->where("id='{$each->action_id}'")->get();
						
						$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $p->title)));
						
						$o->description = "<b>{$u->login}</b> te citou em um post";
						$o->image = AVATAR_DIR.'square/'.$od->avatar;
						$o->link = 'perfil/' . $u->login . '/post/' . $each->action_id . '-'. $safe_url;
						
						break;
						
						
					case Notification::TAGGED_IN_A_COMMENT:
						$u = Model::Factory('user')->where("id='{$each->took_by_user_id}'")->get();
						$p = Model::Factory('posts')->where("id='{$each->action_id}'")->get();
						if($p->user_id != 0){
							$post_owner = Model::Factory('user')->where("id='{$p->user_id}'")->get();
							$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $p->title)));
							$o->link = 'perfil/' . $post_owner->login . '/post/' . $each->action_id . '-'. $safe_url;	
						} else {
							$o->link = "site/post/{$p->wp_posts_ID}-";
						}
						
						$o->description = "<b>{$u->login}</b> te citou em um comentário";
						break;
				}
				
				
				$return[] = $o;
			}

			return $return;
		}
			
	}