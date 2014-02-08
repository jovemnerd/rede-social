<?php

	class PostsAttachmentsController extends Controller {
		
		
		private $post;
		private $session;
		
		private $isLoggedIn = false;
		
		public function init(){
			
			$this->post = Request::post(true, REPLACE);
			$this->files = Request::files();
			
			$this->session = new Session();

			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$this->isLoggedIn = $loginController->isLoggedIn(); 

		}
		
			
		public function AttachFiles(){
			if(! $this->isLoggedIn){
				return;
			}
				
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$o = new stdClass();
			
			//Validação simples do tipo do arquivo
			list($w, $h) = getimagesize($this->files->file->tmp_name);
			if(!$w or !$h){
				$o->status = false;
				$o->message = 'Formato de arquivo inválido';
			} else {
				$ext = @end(explode('.', $this->files->file->name));
				$new_filename = @md5(date('YmdHis').$this->files->file->name) . '.' . $ext;
				
				if(! is_dir(POST_IMAGES_UPLOAD_DIR)){
					mkdir(POST_IMAGES_UPLOAD_DIR, 0775, true);
				}
					
				$upload = move_uploaded_file($this->files->file->tmp_name, POST_IMAGES_UPLOAD_DIR.$new_filename);
				if($upload){
					$o->status = true;
					$o->filelink = POST_IMAGES_DIR.$new_filename; 
				} else {
					$o->status = false;
					$o->message = 'Falha ao salvar o arquivo';
				}
			}
			
			header("Content-type:text/html;charset=utf-8");
			die(json_encode($o));
		}
	}