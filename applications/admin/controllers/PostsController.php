<?php

	class PostsController extends Controller {
		
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
			
			Phalanx::loadClasses('Posts');
		}
		
		public function EmDestaque(){
			if($this->get->id){
				$this->views->post_id = $this->get->id;
				$this->views->search_data = Posts::get($this->get->id);
			}
			
			$this->views->data = Posts::em_destaque();
			$this->views->display('posts-em-destaque.phtml');
		}
		
		public function Promover(){
			$m = Model::Factory('posts');
			$m->promoted = 1;
			$m->promoted_on = date('Y-m-d H:i:s');
			$m->where("id='{$this->get->pid}'");
			$m->update();
			
			Request::redirect(HOST.'adm/posts-em-destaque');
		}
		
		public function __destruct(){
			unset($this);
		}
		
	}
