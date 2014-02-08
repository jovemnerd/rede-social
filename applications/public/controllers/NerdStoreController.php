<?php

	class NerdStoreController extends Controller{
		
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
		
		public function login(){
			$uid = $this->session->user->id;	
			$token = md5(date('Ymd') . $this->session->user->id . $this->session->user->login . $this->session->user->login . 'COMPRABASTANTEAQUINERD');
			
			$v = new Views();
			$v->username = $this->session->user->login;
			$v->link = HOST . "meu-perfil/redes-sociais/nerdstore/callback/?uid={$uid}&token={$token}";
			$message = $v->render('mail/nerdstore-link-account.phtml');
			
			Phalanx::loadExtension('phpmailer');
			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			
			$mail_status = true;
			try{
				$mail->AddReplyTo(MAIL_FROM, MAIL_ALIAS);
				$mail->AddAddress($this->post->email_address, $this->session->user->login);
				$mail->Subject = 'SkyNerd: Vínculo de conta da Nerdstore';
				$mail->MsgHTML($message);
				$mail->Send();
			}catch(phpmailerException $e){
				$mail_status = false;
			}
			
			header("Content-type: text/html; charset=utf-8");
			if($mail_status){
				Phalanx::loadClasses('SocialNetwork');
				SocialNetwork::link_account($this->session->user->id, NERDSTORE, $this->post->email_address, false);
				die('SUCCESS');
			} else {
				die('FAIL');
			}	
		}
		
		public function logout(){
			Phalanx::loadClasses('SocialNetwork');
			SocialNetwork::unlink_account($this->session->user->id, NERDSTORE);
			Request::redirect(HOST.'perfil/configuracoes');
		}
		
		public function callback(){
			$m = Model::Factory('user');	
			$m->where("id='{$this->get->uid}'");
			$data = $m->get();
			
			$expected_token = md5(date('Ymd') . $data->id . $data->login . $data->login . 'COMPRABASTANTEAQUINERD');
			if($expected_token == $this->get->token){
				Phalanx::loadClasses('SocialNetwork');
				SocialNetwork::activate_account($this->session->user->id, NERDSTORE);
				Request::redirect(HOST.'perfil/configuracoes');
			} else {
				Request::redirect(HOST.'perfil/configuracoes');	
			}
		}
		
	}
