<?php

	class AdminLoginController extends Controller {
		
		private $get;
		private $post;
		private $session;
		
		public function init(){
			$this->session = new Session();	
		}
		
		public function check(){
			Phalanx::loadController('public.LoginController');
			$loginController = new LoginController();
			
			
			if (! ($loginController->isLoggedIn() && in_array($this->session->user->id, array(26, 66382, 66380, 65, 1300, 83922, 95394, 138505)))){
				Request::redirect(HOST);
			}
			
		}
		
		
		
	}
