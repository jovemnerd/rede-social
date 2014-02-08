<?php

	class CommentsController extends Controller {
		
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
		
		public function form(){
			Phalanx::loadClasses('public.PostComments');
			
			if($this->get->id != ''){
				$post = Model::Factory('posts', false)->where("id='{$this->get->id}'")->get();
			} else if($this->get->wpid != ''){
				$post = Model::Factory('posts', false)->where("wp_posts_ID='{$this->get->wpid}'")->get();
			}
			
			if($post){
				$this->views->post = $post;
				$this->views->comments = PostComments::get($post->id, false);
			}
			
			$this->views->pid = $this->get->id;
			$this->views->wpid = $this->get->wpid;
			$this->views->display("form-comentarios.phtml");
		}
		
		public function deleteComment(){
			$m = Model::Factory('comment');
			$m->status = 0;
			$m->deleted_by_uid = $this->session->user->id;
			$m->delete_date = date('Y-m-d H:i:s');
			$m->where("id='{$this->get->id}' or in_reply_to='{$this->get->id}'");
			$m->update();
			
			Request::redirect(HOST."adm/moderar-comentarios/?id={$this->get->pid}&wpid={$this->get->wpid}");
		}
		
		public function deleteReply(){
			$m = Model::Factory('comment');
			$m->status = 0;
			$m->deleted_by_uid = $this->session->user->id;
			$m->delete_date = date('Y-m-d H:i:s');
			$m->where("in_reply_to='{$this->get->id}'");
			$m->update();
			
			Request::redirect(HOST."adm/moderar-comentarios/?id={$this->get->pid}&wpid={$this->get->wpid}");
		}
		
	}
