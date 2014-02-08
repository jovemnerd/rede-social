<?php

	class PasswordController extends Controller{
	
		public function init(){
			$this->views = new Views(new Template("sign"));
			$this->post = Request::post();
			$this->session = new Session();
		}
		
		public function forgot_my_password(){
			if($this->post->username!='' or $this->post->email!=''){
				$m = Model::Factory('user');
				if($this->post->username != ''){
					$m->where("login='{$this->post->username}'");
				} elseif($this->post->email != '') {
					$m->where("email='{$this->post->email}'");
				}
				$ud = $m->get();

				if($ud){
					$token = $ud->id . '/' .md5(date('Ymd') . $ud->email . $ud->login . $ud->password . 'NOSSANOSSAASSIMVOCEMEMATA' . '< HA ZUEI');
					$this->send_password_reset_email($token, $ud->email, $ud->login);
				} else {
					$this->session->message = 'ResetPasswordMailNotSent';
					Request::redirect(HOST . 'login');
				}
			} else {
				$this->views->display("password-reset-request.phtml");
			}
		}
		
		private function send_password_reset_email($token, $email, $login){
			$v = new Views();
			$v->username = $login;
			$v->link = HOST . 'esqueci-minha-senha/' . $token . '/';
			$message = $v->render('mail/password_change_request.phtml');
			
			Phalanx::loadExtension('phpmailer');
			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			
			$mail_status = true;
			try {
				$mail->AddReplyTo(MAIL_FROM, MAIL_ALIAS);
				$mail->AddAddress($email, $login);
				$mail->Subject = 'SkyNerd: Troca de senha';
				$mail->MsgHTML($message);
				$mail->Send();
			} catch (phpmailerException $e) {
				$mail_status = false;
				var_dump($mail);
			}
			
			if($mail_status){
				$this->session->message = 'PasswordChangeEmailSent';
			} else{
				$this->session->message = 'PasswordChangeEmailNOTSent';
			}
				
			Request::redirect(HOST . 'login');
		}

		public function reset_password(){
			$get = Request::get();
			
			$user_token = $get->token;
			$uid = $get->uid;
			
			$m = Model::Factory('user');
			$m->where("id='{$uid}'");
			$user = $m->get();
			
			$token = md5(date('Ymd') . $user->email . $user->login . $user->password . 'NOSSANOSSAASSIMVOCEMEMATA' . '< HA ZUEI');
			if($user_token == $token){
				$this->session->change_password_of_uid = $user->id;
				$this->views->username = $user->login;
				$this->views->display("password-reset-form.phtml");
			} else {
				$this->session->change_password_of_uid = null;
				$this->session->message = 'InvalidToken';
				Request::redirect(HOST . 'login');
			}
		}

		public function confirm_reset_password(){
			if($this->post->new_password == $this->post->new_password_confirm){
				$m = Model::Factory('user');
				$m->password = md5($this->post->new_password);
				$m->where("id='{$this->session->change_password_of_uid}'");
				$m->update();
				$this->session->message = 'PasswordChanged';
				
				$this->session->change_password_of_uid = null;
			} else {
				$this->session->message = 'PasswordNotChanged';
			}
			Request::redirect(HOST . 'login');
		}
		
	}