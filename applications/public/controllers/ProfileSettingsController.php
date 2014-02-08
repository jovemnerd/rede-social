<?php

	class ProfileSettingsController extends Controller {
		
		private $session;
		private $post;
		private $views;
		private $get;
		
		public function init(){
			$this->session = new Session;
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			$this->post = Request::post();
			$this->files = Request::files();
			$this->get = Request::get();
			
			$this->views = new Views(new Template("default"));
		}
		
		public function form(){
			Phalanx::loadClasses('Profile', 'PostCategory', 'Lists', 'GamerTags', 'NotificationSettings');
			$this->views->data = Profile::get_profile($this->session->user->login, 1, 0, 1, 0, 0, 1, 1);
			$this->views->categories = PostCategory::get();
			$this->views->lists = Lists::from_user($this->session->user->id);
			$this->views->notification_settings = NotificationSettings::from_user($this->session->user->id);
			
			$this->views->message = $this->session->message;
			$this->session->message = '';
			
			$this->views->display("settings_form.phtml");
		}
		
		public function save_profile_notifications_settings(){
			$notify_array = array();
			foreach($this->post->notification_type as $v)
				$notify_array[] = (int) $v;
			
			$m = Model::Factory('user_notification_settings');
			$m->user_id = $this->session->user->id;
			$m->action_type_ids = serialize($notify_array);
			
			$o = new stdClass;
			$o->status = $m->replace() ? true : false;
			
			
			header("Content-type:text/html;charset=utf-8");
			die(json_encode($o));
		}
		
		public function save_profile_info(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$o = new stdClass;
			$s = true;
			 			
			if($this->post->nome != $this->session->user->name){
				$m = Model::Factory("user");
				$m->name = $this->post->nome;
				$m->where("id='{$this->session->user->id}'");
				$s = $m->update();
				
				if($s)
					$this->session->user->name = $this->post->name;
			}
			
			$m = Model::Factory('user_data');
			$m->address = $this->post->cidade;
			$m->telephone = $this->post->telefone;
			$m->profession = $this->post->profissao;
			$m->genre = $this->post->sexo;
			$m->cifra_bluehand = $this->post->bluehand;
			$m->minibio = $this->post->minibio;
			$m->where("user_id='{$this->session->user->id}'");
			$o->status = $m->update() && $s;
			
			header("Content-type:text/html;charset=utf-8");
			die(json_encode($o));
		}
		
		public function save_profile_privacy_data(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$o = new stdClass;
			
			$m = Model::Factory('user_privacy_settings');
			$m->profile = $this->post->profile;
			$m->posts = $this->post->posts;
			$m->social_network = $this->post->social_network;
			$m->stats = $this->post->stats;
			$m->user_id = $this->session->user->id;
			$o->status = $m->replace();
			
			PhxMemcache::delete('privacy_settings_'.$this->session->user->id);
			
			header("Content-type:text/html;charset=utf-8");
			die(json_encode($o));
		}
		
		public function SaveProfileOptions(){
			PhxMemcache::delete("nsfw_settings_{$this->session->user->id}");
				
			$m = Model::Factory("user_data");
			
			if(isset($this->post->show_nsfw))
				$m->show_nsfw = (Integer) $this->post->show_nsfw;
			
			$m->where("user_id='{$this->session->user->id}'");
			$m->update();
			
			PhxMemcache::set("nsfw_settings_{$this->session->user->id}", $this->post->show_nsfw, 0);
			
			header("Content-type:application/json;charset=utf-8");
			$o = new stdClass();
			$o->status = 1;
			die(json_encode($o));
		}
		
		public function save_profile_list_data(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			PhxMemcache::delete('lists_'.$this->session->user->id);
			
			Phalanx::loadClasses('Lists');
			switch($this->post->method){
				case 'remove_list':
					Lists::remove($this->session->user->id, $this->post->list_id);
					header("Content-type:text/html;charset=utf-8");
					$o->status = true;
					die(json_encode($o));
					break;
					
				case 'add_list':
					$data = new stdClass;
					$data->name = $this->post->list_title;
					$data->social_networks = $this->post->new_list_social_networks;
					$data->categories = $this->post->new_list_categories;
					
					$list_id = Lists::add($this->session->user->id, $data);
					
					header("Content-type:text/html;charset=utf-8");
					$o->status = (bool) $list_id;
					die(json_encode($o));
					break;
			}
		}
		
		public function save_profile_access_data(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$o = new stdClass;
			$o->status = false;
			
			#Verifico se a atual coincide com a informada
			if(md5($this->post->password) == $this->session->user->password){
				#Agora vejo se a nova foi confirmada corretamente
				if($this->post->new_password == $this->post->new_password_confirm){
					$m = Model::Factory('user');
					$m->password = md5($this->post->new_password);
					$m->where("id='{$this->session->user->id}'");
					$o->status = $m->update();
					$this->session->user->password = md5($this->post->new_password);
				}
			}
			
			header("Content-type:text/html;charset=utf-8");
			die(json_encode($o));
		}
		
	
		
		public function TakePicture(){
			$str = file_get_contents("php://input");
			file_put_contents(AVATAR_UPLOAD_DIR."upload.jpg", pack("H*", $str));
		}
		
	}