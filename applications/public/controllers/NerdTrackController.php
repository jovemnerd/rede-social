<?php
	class NerdTrackController extends Controller{
		
		private $get,
				$post,
				$session;
				
		public function init(){
			/*
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			*/
			
			$this->session = new Session();
			$this->get = Request::get();
			$this->post = Request::post();
		}
		
		public function login(){
			$uid = $this->session->user->id;	
			$token = md5(date('Ymd') . $this->session->user->id . $this->session->user->login . $this->session->user->login . 'HAHAAHAVOACABARCOMISSOJAJA');
			
			$v = new Views();
			$v->username = $this->session->user->login;
			$v->link = HOST . "meu-perfil/redes-sociais/nerdtrack/callback/?uid={$uid}&token={$token}";
			$message = $v->render('mail/nerdtrack-link-account.phtml');
			
			Phalanx::loadExtension('phpmailer');
			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			
			$mail_status = true;
			try{
				$mail->AddReplyTo(MAIL_FROM, MAIL_ALIAS);
				$mail->AddAddress($this->post->email_address, $this->session->user->login);
				$mail->Subject = 'SkyNerd: Vínculo de conta da Nerdtrack';
				$mail->MsgHTML($message);
				$mail->Send();
			}catch(phpmailerException $e){
				$mail_status = false;
			}
			
			header("Content-type: text/html; charset=utf-8");
			if($mail_status){
				Phalanx::loadClasses('SocialNetwork');
				SocialNetwork::link_account($this->session->user->id, NERDTRACK, $this->post->email_address, false);
				die('SUCCESS');
			} else {
				die('FAIL');
			}	
		}
		
		public function logout(){
			Phalanx::loadClasses('SocialNetwork');
			SocialNetwork::unlink_account($this->session->user->id, NERDTRACK);
			Request::redirect(HOST.'perfil/configuracoes');
		}
		
		public function callback(){
			$m = Model::Factory('user');	
			$m->where("id='{$this->get->uid}'");
			$data = $m->get();
			
			$expected_token = md5(date('Ymd') . $data->id . $data->login . $data->login . 'HAHAAHAVOACABARCOMISSOJAJA');
			if($expected_token == $this->get->token){
				Phalanx::loadClasses('SocialNetwork');
				SocialNetwork::activate_account($this->session->user->id, NERDTRACK);
				Request::redirect(HOST.'perfil/configuracoes');
			} else {
				Request::redirect(HOST.'perfil/configuracoes');	
			}
		}
		
		public function get(){
			Phalanx::loadClasses('Nerdtrack');
			
			$data = false;
			if($this->get->wpid)
				$data = Nerdtrack::get($this->get->wpid, $this->session->user->id);
			
			header("Content-type:text/plain;charset=utf-8");
			$data = json_encode($data);
			if(isset($this->get->callback))
				die("{$this->get->callback}({$data});");
			else
				die($data);
		}
		
		public function post(){
			Phalanx::loadClasses('Nerdtrack');
			
			$data = new stdClass;
			$data->type = $this->get->type;
			switch($this->get->type){
				case 'quote':
					$o = new stdClass;
					$o->when = $this->get->when;
					$o->phrase = $this->get->phrase;
					$o->who_said = $this->get->who_said;
					$data->status = Nerdtrack::addQuote($this->get->wpid, $this->session->user->id, $o);
					break;
					
				case 'song':
					$o = new stdClass;
					$o->name = $this->get->name;
					$o->type = $this->get->type;
					$o->when = $this->get->when;
					$o->youtube = $this->get->youtube;
					$data->status = Nerdtrack::addSong($this->get->wpid, $this->session->user->id, $o);
					break;
			}
			
			header("Content-type:application/json;charset=utf-8");
			die(json_encode($data));
		}
		
		public function rate(){
			$o = new stdClass();
			$nerdtrackID = $this->get->nerdtrackID;
			
			switch($this->get->action){
				case "rate":
					$m = Model::Factory('rating');
					$m->nerdtrack_id = $nerdtrackID;
					$m->user_id = $this->session->user->id;
					$m->rating = $this->get->rating;
					$m->date = date('Y-m-d H:i:s');
					$o->status = $m->insert();
					if($o->status){
						$field = ($this->get->rating == '1') ? 'like_count' : 'dislike_count';
						Model::ExecuteQuery("UPDATE nerdtrack SET {$field}={$field}+1 WHERE id='{$nerdtrackID}' AND {$field}>0");
					}
					break;

				case "unrate":
					$m = Model::Factory('rating');
					$m->where("nerdtrack_id='{$nerdtrackID}' AND user_id='{$this->session->user->id}'");
					$o->status = (Boolean) $m->delete();
					if($o->status){
						$field = ($this->get->currentRate == '1') ? 'like_count' : 'dislike_count';
						$where = '';
						if($this->get->currentRate <> '1') $where=' AND {$field} > 0';
						Model::ExecuteQuery("UPDATE nerdtrack SET {$field}={$field}-1 WHERE id='{$nerdtrackID}' {$where}");
					}
					break;

				case "change_rate":
					$m = Model::Factory('rating');
					$m->rating = ($this->get->currentRate == 1) ? '-1' : '1';
					$m->date = date('Y-m-d H:i:s');
					$m->where("nerdtrack_id='{$nerdtrackID}' AND user_id='{$this->session->user->id}'");
					$o->status = (Boolean) $m->update();
					if($o->status){
						if($this->get->currentRate == '1'){
							Model::ExecuteQuery("UPDATE nerdtrack SET like_count=like_count-1 WHERE id='{$nerdtrackID}' AND like_count>0");
							Model::ExecuteQuery("UPDATE nerdtrack SET dislike_count=dislike_count+1 WHERE id='{$nerdtrackID}'");
						}elseif($this->get->currentRate == '-1'){
							Model::ExecuteQuery("UPDATE nerdtrack SET like_count=like_count+1 WHERE id='{$nerdtrackID}'");
							Model::ExecuteQuery("UPDATE nerdtrack SET dislike_count=dislike_count-1 WHERE id='{$nerdtrackID}' AND dislike_count>0");
						}
					}
					break;
			}
			
			header("Content-type:text/plain;charset=utf-8");
			$data = json_encode($o);
			if(isset($this->get->callback))
				die("{$this->get->callback}({$data});");
			else
				die($data);
		}
	}