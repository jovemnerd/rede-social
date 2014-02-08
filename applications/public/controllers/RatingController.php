<?php
	
	class RatingController extends Controller {
		
		private $get;
		private $post;
		private $views;
		private $session;
		
		private $model;
		private $isLoggedIn = false;
		
		public function init(){
			Phalanx::loadClasses('Notification');
			
			$this->get = Request::get();
			$this->post = Request::post(true, REPLACE);
			$this->session = new Session();
			$template = new Template("default");
			
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$this->isLoggedIn = $loginController->checkStatus(); 
			
			if(! $this->isLoggedIn)
				$template->show_login_bar = true;
			
			$this->views = new Views($template);
		}
		
		public function Rate(){
			Phalanx::loadClasses('Notification');
			
			if(! $this->isLoggedIn)
				return;
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$m = Model::Factory('rating');
			switch($this->post->action){
				case 'rate':
					$m->date = date('Y-m-d H:i:s');
					$m->ip = REQUEST_IP;
					$m->user_id = $this->session->user->id;
					$m->rating = $this->post->rating;
					if($this->post->comment_id){
						$m->comment_id = $this->post->comment_id;
					} elseif($this->post->post_id) {
						$m->posts_id = $this->post->post_id;
					}
					
					$status = $m->insert();
					if($status){
						if($this->post->comment_id){
							if($this->post->rating == 1){
								$n = new Notification(Notification::LIKED_COMMENT, $this->session->user->id, $this->post->comment_id);
								Model::ExecuteQuery("UPDATE comment SET like_count = like_count+1 WHERE id='{$this->post->comment_id}'");
							} else {
								$n = new Notification(Notification::DISLIKED_COMMENT, $this->session->user->id, $this->post->comment_id);
								Model::ExecuteQuery("UPDATE comment SET dislike_count = dislike_count+1 WHERE id='{$this->post->comment_id}'");
							}
						} else {
							if($this->post->rating == 1){
								$n = new Notification(Notification::LIKED_POST, $this->session->user->id, $this->post->post_id);
								Model::ExecuteQuery("UPDATE posts SET like_count = like_count+1 WHERE id='{$this->post->post_id}'");
							} else {
								$n = new Notification(Notification::DISLIKED_POST, $this->session->user->id, $this->post->post_id);
								Model::ExecuteQuery("UPDATE posts SET dislike_count = dislike_count+1 WHERE id='{$this->post->post_id}'");
							}
						}	
					}
					break;
					
				case 'unrate':
					if($this->post->comment_id){
						$m->where("comment_id='{$this->post->comment_id}' AND user_id='{$this->session->user->id}'");
						if($this->post->current_vote == '1')
							Model::ExecuteQuery("UPDATE comment SET like_count = like_count-1 WHERE id='{$this->post->comment_id}' AND like_count>0"); 
						else
							Model::ExecuteQuery("UPDATE comment SET dislike_count = dislike_count-1 WHERE id='{$this->post->comment_id}' AND dislike_count>0");
					} elseif($this->post->post_id) {
						$m->where("posts_id='{$this->post->post_id}' AND user_id='{$this->session->user->id}'");
						if($this->post->current_vote == '1')
							Model::ExecuteQuery("UPDATE posts SET like_count = like_count-1 WHERE id='{$this->post->post_id}' AND like_count>0"); 
						else
							Model::ExecuteQuery("UPDATE posts SET dislike_count = dislike_count-1 WHERE id='{$this->post->post_id}' AND dislike_count>0");
					}
					$status = $m->delete();
					break;
					
				case 'change_rate':
					if(! $this->post->current_vote or $this->post->current_vote == ''){
						return;
					}

					$m->rating = $this->post->rating;
					if($this->post->comment_id){
						$m->where("comment_id='{$this->post->comment_id}' AND user_id='{$this->session->user->id}'");
					} else {
						$m->where("posts_id='{$this->post->post_id}' AND user_id='{$this->session->user->id}'");
					}
					$status = $m->update();
					if($status){
						if($this->post->comment_id){
							$m->where("comment_id='{$this->post->comment_id}' AND user_id='{$this->session->user->id}'");
							if($this->post->current_vote == '1'){
								Model::ExecuteQuery("UPDATE comment SET like_count=like_count-1, dislike_count=dislike_count+1 WHERE id='{$this->post->comment_id}' AND like_count>0");
							} else {
								Model::ExecuteQuery("UPDATE comment SET dislike_count=dislike_count-1, like_count=like_count+1 WHERE id='{$this->post->comment_id}' AND dislike_count>0");
							}
								
						} else {
							$m->where("posts_id='{$this->post->post_id}' AND user_id='{$this->session->user->id}'");
							if($this->post->current_vote == '1'){
								Model::ExecuteQuery("UPDATE posts SET dislike_count=dislike_count+1 WHERE id='{$this->post->post_id}'");
								Model::ExecuteQuery("UPDATE posts SET like_count=like_count-1 WHERE id='{$this->post->post_id}' AND like_count>0");
							} else {
								Model::ExecuteQuery("UPDATE posts SET like_count=like_count+1 WHERE id='{$this->post->post_id}'");
								Model::ExecuteQuery("UPDATE posts SET dislike_count=dislike_count-1 WHERE id='{$this->post->post_id}' AND dislike_count>0");
							}
						}
					}
					break;
			}
			
			#Faz a contagem dos likes e dislikes
			if($this->post->comment_id){
				$m = Model::Factory('comment', false, false);
				$m->fields("like_count", "dislike_count");
				$m->where("id='{$this->post->comment_id}'");
				$data = $m->get();
			} else {
				$m = Model::Factory('posts', false, false);
				$m->fields("like_count", "dislike_count");
				$m->where("id='{$this->post->post_id}'");
				$data = $m->get();
			}
			
			header("Content-type: text/html; charset=utf-8");
			$o = new stdClass;
			$o->status = $status;
			$o->quantity = new stdClass;
			$o->quantity->whatever = $data->dislike_count;
			$o->quantity->megaboga = $data->like_count;
			die(json_encode($o));
		}

		public function GetInfo(){
			$m = Model::Factory('rating r');
			$m->fields("u.login", "ud.avatar", "u.name");
			$m->innerJoin("user u", "u.id = r.user_id");
			$m->leftJoin("user_data ud", "ud.user_id = u.id");
			if($this->post->comment_id)
				$m->where("r.comment_id='{$this->post->comment_id}' AND r.rating='{$this->post->rating}'");
			elseif($this->post->post_id)
				$m->where("r.posts_id='{$this->post->post_id}' AND r.rating='{$this->post->rating}'");
			
			$m->order("u.login ASC");
			$data = $m->all();
			
			header("Content-type:text/html;charset=utf-8");
			die(json_encode($data));	
		}
		
	}
