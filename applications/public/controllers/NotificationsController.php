<?php
	
	class NotificationsController extends Controller {
		
		private $post;
		private $session;
		
		public function init(){
			$this->post = Request::post();
			$this->session = new Session();
			
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			
			Phalanx::loadClasses('Friendship', 'Timeline', 'Notification', 'Message');
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
		}
		
		public function get_json(){
			$o = new stdClass;
			$o->friends = Friendship::pending($this->session->user->id);
			$o->notifications = Notification::from_user($this->session->user->id, 15);
			$o->messages = Message::get($this->session->user->id, 10);
			
			header("Content-type: text/html; charset=utf-8");
			die('(' .  json_encode($o) . ');');
		}
		
		public function mark_as_readed(){
			Notification::mark_as_readed($this->post->notify_id);
		}
		
		
	}
