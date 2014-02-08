<?php
	
	class ReblogController extends Controller {
		
		private $get;
		private $isLoggedIn = false;
		private $session;
		
		public function init(){
			$this->get = Request::get();
			$this->session = new Session();
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$this->isLoggedIn = $loginController->checkStatus();
			
			Phalanx::loadClasses('Posts', 'Notification'); 
		}
		
		public function Reblog(){
			
			$s = Posts::Reblog($this->get->postID, $this->session->user->id);
			
			$n = new Notification(Notification::REBLOGGED_POST, $this->session->user->id, $this->get->postID);
			
			$o = new stdClass;
			$o->status = $s;
			$o->count = Model::Factory('posts')->fields('reblog_count')->where("id='{$this->get->postID}'")->get()->reblog_count;
			
			header("Content-type:application/json;charset=utf-8");
			die(json_encode($o));
		}
		
		public function Unblog(){
			$s = Posts::Unblog($this->get->postID, $this->session->user->id);
			
			$o = new stdClass;
			$o->status = $s;
			$o->count = Model::Factory('posts')->fields('reblog_count')->where("id='{$this->get->postID}'")->get()->reblog_count;
			
			header("Content-type:application/json;charset=utf-8");
			die(json_encode($o));
		}

	}
