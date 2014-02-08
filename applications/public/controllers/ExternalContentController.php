<?php

	class ExternalContentController extends Controller {
		
		private $views;
		private $get;
		private $session;
		
		public function init(){
			$this->get = Request::get();
			$this->session = new Session();
		}
		
		public function SkynerdRatingFrame(){
			$v = new Views;
			$v->wpid = $this->get->wpid;
			$v->post_id = Model::Factory('posts', false)->where("wp_posts_ID='{$this->get->wpid}'")->get()->id;
			echo $v->render("iframe_likes.phtml");
		}
		
		public function GetPostData(){
			Phalanx::loadClasses('Posts');
			
			$uid = ($this->session->logged_in) ? $this->session->user->id : false; 
			$data = Posts::GetWPPostData($this->get->wpid, $uid);
			
			header("Content-type:text/html;charset=utf-8");
			$json = json_encode($data);
			$callback = ($this->get->callback) ? $this->get->callback : false;
			
			if($callback)
				die("{$callback}({$json}, {$this->get->wpid})");
			else
				die($json);
		}
		
		public function DisplayWordpressPost(){
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$this->isLoggedIn = $loginController->isLoggedIn();
			
			Phalanx::loadClasses('public.Posts', 'public.PostComments');
			
			$post = Posts::GetWPPostData($this->get->post_id, $this->session->user->id, true);
			$slug = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $post->content->post_title)));
			if($slug != $this->get->slug){
				Request::redirect_301(HOST."site/post/{$this->get->post_id}-{$slug}");
			}
			
			$v = new Views();
			$v->title = $post->content->post_title;
			$v->content = $post->content->post_content;
			$v->comments = $post->comments;
			$v->comments_array = PostComments::get($post->post_id);
			$v->replies = $post->replies;
			$v->post_id = $post->post_id;
			$v->rating = $post->rating;
			$v->when = Date::RelativeTime($post->content->post_date);
			
			$v->my_rating = $p->my_rating;
			$v->current_user = $this->session->user->login;
			$v->is_favorite = $p->is_favorite;
			$content = $v->render("post_body_wp.phtml");
			
			
			$template = new Template("default");
			$template->og = new stdClass;
			$template->og->title = $v->title;
			$template->og->description = substr(strip_tags($content), 0, 250);
			//$template->og->img = MEDIA_DIR . 'images/avatar/big/' . $profile_data->aditional_info->avatar;
			
			if(! $this->isLoggedIn)
				$template->show_login_bar = true;
			
			$v = new Views($template);
			$v->data = new stdClass();
			$v->data->post = $content;
			$v->display("single_post_display.phtml");
		}

		public function Memcache(){
			$data = Model::Factory('posts')->where("id='61134'")->get();
			
			$post_title = $data->title;
			
			echo "<pre>";
			echo "Direto do banco <br/ >";
			print_r($data);
			
			echo "<br> <br> <br> Tratato como nas notificacoes <br/ >";
			$data2 = $data;
			foreach($data2 as &$v){
				$v = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $v)));
			}
			print_r($data2);
			
			echo "<br> <br> <br> Com html entities <br/ >";
			$data3 = $data;
			foreach($data3 as &$v){
				htmlspecialchars_decode(htmlentities($v));
			}
			print_r($data3);
		} 
	} 