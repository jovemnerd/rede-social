<?php

	class ProfileAccessController extends Controller {
		
		private $session;
		private $post;
		private $views;
		private $get;
		
		public function init(){
			$this->session = new Session;
		
		if(! in_array(URI, array('/perfil/configuracoes/reativar-conta', 'perfil/configuracoes/reativar-conta'))){
				Phalanx::loadController('LoginController');
				$loginController = new LoginController();
				$loginController->checkStatus();	
			}
			
			$this->post = Request::post();
			$this->files = Request::files();
			$this->get = Request::get();
			
			$this->views = new Views(new Template("default"));
		}
		
		public function CancelAccount(){
			Phalanx::loadClasses('Notification');
			
			$this->session->took_the_first_step_to_cancel = 'yes';
						
			$this->views->data = Model::Factory('notifications n')->innerJoin('user u', 'u.id = n.took_by_user_id')->innerJoin('user_data ud', 'ud.user_id=u.id')->fields('DISTINCT u.login', 'ud.avatar')->where("n.notify_user_id='{$this->session->user->id}' AND n.readed=1")->order('n.date DESC')->limit(9)->all();
			$this->views->display("cancel_account.phtml");
		}
		
		public function SendCancelAccountMail(){
			if($this->session->took_the_first_step_to_cancel != 'yes'){
				Request::redirect(HOST . 'perfil/configuracoes/cancelar-conta');
				return;
			}
			
			$v = new Views();
			$v->link = HOST . 'perfil/configuracoes/cancelar-conta/confirmar?token=' . md5(date('Ymd') . $this->session->user->id . $this->session->user->login . $this->session->user->login . 'Na0NERDNa0CANC3LAAC0NTaCARa');
			$v->username = $this->session->user->login;
			$message = $v->render('mail/cancel_account_request.phtml');
			
			
			Phalanx::loadExtension('phpmailer');
			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			$mail_status = true;
			try {
				$mail->AddReplyTo(MAIL_FROM, MAIL_ALIAS);
				$mail->AddAddress($this->session->user->email, $this->session->user->login);
				$mail->Subject = 'SkyNerd: Pedido de cancelamento de conta';
				$mail->MsgHTML($message);
				$mail->Send();
			} catch(phpmailerException $e) {
				$mail_status = false;
				print_r($mail);
			}
			
			if($mail_status) $this->session->message = 'AccountCancelationRequestReceived';
			else $this->session->message = '';
			
			Request::redirect(HOST . 'perfil/configuracoes');
		}

		public function CancelAccountConfirm(){
			if(empty($this->get->token) or ($this->get->token != md5(date('Ymd') . $this->session->user->id . $this->session->user->login . $this->session->user->login . 'Na0NERDNa0CANC3LAAC0NTaCARa'))){
				Request::redirect(HOST.'perfil/configuracoes');
				exit();
			}
			
			$m = Model::Factory('user');
			$m->account_cancel_date = date('Y-m-d');
			$m->active = 0;
			$m->where("id='{$this->session->user->id}'");
			$m->update();
			
			
			$this->session->destroy();
			
			$this->views->display('cancel_account_confirm.phtml');
		}
		
		public function ReactivateAccountRequest(){
			if($this->session->user->active != 0)
				Request::redirect(HOST.'perfil/configuracoes');
				
			$cancel_date = date_create($this->session->user->account_cancel_date);
			$today = date_create(date('Y-m-d'));
			$days = date_diff($cancel_date, $today);
			
			if($days <= 30){
				$this->views->remaining_days = 30 - $days;
				$this->views->user = $this->session->user->login;
				$this->views->display('reactivate_account.phtml');
			} else {
				$this->TimeLimitExceeded();
			}
				
		}
		
		
		public function ReactivateAccount(){
			if($this->session->user->active != 0)
				Request::redirect(HOST.'perfil/configuracoes');
			
			$cancel_date = date_create($this->session->user->account_cancel_date);
			$today = date_create(date('Y-m-d'));
			$interval = date_diff($cancel_date, $today);
			
			if($interval->days <= 30){
				$m = Model::Factory('user');
				$m->account_cancel_date = null;
				$m->active = 1;
				$m->where("id='{$this->session->user->id}'");
				$m->update();
				
				$this->session->user->active = 1;
				$this->session->user->account_cancel_date = null;
				
				Request::redirect(HOST.'perfil/'.$this->session->user->login);
			} else {
				Request::redirect(HOST.'perfil/configuracaoes/tempo-limite-excedido');
			}
		}
		
		public function TimeLimitExceeded(){
			$t = new Template("sign");
			
			$v = new Views($t);
			$v->display('reactivate_account_timelimit_exceeded.phtml');
		}
		
	}