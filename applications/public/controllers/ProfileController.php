<?php

	class ProfileController extends Controller {
		
		private $get;
		private $post;
		private $views;
		private $session;
		private $isLoggedIn;
		
		
		public function init(){
			$this->get = Request::get();
			$this->post = Request::post();
			$this->session = new Session();
			
			$template = new Template("default");
			
			Phalanx::loadController("LoginController");
			$LoginController = new LoginController();
			if(! $LoginController->isLoggedIn()){
				$this->isLoggedIn = $template->show_login_bar = true;
			}
			$this->views = new Views($template);
			Phalanx::loadClasses('Friendship', 'Profile', 'Posts');
		}
		
		public function DisplayProfileFromShortURL(){
			Request::redirect_301(HOST.'perfil/'.$this->get->username);
		}
		
		public function DisplayProfile(){
			if($this->get->username == 'wordpressagent'){
				$this->views->display("profile_not_found.phtml");
				return;	
			}
			
			$username = $this->get->username ? $this->get->username : $this->session->user->login;
			$this->session->times_reloaded->{"profile_$username"} = 0;
			$profile_data = Profile::get_profile($username);
			
			#Perfil banido
			if($profile_data->banned == 1){
				$this->views->display("profile_banned.phtml");
				return;
			}
			
			if($profile_data->active == 0){
				$this->views->display("profile_deactivated.phtml");
				return;
			}
			
			# Perfil não encontrado
			if(!$profile_data or $profile_data->login == 'wordpressagent' or $profile_data->id == '0'){
				$this->views->display("profile_not_found.phtml");
				return;	
			}
			
			if($this->get->username and ($this->get->username != $this->session->user->login)){
				$friendship_status = Friendship::get_status($this->session->user->id, $profile_data->id);
				$its_me = false;	
			} else {
				if(! $this->session->user)
					Request::redirect(HOST.'login');
				
				$friendship_status = false;
				$its_me = true;
			}
			
			$template = new Template("default");
			$template->og = new stdClass;
			$template->og->title = $username;
			$template->og->description = 'Skynerd: A rede social do JovemNerd. Vem você também!';
			$template->og->img = MEDIA_DIR . 'images/avatar/big/' . $profile_data->aditional_info->avatar;
			
			$LoginController = new LoginController();
			if(! $LoginController->isLoggedIn())
				$template->show_login_bar = true;
			
			$this->views = new Views($template);
			$this->views->data = $profile_data;
			$this->views->data->friendship_status = $friendship_status;
			$this->views->data->its_me = $its_me;
			
			$p = Posts::from_user($profile_data->id);
			$posts = array();
			foreach($p as $key => $each){
				$v = new Views;
				$v->accept_nsfw = Profile::acceptNSFW($this->session->user->id);
				$v->current_user = $this->session->user->login;
				$v->user = $each->user;
				$v->name = $each->name;
				$v->when = $each->date;
				$v->title = $each->title;
				$v->content = $each->content;
				$v->comments = $each->comments;
				$v->replies = $each->replies;
				$v->post_id = $each->id;
				$v->original_id = $each->original_id;
				$v->is_reblogged = $each->is_reblogged;
				$v->avatar = $each->avatar;
				$v->rating = $each->rating;
				$v->my_rating = $each->my_rating;
				$v->categories = $each->categories;
				$v->its_mine = ($profile_data->id == $this->session->user->id) ? true : false;
				$v->is_favorite = $each->is_favorite;
				$v->user_points = $each->user_points;
				
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
			$this->views->data->timeline = $posts;
			 
			if($profile_data)	$this->views->display("profile.phtml");
			else 				$this->views->display("profile_not_found.phtml");
		}

		public function DisplayOldPosts(){
			$profile_data = Profile::get_profile($this->post->profile,0,0,0,0,0,0,0);
			$profile = $this->post->profile;



			if(property_exists($this->session->times_reloaded, "profile_$profile")){
				$this->session->times_reloaded->{"profile_$profile"} += 1;
			} else {
				$this->session->times_reloaded->{"profile_$profile"} = 1;
			}
			
			$p = Posts::from_user($profile_data->id, false, $this->post->min_id, $this->post->max_id);
			$posts = array();
			foreach($p as $key => $each){
				$v = new Views;
				$v->accept_nsfw = Profile::acceptNSFW($this->session->user->id);
				$v->current_user = $this->session->user->login;
				$v->user = $each->user;
				$v->name = $each->name;
				$v->when = $each->date;
				$v->title = $each->title;
				$v->content = $each->content;
				$v->comments = $each->comments;
				$v->post_id = $each->id;
				$v->original_id = $each->original_id;
				$v->is_reblogged = $each->is_reblogged;
				$v->avatar = $each->avatar;
				$v->rating = $each->rating;
				$v->my_rating = $each->my_rating;
				$v->categories = $each->categories;
				$v->its_mine = ($profile_data->id == $this->session->user->id) ? true : false;
				$v->is_favorite = $each->is_favorite;
				$v->user_points = $each->user_points;
				
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
					$v->rating->reblog_count = $originalPost->rating->reblog_count;
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
			
			
			
			header("Content-type: text/html; charset=utf-8");
			foreach($posts as $postHTML)
				echo $postHTML;
		
		}
		
		public function RequestFriendship(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			header("Content-type: text/html; charset=utf-8");
			$status = Friendship::add($this->session->user->id, $this->post->uid);
			if($status !== false){
				echo 'SUCCESS';
			} else {
				echo 'FAIL';
			}
		}
		
		public function RemoveFriend(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			header("Content-type: text/html; charset=utf-8");
			if(Friendship::remove($this->session->user->id, $this->post->uid)){
				echo 'SUCCESS';
			} else {
				echo 'FAIL';
			}
		}
		
		public function DisplayUserBadges(){
			Phalanx::loadClasses('Badges', 'Profile');
			if(isset($this->get->id)){
				$template = new Template("default");
				$template->show_login_bar = $this->isLoggedIn;
				
				$badge = Badges::get($this->get->id);
				
				$template->og = new stdClass();
				$template->og->description = $badge->description;
				$template->og->type = 'skynerd_jn:badge';
				$template->og->title = $badge->name;
				$template->og->img = MEDIA_DIR . "images/badges/{$badge->icon_url}";
				$this->views = new Views($template);	
			}
			
			
			if(isset($this->get->username)){
				$username = $this->get->username;	
			} else {
				if(! $this->session->user)
					Request::redirect(HOST.'login');
				
				$username = $this->session->user->login;
			}
			
			$profile_data = Profile::get_profile($username);
			$this->views->data = $profile_data;
			
			if($username != $this->session->user->login)
				$this->views->data->friendship_status = Friendship::get_status($this->session->user->id, $profile_data->id);
			
			$this->views->data->badges_list = Badges::from_user($profile_data->id, false);
			$this->views->display("profile.phtml");
		}
		
		public function DisplayUserFriends(){
			if($this->get->username == 'wordpressagent') Request::redirect(HOST);
			
			Phalanx::loadClasses('Profile');
			if(isset($this->get->username)){
				$username = $this->get->username;	
			} else {
				if(! $this->session->user)
					Request::redirect(HOST.'login');
				
				$username = $this->session->user->login;
			}
			
			$profile_data = Profile::get_profile($username);
			$this->views->data = $profile_data;
			
			if($username != $this->session->user->login)
				$this->views->data->friendship_status = Friendship::get_status($this->session->user->id, $profile_data->id);

			$this->views->data->friends_list = Friendship::from_user($profile_data->id, 30, 0);
			if(! $this->get->username or ($this->get->username == $this->session->user->login))
				$this->views->data->blocked_users = Friendship::getBlockedUsers($this->session->user->id);

			$this->views->display("profile.phtml");
		}
		
		public function GetFriendsPage(){
			$o = new stdClass;
			if(! $this->get->username){
				$o->status = 0;
				$o->message = "Requisição inválida: Parâmetro 'user' faltando.";
				
				header("Content-type: applicatioBAdgen/json; charset=utf-8");
				die(json_encode($o));
			}
			
			if($this->get->p && $this->get->p < 0){
				$o->status = 0;
				$o->message = "Requisição inválida: Parâmetro 'p' não pode ser menor que zero.";
				
				header("Content-type: application/json; charset=utf-8");
				die(json_encode($o));
			}
			
			$user = Profile::get_profile($this->get->username);
			$allies = Friendship::from_user($user->id, 30, $this->get->p);
			
			$o->allies = $allies;
			$o->requested_page = $this->get->p;
			
			header("Content-type: application/json; charset=utf-8");
			die(json_encode($o));
		}
		
		
		public function AllowFriendshipRequest(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$status = Friendship::approve($this->post->friend_id, $this->session->user->id);
			
			header("Content-type: text/html; charset=utf-8");
			echo ($status) ? 'SUCCESS' : 'FAIL';
		}
		
		public function DenyFriendshipRequest(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$status = Friendship::remove($this->post->friend_id, $this->session->user->id);
			
			header("Content-type: text/html; charset=utf-8");
			echo ($status) ? 'SUCCESS' : 'FAIL';
		}
		
		public function BlockFriendshipRequest(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$status = Friendship::block($this->post->friend_id, $this->session->user->id);
			
			header("Content-type: text/html; charset=utf-8");
			echo ($status) ? 'SUCCESS' : 'FAIL';
		}
		
		public function SearchPeople(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$m = Model::Factory('user u', true, 180);
			$username = trim($this->post->username);
			$m->fields(	"DISTINCT	u.name	AS name",
						"u.id				AS id",
						"u.login			AS login",
						"ud.avatar			AS avatar");
			
			$m->leftJoin('user_data ud', 'ud.user_id = u.id');
			$m->where("((u.login LIKE '{$username}%') OR u.name LIKE '{$username}%') AND u.id NOT IN('{$this->session->user->id}', 0)");
			$m->limit(15);			
			$users = $m->all();
			
			foreach($data as $k => $v){
				$users[$k]->friendship_status = Friendship::get_status($this->session->user->id, $v->id);
			}
			
			$o = new stdClass;
			
			header("Content-type:application/json;charset=utf-8");
			if($users){
				$o->status = 1;
				$o->users = $users;
			} else {
				$o->status = 0;
				$o->message = "NOT FOUND";
			}
			die(json_encode($o));
		}
	}