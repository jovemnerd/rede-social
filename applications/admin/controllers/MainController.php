<?php

	class MainController extends Controller {
		
		private $get;
		private $post;
		private $files;
		private $session;
		private $views;
		
		public function init(){
			Phalanx::loadController("admin.AdminLoginController");
			$alc = new AdminLoginController();
			$alc->check();	
				
			$this->session = new Session();
			$this->get = Request::get();
			$this->post = Request::post();
			
			$this->views = new Views(new Template("admin"));
		}
		
		public function Home(){
			$this->views->display();
		}
		
		public function __destruct(){
			
		}
		
	}
