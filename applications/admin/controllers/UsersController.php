<?php

	class UsersController extends Controller {
		
		private $get;
		private $post;
		private $files;
		private $session;
		private $views;
		
		public function init(){
			$this->session = new Session();
			
			$this->get = Request::get();
			$this->post = Request::post();
			
			$this->views = new Views(new Template("admin"));
		}
		
		public function form(){
			if($this->get->user){
				$m = Model::Factory('user');
				$user = $m->where("login='{$this->get->user}'")->get();
				
				if($user){
					$user->data = Model::Factory("user_data")->where("user_id='{$user->id}'")->get();
					$user->badges = Model::Factory("user_has_badge uhb")->innerJoin("badge b", "b.id = uhb.badge_id")->where("uhb.user_id='{$user->id}'")->order("uhb.date DESC")->all();
					$user->experience = Model::Factory("user_points")->where("user_id='{$user->id}'")->get();
					$user->social_networks = Model::Factory("user_has_social_network uhs")->innerJoin("social_network n", "n.id = uhs.social_network_id")->where("uhs.user_id='{$user->id}'")->all();
					$this->views->user = $user;
				}
			}
			
			
			$this->views->display("form-usuarios.phtml");
		}
		
		public function ban(){
			$m = Model::Factory('user');
			$m->banned = 1;
			$m->ban_date = date('Y-m-d H:i:s');
			$m->where("login='{$this->get->user}'");
			$m->update();
			
			Request::redirect(HOST . "adm/usuarios?user={$this->get->user}");
		}
		
		public function unban(){
			$m = Model::Factory('user');
			$m->banned = NULL;
			$m->ban_date = NULL;
			$m->where("login='{$this->get->user}'");
			$m->update();
			
			Request::redirect(HOST . "adm/usuarios?user={$this->get->user}");
		}
		
	}
