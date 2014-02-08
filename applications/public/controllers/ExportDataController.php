<?php

	class ExportDataController extends Controller {
		
		private $session,
				$get,
				$post;
		
		public function init(){
			$this->get = Request::get();
			$this->post = Request::post();
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$loginController->checkStatus();
			
			$this->session = new Session();
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
		}
		
		public function Export(){
			Phalanx::loadClasses('Profile', 'Badges');
			
			$profile = Profile::get_profile($this->session->user->login, 0, 0, 0, 0, 1, 1, 1);
			$profile->badges = Badges::from_user($this->sessio->user->id, false);
			
			$t = new Template("export");
			$t->show_login_bar = true;
			
			$userPosts = Posts::exportFromUser($this->session->user->id);
			
			$postsImages = array();
			$avatarImages = array();
			
			$posts = array();
			
			Phalanx::loadExtension('simple_html_dom');
			foreach($userPosts as $key => $each){
				
				$html = str_get_html($each->content);
				/*
				 * Em alguns casos o objeto não está sendo criado, gerando um fatal error.
				 * Conteúdo vazio? Estranho, ainda não sei o que está rolando.
				 * Isso aqui resolve.
				 * */
				if(is_object($html)){
					$images = $html->find('img');
					foreach($images as &$image){
						if(stripos($image, HOST)){
							$postsImages[] = basename($image->src);
							$image->src = "./images/posts/" . basename($image->src);	
						}
					}	
					$each->content = $html;	
				}
				
				
				$avatarImages[] = $each->avatar;
				
				$v = new Views;
				$v->accept_nsfw = Profile::acceptNSFW($this->session->user->id);
				$v->current_user = $this->session->user->login;
				$v->user = $each->user;
				$v->name = $each->name;
				$v->when = $each->date;
				$v->title = $each->title;
				$v->content = $each->content;
				$v->comments = $each->comments;
				$v->comments_array = $each->comments_array;
				$v->replies = $each->replies;
				$v->post_id = $each->id;
				$v->original_id = $each->original_id;
				$v->is_reblogged = $each->is_reblogged;
				$v->avatar = $each->avatar;
				$v->rating = $each->rating;
				$v->my_rating = $each->my_rating;
				$v->categories = $each->categories;
				$v->its_mine = ($profile_data->id == $this->session->user->id) ? true : false;
				$v->is_favorite = $each->is_favorite;
				$v->user_points = $each->user_points;
				
				foreach($each->comments_array as $eachComment){
					$avatarImages[] = $eachComment->user->avatar;
					foreach($eachComment->replies as $eachReply){
						$avatarImages[] = $eachReply->user->avatar;
					}
				}
				
				
				if(! empty($each->original_id)){
					//Se o post for um reblog, então o conteúdo dele deve ser o do reblogado, mostrando as ações
					$originalPost = Posts::from_user(false, $v->original_id);
					$originalPost = reset($originalPost);
					
					$v->content = $originalPost->content;
					$v->title = $originalPost->title;
					$v->reblogged_from = $originalPost->user;
					$v->reblog_avatar = $originalPost->avatar;
					$v->reblog_points = $originalPost->user_points;
					
					$v->original_date = $originalPost->date;
					$v->comments = $originalPost->comments;
					$v->replies = $originalPost->replies;
					$v->is_favorite = $originalPost->is_favorite;
					$v->categories = $originalPost->categories;
					$v->rating = $originalPost->rating;
					$v->id = $v->post_id;
					$v->post_id = $originalPost->id;
				}
				
				$content = $v->render("export/post_body.phtml");
				$posts[] = $content;
			}
						
			$v = new Views($t);
			$v->data = $profile;
			$v->data->timeline = $posts;
			
			ob_start();
				$v->display("export/profile.phtml");
				$profile_html_data = ob_get_contents();
			ob_end_clean();
			
			if(! is_dir(TMP_DIR.DIRECTORY_SEPARATOR.'export'))
				mkdir(TMP_DIR.DIRECTORY_SEPARATOR.'export', 0755, true);
			
			$dirname = TMP_DIR.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.$this->session->user->login.DIRECTORY_SEPARATOR;
			if(! is_dir($dirname))
				mkdir($dirname, 0755, true);
			
			$filename = "perfil-{$this->session->user->login}.html";
			file_put_contents($dirname.$filename, $profile_html_data);
			
			$zip = new ZipArchive();
			if($zip->open("{$dirname}data.zip", ZipArchive::CREATE) === TRUE){
				$zip->addEmptyDir('css');
				foreach(glob(TEMPLATE_DIR . '/export/css/*') as $file)
					$zip->addFile($file, "/css/" . basename($file));
				
				$zip->addEmptyDir('js');
				foreach(glob(TEMPLATE_DIR . '/export/js/*') as $file)
					$zip->addFile($file, "/js/" . basename($file));
				
				
				$zip->addEmptyDir('fonts');
				$zip->addEmptyDir('fonts/Engschrift');
				foreach(glob(TEMPLATE_DIR . '/export/fonts/Engschrift/*') as $file)
					$zip->addFile($file, "/fonts/Engschrift/" . basename($file));
				
				
				$zip->addEmptyDir('images');
				foreach(glob(TEMPLATE_DIR . '/export/images/*.*') as $file)
					$zip->addFile($file, "/images/" . basename($file));
				
				$zip->addEmptyDir('images/socialnetworks');
				foreach(glob(TEMPLATE_DIR . '/export/images/socialnetworks/*') as $file)
					$zip->addFile($file, "/images/socialnetworks/" . basename($file));
				
				$zip->addEmptyDir('images/images');
				foreach(glob(TEMPLATE_DIR . '/export/images/images/*') as $file)
					$zip->addFile($file, "/images/images/" . basename($file));
				
				
				$zip->addEmptyDir('images/avatar');
				$zip->addEmptyDir('images/avatar/big');
				$zip->addEmptyDir('images/avatar/small');
				$zip->addEmptyDir('images/avatar/square');
				foreach($avatarImages as $avatar){
					$zip->addFile(AVATAR_UPLOAD_DIR."/big/{$avatar}", "/images/avatar/big/{$avatar}");
					$zip->addFile(AVATAR_UPLOAD_DIR."/small/{$avatar}", "/images/avatar/small/{$avatar}");
					$zip->addFile(AVATAR_UPLOAD_DIR."/square/{$avatar}", "/images/avatar/square/{$avatar}");
				}
				
				$zip->addEmptyDir('images/posts');
				foreach($postsImages as $image){
					$zip->addFile(POST_IMAGES_UPLOAD_DIR."/{$image}", "/images/posts/{$image}");
				}
				
				$zip->addEmptyDir('images/badges');
				foreach(glob(ROOT.PROJECT_DIR.'/media/images/badges/*') as $file){
					$zip->addFile($file, "/images/badges/" . basename($file));
				}
				
				
				$zip->addFile("{$dirname}{$filename}", "/{$filename}");
			}
			$zip->close();
			
			
			header("Content-disposition: attachment; filename={$this->session->user->login}.zip");
			header("Content-type: application/zip");
			readfile("{$dirname}data.zip");
		
			$t = new Template("export", "thankyou.phtml");
			$v = new Views($t);
			$v->display("");
			
			$c = new Cookies();
			$c->setExpire(strtotime("+15 days"));
			$c->data_exported = 1;
			
		}
		
		 
	} 