<?php

	class SignupController {
		
		private $get;
		private $post;
		private $session;
		private $views;
		private $cookies;
		
		public function __construct(){
			$this->get = Request::get();
			$this->post = Request::post();
			$this->cookies = new Cookies();			
			
			$this->session = new Session();
			$this->views = new Views(new Template("sign"));
		}
		
		public function SignUp(){
			if($this->cookies->isBanned == 1){
				Request::redirect(HOST);
			}
			
			$ipBanned = Model::Factory('rejected_ips')->where("ip='".REQUEST_IP."'")->get();
			if($ipBanned){
				Request::redirect(HOST);
			}
			
			$this->views->display("sign_up_form.phtml");
		}
		
		public function SignUpProccess(){
			$o = new stdClass();
			$o->username = ! (bool) Model::Factory('user', false, false)->where("login='{$this->post->login}'")->get();
			$o->email =  ! (bool) Model::Factory('user', false, false)->where("email='{$this->post->email}'")->get();
			$o->status = $o->username && $o->email;
			
			if($o->status){
				$m = Model::Factory('user');
				$m->login = $this->post->login;
				$m->email = $this->post->email;
				$m->password = md5($this->post->password);
				$m->name = $this->post->real_name;
				$m->active = 1;
				$m->created_at = date('Y-m-d H:i:s');
				$user_id = $m->insert();
				
				$m = Model::Factory('user_data');
				$m->genre = 'M';
				$m->avatar = 'default.jpg';
				$m->user_id = $user_id;
				$m->insert();
				
				$m = Model::Factory('user_points');
				$m->exp = 0;
				$m->hp = 10;
				$m->gold = 0;
				$m->current_level = 1;
				$m->exp_needed = 0;
				$m->exp_to_next_level = 600;
				$m->insert();
			}
			
			header("Content-type:text/html;charset=utf-8");
			die(json_encode($o));	
			
		}
		
		
	}