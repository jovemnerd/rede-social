<?php

	class FavoritesController extends Controller{
		
		private $session,
				$get,
				$post;
		
		public function init(){
			$this->get = Request::get();
			$this->post = Request::post();
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			$this->session = new Session();
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
		}
		
		public function proccess(){
			Phalanx::loadClasses('Favorites', 'Notification');
			switch($this->post->action){
				case 'favorite':
					$n = new Notification(Notification::FAVORITED_A_POST, $this->session->user->id, $this->post->post_id);
					$status = Favorites::add($this->session->user->id, $this->post->post_id);
					break;
					
				case 'unfavorite':
					$status = Favorites::remove($this->session->user->id, $this->post->post_id);
					break;
			}
			
			header("Content-type: text/html; charset=utf-8");
			$o = new stdClass;
			$o->status = $status;
			die(json_encode($o));
		}
		
	}
