<?php

	class ProfileGamerTagsController extends Controller {
		
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
		
		public function save(){
			$o = new stdClass;
			
			$m = Model::Factory('user_gamertags');
			$m->user_id = $this->session->user->id;
			$m->psn = $this->post->gamertags->psn;
			$m->xboxlive = $this->post->gamertags->xboxlive;
			$m->steam = $this->post->gamertags->steam;
			$m->battlelog = $this->post->gamertags->battlelog;
			$m->nuuvem = $this->post->gamertags->nuuvem;
			$m->origin = $this->post->gamertags->origin;
			$m->gamecenter = $this->post->gamertags->gamecenter;
			$m->battlenet = $this->post->gamertags->battlenet;
			$m->raptr = $this->post->gamertags->raptr;
			$m->lol = $this->post->gamertags->lol;
			$o->status = $m->replace();
			
			header("Content-type:text/html;charset=utf-8");
			die(json_encode($o));
		}
		
		
	}