<?php
	class MainController extends Controller {
		
		private $post,
				$session,
				$cookies,
				$views;
		
		public function init(){
			$this->session = new Session;
			$this->cookies = new Cookies;
			$this->post = Request::post();
		}
		
		public function Index(){
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			
			if($loginController->isLoggedIn()){
				$this->feed();
			} else {
				Request::redirect(HOST . 'login');
			}
		}
		
		
		private function feed(){
			Phalanx::loadClasses('Profile', 'Lists', 'PostCategory');
			$user_data = Profile::get_profile($this->session->user->login);
			
			#Correção p/ um bug com os nomes de usuários incorretos
			if(empty($user_data->login)){
				Request::redirect(HOST);
			} else {
				#Reutilização do mesmo core para criação da timeline - Fuck yeah
				Phalanx::loadController('TimelineController');
				$Timeline = new TimelineController();
				$posts = $Timeline->BuildFromList($this->cookies->active_list);
					
				#Pego os outros dados do usuário que são utilizados na página de feed	
				$this->views = new Views(new Template("default"));
				$this->views->data = $user_data;
				$this->views->lists = Lists::from_user($this->session->user->id);
				$this->views->categories = PostCategory::get();
				$this->views->posts = $posts;
							
				$this->views->display("feed.phtml");
			}
		}
		
		public function DisplayHelpPage(){
			$this->views = new Views(new Template("default"));
			$this->views->display("help.phtml");
		}
		
		public function SessionID(){
			echo "load_skynerd_frame('{$this->session->id()}');";
		}
		
		public function __destruct(){
			unset($this);
		}
		
	}
