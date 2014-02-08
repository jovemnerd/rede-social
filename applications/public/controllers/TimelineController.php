<?php
	
	class TimelineController extends Controller {
		
		private $session,
				$post,
				$get;
		
		public function init(){
			$this->session = new Session;
			$this->post = Request::post(false, false);
			$this->get = Request::get();
			
			Phalanx::loadClasses('Timeline', 'Posts');
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
		}
		
		private function SimpleBuild(){
			$posts = Timeline::build($this->session->user->id, new stdClass);
			return $this->render($posts);
		}
		
		private function MyFavorites(stdClass $config){
			Phalanx::loadClasses('Favorites');
			return Favorites::from_user($this->session->user->id, $config);
		}
		
		public function BuildFromList($list_id=null){
			$Lid = is_null($list_id) ? $this->post->list_id : $list_id;
			
			if(is_numeric($Lid)){
				#Retorna o array com os posts da TL
				$timeline = Timeline::build_from_list($this->session->user->id, $Lid, new stdClass);
				$posts = $this->render($timeline);
			} elseif($Lid == 'bookmarks') {
				#Retorna os favoritos do usuário
				$favs = $this->MyFavorites(new stdClass());
				$posts = $this->render($favs);
			} elseif($Lid == 'all_posts'){
				$posts = Timeline::get_public_posts(new stdClass());
				$posts = $this->render($posts);
			} else {
				$posts = $this->SimpleBuild();
			}

			if(Request::isAjax()){
				header("Content-type:text/html; charset=utf-8");
				if(is_array($posts))	echo implode('', $posts);	
				else 	die('{status: "FAIL"}');
			} else {
				return is_array($posts) ? $posts : false;
			}
			
		}
		
		public function GetOlderPosts(){
			#Aumenta o contador
			if(! $this->session->times_reloaded)
				$this->session->times_reloaded = new stdClass;
			
			$list_id = is_null($this->post->list_id) ? '' : $list_id;
			
			$o = new stdClass;
			$o->min = $this->post->min_id;
			$o->max = $this->post->max_id;

			if($this->post->list_id == ''){
				$data = Timeline::build($this->session->user->id, $o);
				$posts = $this->render($data);
				
			} elseif($this->post->list_id == 'bookmarks') {
				$data = $this->MyFavorites($o);
				$posts = $this->render($data);
			} elseif($this->post->list_id == 'all_posts') {
				$data = Timeline::get_public_posts($o);
				$posts = $this->render($data);
			} elseif(is_numeric($this->post->list_id)) {
				$data = Timeline::build_from_list($this->session->user->id, $list_id, $o);
				$posts = $this->render($data);
			}
			
			header("Content-type: text/html; charset=utf-8");
			if(is_array($posts))
				foreach($posts as $post)
					echo $post;
			
			else die('');
		}
		
		public function Render($data){
			Phalanx::loadClasses('Profile');
			$posts = array();
			
			foreach($data as $key => $each){
				$v = new Views;
				$v->accept_nsfw = Profile::acceptNSFW($this->session->user->id);
				$v->original_id = $each->original_id;
				$v->reblog_count = $each->reblog_count;
				$v->is_reblogged = $each->is_reblogged;
				$v->current_user = $this->session->user->login;
				$v->user = $each->user;
				$v->title = $each->title;
				$v->name = $each->name;
				$v->when = ($each->when) ? $each->when : $each->date;
				$v->content = $each->content;
				$v->via = $each->via;
				$v->comments = $each->comments;
				$v->replies = $each->replies;
				$v->rating = $each->rating;
				$v->my_rating = $each->my_rating;
				$v->post_id = $each->id;
				$v->avatar = $each->avatar;
				$v->categories = $each->categories;
				$v->is_favorite = $each->is_favorite;
				$v->is_reblogged = $each->is_reblogged;
				$v->its_mine = ($each->user_id == $this->session->user->id) ? true : false;
				$v->user_points = $each->user_points;
				$v->promoted = (bool) $each->promoted;
				
				if(! empty($each->original_id)){
					//Se o post for um reblog, então o conteúdo dele deve ser o do reblogado, mostrando as ações
					$originalPost = Posts::from_user(false, $v->original_id);
					$originalPost = reset($originalPost);
					
					$v->content = $originalPost->content;
					$v->title = $originalPost->title;
					$v->reblogged_from = $originalPost->user;
					$v->reblog_avatar = $originalPost->avatar;
					$v->reblog_points = $originalPost->user_points;
					
					$v->original_date = $originalPost->date;
					$v->comments = $originalPost->comments;
					$v->replies = $originalPost->replies;
					$v->is_favorite = $originalPost->is_favorite;
					$v->categories = $originalPost->categories;
					$v->rating = $originalPost->rating;
					$v->id = $v->post_id;
					$v->post_id = $originalPost->id;
				}
				
				
				$content = $v->render("post_body.phtml");
				$posts[] = $content;
			}
			return $posts;
		}
	}