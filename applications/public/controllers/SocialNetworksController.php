<?php

	class SocialNetworksController extends Controller {
		
		private $post;
		private $session;
		
		public function init(){
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			$this->post = Request::post();
			$this->session = new Session;
		}
		
		public function saveSharingOptions(){
			Phalanx::loadClasses('SocialNetwork');
			
			if(! is_object($this->post->facebook_options))
				$this->post->facebook_options = new stdClass;
			
			if(! is_object($this->post->twitter_options))
				$this->post->twitter_options = new stdClass;
			
			SocialNetwork::saveOptions($this->session->user->id, SocialNetwork::FACEBOOK, $this->post->facebook_options);
			SocialNetwork::saveOptions($this->session->user->id, SocialNetwork::TWITTER, $this->post->twitter_options);
		}
		
	}