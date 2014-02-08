<?php

	class MessagesController extends Controller {
		
		private $get,
				$post,
				$session;
		
		public function init(){
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
						
			$this->session = new Session();
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
				
			$this->get = Request::get();
			$this->post = Request::post();
		}
		
		public function SendMessage(){
			$data = false;
			
			if(($this->session->user->id != '') and ($this->post->send_message_to_uid != '')){
				$m = Model::Factory('messages');
				$m->from_user_id = $this->session->user->id;
				$m->to_user_id = $this->post->send_message_to_uid;
				$m->title = $this->post->message_title;
				$m->message = $this->post->message_content;
				$m->status = 'U';
				$m->ip = REQUEST_IP;
				$m->date = date('Y-m-d H:i:s');
				$data = $m->insert();	
			}
			
			
			$o = new stdClass;
			if($data)	$o->status = 'SUCCESS';
			else 		$o->status = 'ERROR';
			
			die('(' . json_encode($o) . ');');
		}
		
		public function MarkAsReaded(){
			$ids = array();
			foreach($this->post->id as $id)
				$ids[] = $id;
			
			$ids = implode("', '", $ids);
			
			$m = Model::Factory('messages');
			$m->status = 'R';
			$m->where("id IN('{$ids}') AND to_user_id='{$this->session->user->id}'");
			$m->update();
		}
		
		public function DeleteMessage(){
			$m = Model::Factory('messages');
			$m->status = 'D';
			$m->where("id='{$this->get->msgid}' AND to_user_id='{$this->session->user->id}'");
			$status = $m->update();
			Request::redirect(HOST);
		}
		
	}