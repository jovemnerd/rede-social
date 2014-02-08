<?php

	class ProfileAvatarController extends Controller {
		
		private $get;
		private $post;
		private $files;
		private $session;
		
		public function init(){
			$this->get = Request::get();
			$this->post = Request::post();
			$this->files = Request::files();
			$this->session = new Session();
			
			Phalanx::loadController("LoginController");
			$LoginController = new LoginController();
			if(! $LoginController->isLoggedIn()){
				$this->isLoggedIn = $template->show_login_bar = true;
			}
		}
		
		public function change_avatar(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			//Guardo alguns dados da imagem. Adicionalmente, dá pra descobrir se o usuário está upando uma imagem mesmo ;)
			list($w, $h) = getimagesize($this->files->new_avatar->tmp_name);
			if(!$w or !$h){
				header("Content-type:text/html;charset=utf-8");
				
				$o = new stdClass;
				$o->status = false;
				$o->message = 'INVALID FILETYPE';
				
				die('('.json_encode($o).');');
			}
			
			//Faz o upload do arquivo
			$fileext = @strtolower(end(explode('.', $this->files->new_avatar->name)));
			$filename = md5(date('YmdHis') . $this->session->user->id) .'.'. $fileext;
			
			if(!is_dir(AVATAR_UPLOAD_DIR))
				mkdir(AVATAR_UPLOAD_DIR, 0775, true);
			
			move_uploaded_file($this->files->new_avatar->tmp_name, AVATAR_UPLOAD_DIR.$filename);
			
			//Se a imagem for menor que 800/x, já devolve o JSON deste objeto
			$o = new stdClass;
			$o->height = $h;
			$o->width = $w;
			$o->src = AVATAR_DIR.$filename;
			
			//Senao, faz o resize pra chegar à 800x600 no máximo, proporcionalmente.
			if($w > 800 or $h > 600){
				$xscale = $w / 800; 
			    $yscale = $h / 600; 
			
			    if ($yscale > $xscale){ 
			        $new_width = round($w * (1/$yscale)); 
			        $new_height = round($h * (1/$yscale)); 
			    } 
			    else { 
			        $new_width = round($w * (1/$xscale)); 
			        $new_height = round($h * (1/$xscale)); 
			    } 
			    
			    
			    $imageResized = imagecreatetruecolor($new_width, $new_height);
				//Verifca qual é a extensão do arquivo p/ inicializar a GD da melhor forma 
			    switch($fileext){
					case 'jpg':
					case 'jpeg':
						$imageTmp = imagecreatefromjpeg(AVATAR_UPLOAD_DIR.$filename);
						break;
						
					case 'png':
						$imageTmp = imagecreatefrompng(AVATAR_UPLOAD_DIR.$filename);
						break;
			    }
			    
			     
			    imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $w, $h); 
				
				//Apaga o arquivo original (muito grande)
				unlink(AVATAR_UPLOAD_DIR.$filename);
				
				//Cria o novo
				$filename = md5(date('YmdHis') . $this->session->user->id . $filename) . '.jpg';
				imagejpeg($imageResized, AVATAR_UPLOAD_DIR.$filename);
				
				//Redefine o objeto
				$o = new stdClass;
				$o->height = $new_height;
				$o->width = $new_width;
				$o->src = HOST.'/media/images/avatar/'.$filename;
				$o->status = true;
			}
			
			$this->session->new_avatar_tmp_name = AVATAR_UPLOAD_DIR.$filename;
			//Gambiarra: MELHORAR ISSO
			if(strrpos($_SERVER['HTTP_REFERER'], 'fallback') === false){
				header("Content-type:text/html;charset=utf-8");
				die('('.json_encode($o).');');
			} else {
				$v = new Views;
				$v->json = '('.json_encode($o).');';
				echo $v->render('iframe_avatar_upload_finish.phtml');
			}
		}

		public function confirm_avatar_change(){
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$filename = $this->session->new_avatar_tmp_name;
			$fileext = @strtolower(end(explode('.', $filename)));
			
			$resource = imagecreatetruecolor($this->post->w, $this->post->h);
		    switch($fileext){
				case 'jpg':
				case 'jpeg':
					$imageTmp = imagecreatefromjpeg($filename);
					break;
					
				case 'png':
					$imageTmp = imagecreatefrompng($filename);
					break;
		    }
		    
			$x = $this->post->x;
			$y = $this->post->y;
			$w = $this->post->w;
			$h = $this->post->h;
			
			imagecopyresampled($resource, $imageTmp, 0, 0, $x, $y, $w, $h, $w, $h);
			
			//Cria o novo, com os tamanhos que o usuário enviou
			$filename = md5(date('YmdHis') . $this->session->user->id . $filename) . '.jpg';
			
			
			imagejpeg($resource, AVATAR_UPLOAD_DIR.$filename);
			
			
			//Agora eu crio as minhas imagens
			$xscale = $w / 278; 
			$yscale = $h / 466; 
			if ($yscale > $xscale){ 
				$new_width = round($w * (1/$yscale)); 
				$new_height = round($h * (1/$yscale)); 
			} else { 
				$new_width = round($w * (1/$xscale)); 
				$new_height = round($h * (1/$xscale)); 
			}
			    
			$imageTmp = imagecreatefromjpeg(AVATAR_UPLOAD_DIR.$filename);
			$imageResized = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $w, $h); 
			imagejpeg($imageResized, AVATAR_UPLOAD_DIR.'big/'.$filename, 100);
			
			
			$xscale = $w / 113; 
			$yscale = $h / 192; 
			if ($yscale > $xscale){ 
				$new_width = round($w * (1/$yscale)); 
				$new_height = round($h * (1/$yscale)); 
			} else { 
				$new_width = round($w * (1/$xscale)); 
				$new_height = round($h * (1/$xscale)); 
			}
			$imageTmp = imagecreatefromjpeg(AVATAR_UPLOAD_DIR.$filename);
			$imageResized = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $w, $h); 
			imagejpeg($imageResized, AVATAR_UPLOAD_DIR.'small/'.$filename, 100);
			
			
			$xscale = $w / 45; 
			$yscale = $h / 75; 
			if ($yscale > $xscale){ 
				$new_width = round($w * (1/$yscale)); 
				$new_height = round($h * (1/$yscale)); 
			} else { 
				$new_width = round($w * (1/$xscale)); 
				$new_height = round($h * (1/$xscale)); 
			}
			$imageTmp = imagecreatefromjpeg(AVATAR_UPLOAD_DIR.$filename);
			$imageResized = imagecreatetruecolor($new_width, $new_height);
			imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $w, $h); 
			imagejpeg($imageResized, AVATAR_UPLOAD_DIR.'square/'.$filename, 100);
			
			imagedestroy($imageResized);
			
			@unlink(AVATAR_UPLOAD_DIR.$filename);
			@unlink($this->session->new_avatar_tmp_name);
			unset($this->session->new_avatar_tmp_name);
			
			
			$m = Model::Factory('user_data');
			$m->avatar = $filename;
			$m->where("user_id='{$this->session->user->id}'");
			if(! $m->update()){
				$m->user_id = $this->session->user->id;
				$m->insert();
			}
			
		#	Phalanx::loadClasses('Notification');
		#	$n = new Notification(Notification::CHANGED_AVATAR, $this->session->user->id, null);
			
			$this->session->user->other_data->avatar = $filename;
			
			Phalanx::loadExtension('S3');
			$s3 = new S3(S3_ACCESS_KEY_ID, S3_SECRET_ACCESS_KEY);
			$s3->putObjectFile(AVATAR_UPLOAD_DIR."big/{$filename}", S3_BUCKET_NAME, "media/images/avatar/big/{$filename}", S3::ACL_PUBLIC_READ);
			$s3->putObjectFile(AVATAR_UPLOAD_DIR."small/{$filename}", S3_BUCKET_NAME, "media/images/avatar/small/{$filename}", S3::ACL_PUBLIC_READ);
			$s3->putObjectFile(AVATAR_UPLOAD_DIR."square/{$filename}", S3_BUCKET_NAME, "media/images/avatar/square/{$filename}", S3::ACL_PUBLIC_READ);
			
			header("Content-type: text/html; charset=utf-8");
			die($filename);
		}
		
		public function avatar_upload_frame(){
			$v = new Views;
			echo $v->render("iframe_avatar_upload_fallback.phtml");
		}
		
	}
