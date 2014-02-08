<?php

	class PostsController extends Controller {
		
		private $get;
		private $post;
		private $views;
		private $session;
		
		private $model;
		private $isLoggedIn = false;
		
		public function init(){
			Phalanx::loadClasses('Notification');
			
			$this->get = Request::get();
			$this->post = Request::post(true, REPLACE);
			$this->session = new Session();
			$template = new Template("default");
			
			Phalanx::loadController('LoginController');
			$loginController = new LoginController();
			$this->isLoggedIn = $loginController->isLoggedIn(); 
			
			if(! $this->isLoggedIn)
				$template->show_login_bar = true;
			
			$this->views = new Views($template);
		}
		
		public function DisplayPost(){
			if($this->get->username == 'wordpressagent') Request::redirect(HOST);
			
			Phalanx::loadClasses('Profile', 'Friendship', 'Posts');
			$profile_data = Profile::get_profile($this->get->username, 0, 0, 0, 0, 0, 0, 0);
			if($profile_data->banned == 1){
				$this->views->display("profile_banned.phtml");
				return;
			}
			
			if($profile_data->active == 0){
				$this->views->display("profile_deactivated.phtml");
				return;
			}
			
			$friendship_status = Friendship::get_status($this->session->user->id, $profile_data->id);
			$this->views->data->friendship_status = $friendship_status;
			$this->views->data = $profile_data;
	
			$p = Posts::from_user($profile_data->id, $this->get->post_id);
			if(! $p){
				$this->views->display("post_unavailable.phtml");
				return;
			}
			
			$p = reset($p);
			$can_be_displayed = true;
			#Verifica se o post é privado.
			if($p->privacy == 1){
				if(! $this->session->user->id){
					$this->views->display("post_unavailable.phtml");
					die();
				}
				
				if($this->session->user->id == $p->user_id){
					$can_be_displayed = true;
				} else {
					$can_be_displayed = Friendship::get_status($this->session->user->id, $p->user_id);
				}
					
			}
			
			if(! $can_be_displayed){
				$this->views->display("post_unavailable.phtml");
				die();
			}
			
			$v = new Views;
			$v->title = $p->title;
			$v->user = $p->user;
			$v->name = $p->name;
			$v->content = $p->content;
			$v->comments = $p->comments;
			$v->comments_array = PostComments::get($this->get->post_id);
			$v->replies = $p->replies;
			$v->post_id = $p->id;
			$v->original_id = $p->original_id;
			$v->avatar = $p->avatar;
			$v->rating = $p->rating;
			$v->promoted = (bool) $p->promoted;
			$v->accept_nsfw = Profile::acceptNSFW($this->session->user->id);
			$v->when = $p->date;
			
			$v->my_rating = $p->my_rating;
			$v->current_user = $this->session->user->login;
			$v->categories = PostCategory::from_post($p->id);
			$v->its_mine = ($profile_data->id == $this->session->user->id) ? true : false;
			$v->is_favorite = $p->is_favorite;
			$v->user_points = $p->user_points;
			
			
			if(! empty($p->original_id)){
				//Se o post for um reblog, então o conteúdo dele deve ser o do reblogado, mostrando as ações
				$originalPost = Posts::from_user(false, $p->original_id);
				$originalPost = reset($originalPost);
				
				$v->content = $originalPost->content;
				$v->title = $originalPost->title;
				$v->reblogged_from = $originalPost->user;
				$v->reblog_avatar = $originalPost->avatar;
				$v->reblog_points = $originalPost->user_points;
				
				$v->original_date = $originalPost->date;
				$v->rating = $originalPost->rating;
				$v->comments = $originalPost->comments;
				$v->replies = $originalPost->replies;
				$v->is_favorite = $originalPost->is_favorite;
				
				$v->categories = PostCategory::from_post($p->original_id);
				$v->comments_array = PostComments::get($p->original_id);
				
				$v->id = $p->id;
				$v->post_id = $originalPost->id;
			}
			
			$content = $v->render("post_body.phtml");
			
			$template = new Template("default");
			$template->og = new stdClass;
			$template->og->title = $v->user . ': ' . $v->title;
			$template->og->description = $p->content;
			$template->og->type = FACEBOOK_APPNAMESPACE.':article_';
			$template->og->img = MEDIA_DIR . 'images/avatar/big/' . $p->avatar;
			
			if(! $this->isLoggedIn)
				$template->show_login_bar = true;
			
			$this->views = new Views($template);
			$this->views->data = $profile_data;
			$this->views->data->friendship_status = $friendship_status;
			$this->views->data->post = $content;
			$this->views->display("single_post_display.phtml");
		}
		
		public function Post(){
			if(! $this->isLoggedIn)
				return;
			
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$content = stripslashes(Request::post(false)->content);
			
			
			#Faz a validação do HTML; HTMLPurifier <3
			require_once(EXTENSIONS_DIR.'HTMLPurifier/HTMLPurifier.includes.php');
			$config = HTMLPurifier_Config::createDefault();
			$config->set('Cache.DefinitionImpl', null);
			$config->set('HTML.TargetBlank', true);
			$config->set('HTML.SafeObject', true);
			$config->set('HTML.SafeEmbed', true);
			$config->set('HTML.SafeIframe', true);
			$config->set('Output.FlashCompat', true);
    		$config->set('HTML.FlashAllowFullScreen', true);
			$config->set('HTML.Allowed', 'i, b, a[href], p, ul, ol, li, span[style], img[src|style], strike, br, hr, blockquote, div, object, param[name|value], embed[src|type|width|height|allowscriptaccess], iframe[src|width|height]');
			$config->set('CSS.AllowedProperties', 'color, float, height, width, margin, border');
			$config->set('AutoFormat.Linkify', true);
			$config->set('URI.MungeResources', true);
			$config->set('URI.SafeIframeRegexp', '#^https://((www.)?youtube.com/embed/|player.vimeo.com/video/)#');

			$HTMLPurifier = new HTMLPurifier($config);
			$content = $HTMLPurifier->purify($content);
			$content = preg_replace('/(?<=^|\s|>)@([A-Za-z0-9_]{1,20})/', '<a class="profile-link" href="'.HOST.'perfil/$1" data-login="{$1}">@$1</a>', $content);
			
			$bbCodeTags = array(
				'spoiler'	=>	array('<div class="spoiler-alert"><span class="alert"><b>SPOILER ALERT!</b> <a href="javascript:void(0);">Clique aqui</a> para exibir.</span><span class="spoiler-content">', '<span></div>')
			);
			$content = BBCode::parse($content, $bbCodeTags);
			
			Phalanx::loadClasses('Profile', 'Badges', 'PostCategory');
			
			$m = Model::Factory('posts');
			$m->user_id = $this->session->user->id;
			if(trim($this->post->title) != "")
				$m->title = $this->post->title;
			$m->content = $content;
			$m->public = $this->post->post_privacy;
			$m->date = date('Y-m-d H:i:s');
			$m->like_count = 0;
			$m->dislike_count = 0;
			$m->comment_count = 0;
			$post_id = $m->insert();
			
			if(isset($this->post->categories)){
				$categories = explode(",", $this->post->categories);
				
				foreach($categories as $category){
					$category = trim($category);
					$categoryID = PostCategory::get($category);
					
					$m = Model::Factory('posts_has_category');
					$m->posts_id = $post_id;
					$m->category_id = $categoryID;
					$m->insert(); 
				}
			}
			
			$view = new Views;
			$view->title = $this->post->title;
			$view->content = $content;
			$view->user = $this->session->user->login;
			$view->avatar = $this->session->user->other_data->avatar;
			$view->when = ' agora';
			$view->post_id = $post_id;
			$view->rating = new stdClass;
			$view->rating->whatever = 0;
			$view->rating->megaboga = 0;
			$view->my_rating = null;
			$view->its_mine = true;
			$view->experience = Profile::experience($this->session->user->id);
			$view->badges = Badges::from_user($this->session->user->id, 4);
			
			preg_match_all('/(?<=|(?<=[.A-Za-z0-9_-]))@([.A-Za-z0-9_-]+[.A-Za-z0-9_-]+)/', $this->post->content, $usernames);
			foreach($usernames[1] as $username){
				$user = Profile::get_user_info($username);
				if($user)
					$n = new Notification(Notification::TAGGED_IN_A_POST, $this->session->user->id, $post_id, $user->id);
			}
			
			if($this->post->post_to_twitter == 1 or $this->post->post_to_facebook == 1){
				$o = new stdClass();
				$o->title = $this->post->title;
				$o->url = HOST . 'perfil/'.$this->session->user->login.'/post/'.$post_id.'-'.mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $this->post->title)));
				$o->uid = $this->session->user->id;
				$o->content = strip_tags($content);
				$o->avatar = MEDIA_DIR.'images/avatar/big/'.$this->session->user->other_data->avatar;
				$o->post_id = $post_id;
				
				try{
					if($this->post->post_to_facebook == 1){
						Phalanx::loadController('FacebookController');
						$facebook = new FacebookController();
						$facebook->callOpenGraphAction('write', array('article_' => $o->url));
					}
				 	
						
					if($this->post->post_to_twitter == 1){
						Phalanx::loadController('TwitterController');
						$twitter = new TwitterController();
						$twitter->sharePost($o);
					}
				} catch (Exception $e){
					
				}
				
			}

			header("Content-type: text/html; charset=utf-8");
			echo $view->render("post_body.phtml");
		}
		
		
		public function Comment(){
			if(! $this->isLoggedIn)
				return;
			
			if(! $this->session->recent_comments)
				$this->session->recent_comments = new stdClass;
			
			if(! $this->session->recent_comments->{"pid".$this->post->post_id})
				$this->session->recent_comments->{"pid".$this->post->post_id} = array();
			
			if(in_array(md5($this->post->comment), $this->session->recent_comments->{"pid".$this->post->post_id})){
				header("Content-type:application/json;charset=utf-8");
				$o = new stdClass;
				$o->status = 0;
				$o->message = "Comentário duplicado";
				die(json_encode($o));
			}
			
			if(trim($this->post->comment) == ''){
				header("Content-type:application/json;charset=utf-8");
				$o = new stdClass;
				$o->status = 0;
				$o->message = "Comentário vazio";
				die(json_encode($o));
			}
			
			$m = Model::Factory('comment', false, false);
			$m->posts_id = $this->post->post_id;
			$m->comment = trim($this->post->comment);
			$m->user_id = $this->session->user->id;
			if($this->post->in_reply_to) $m->in_reply_to = $this->post->in_reply_to;
			$m->date = date('Y-m-d H:i:s');
			
			if(isset($this->post->in_reply_to)){
				$m->in_reply_to = $this->post->in_reply_to;
				$n = new Notification(Notification::REPLYED_COMMENT, $this->session->user->id, $this->post->in_reply_to);
			} else {
				$n = new Notification(Notification::COMMENTED_POST, $this->session->user->id, $this->post->post_id);
			}
			
			$s = $m->insert();
			
			if($s){
				$this->session->recent_comments->{"pid".$this->post->post_id}[] = md5($this->post->comment);
				
				Phalanx::loadClasses('Profile');
				
				preg_match_all('/(?<=|(?<=[.A-Za-z0-9_-]))@([.A-Za-z0-9_-]+[.A-Za-z0-9_-]+)/', $this->post->comment, $usernames);
				foreach($usernames[1] as $username){
					$user = Profile::get_user_info($username);
					if($user)
						$n = new Notification(Notification::TAGGED_IN_A_COMMENT, $this->session->user->id, $this->post->post_id, $user->id);
				}
				
				if($this->post->in_reply_to)	Model::ExecuteQuery("UPDATE posts SET reply_count = reply_count+1 WHERE id='{$this->post->post_id}'");
				else	Model::ExecuteQuery("UPDATE posts SET comment_count = comment_count+1 WHERE id='{$this->post->post_id}'");
			
				Phalanx::loadClasses('Profile', 'Badges');
				
				header("Content-type:application/json;charset=utf-8");
				$o = new stdClass;
				$o->status = 1;
				$o->isReply = ($this->post->in_reply_to) ? true : false;
				$o->id = $s;
				$o->avatar = $this->session->user->other_data->avatar;
				$o->user = $this->session->user->login;
				$o->comment = nl2br(trim(preg_replace('/(?<=|(?<=[.A-Za-z0-9_-]))@([.A-Za-z0-9_-]+[.A-Za-z0-9_-]+)/', '<a class="profile-link" href="'.HOST.'perfil/$1"e>@$1</a>', $this->post->comment)));
				$o->comment_id = $s;
				$o->post_id = $this->post->post_id;
				if($this->post->in_reply_to) $o->in_reply_to = $this->post->in_reply_to;
				$o->experience = Profile::experience($this->session->user->id);
				$o->badges = Badges::from_user($this->session->user->id, 4);
				die(json_encode($o));
			}
		}
		

		public function DeletePost(){
			if(! $this->isLoggedIn)
				return;
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$PostsModel = Model::Factory('posts')->where("id='{$this->post->posts_id}' AND user_id='{$this->session->user->id}'");
			$PostData = $PostsModel->get();
			
			$p = false;
			if($PostData->user_id == $this->session->user->id){
				$PostsModel->status = 0;
				$PostsModel->delete_date = date('Y-m-d H:i:s');
				$PostsModel->deleted_by_uid = $this->session->user->id;
				$p = $PostsModel->update();
				
				#agora apaga os reblogados
				$m = Model::Factory('posts');
				$m->status = 0;
				$m->delete_date = date('Y-m-d H:i:s');
				$m->deleted_by_uid = $this->session->user->id;
				$m->where("original_posts_id='{$this->post->posts_id}'");
				$m->update();
			}
			
			header("Content-type: text/html; charset=utf-8");
			echo ($p) ? 'SUCCESS' : 'FAIL';
		}
	
		public function DeleteComment(){
			if(! $this->isLoggedIn)
				return;
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$cm = Model::Factory('comment', true, 3600);
			$cm->where("id='{$this->post->comment_id}'");
			$comment = $cm->get();
			
			if($comment->in_reply_to != ''){
				Model::ExecuteQuery("UPDATE posts SET comment_count = comment_count-1 WHERE id='{$comment->posts_id}'");	
			} else {
				Model::ExecuteQuery("UPDATE posts SET reply_count = reply_count-1 WHERE id='{$comment->posts_id}'");
			}
			
			
			
			header("Content-type: text/html; charset=utf-8");
			$m = Model::Factory('comment')->where("id='{$this->post->comment_id}' AND user_id='{$this->session->user->id}'");
			$m->status = 0;
			$m->delete_date = date('Y-m-d H:i:s');
			$m->deleted_by_uid = $this->session->user->id;
			$s = $m->update();
			
			if($s){
				$m = Model::Factory('comment')->where("in_reply_to='{$this->post->comment_id}'");
				$m->status = 0;
				$m->delete_date = date('Y-m-d H:i:s');
				$m->deleted_by_uid = $this->session->user->id;
				$m->update();
				echo 'SUCCESS';
			} else {
				echo 'FAIL';
			}
			
		}
			
		public function ParseURL(){
			if(! $this->isLoggedIn)
				return;
			
			#Goodbye, XSS
			if($this->session->accept_token != REQUEST_TOKEN){
				Request::redirect(HOST.'login');
				return;
			}
			
			$url = $this->post->url;
			
			$url_parsed = parse_url($url);
			switch($url_parsed['host']){
				case 'www.youtube.com':
					parse_str($url_parsed['query'], $url_variables);
					if($url_variables['v']){
						$url_data = json_decode(file_get_contents('http://gdata.youtube.com/feeds/api/videos/'.$url_variables['v'].'?v=2&alt=jsonc'));
						$o = new stdClass;
						$o->thumbnail = $url_data->data->thumbnail->sqDefault;
						$o->description = $url_data->data->description;
						$o->title = $url_data->data->title;
						$o->type = "youtube";
					}
					break;
					
				default:
					break;
					
			}		
			die(json_encode($o));
		}
		
		public function PromotedPosts(){
			$m = Model::Factory("posts p", true);
			$m->fields('p.id', 'p.title', 'p.date', 'u.login as username', 'ud.avatar');
			
			$m->innerJoin('user u', "u.id = p.user_id");
			$m->leftJoin("user_data ud", "ud.user_id = u.id");
			
			$m->where("promoted=1 AND public=0");
			$m->order("promoted_on DESC");
			$m->limit(15);
			$data = $m->all();
			
			foreach($data as $k => $v){
				$safe_url = mb_strtolower(preg_replace('/--+/u', '-', preg_replace('/[^\w\-]+/u', '-', $v->title)));
				$data[$k]->relative_time = htmlentities(utf8_decode(Date::RelativeTime($v->date)));
				$data[$k]->date = preg_replace('/^(\d{4})\-(\d{2})\-(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})$/i', "$3/$2/$1", $v->date);
				$data[$k]->avatar = MEDIA_DIR .'images/avatar/square/'. $v->avatar;
				$data[$k]->link = HOST . 'perfil/' . $v->username . '/post/' . $v->id . '-' . $safe_url;
			}
			
			$json['posts'] = $data;
			$json = json_encode($json);
			
			header("Content-type:application/javascript;charset=utf-8");
			die("{$this->get->callback}($json)");
		}
		
		public function GetComments(){
			$post_id = ($this->post->post_id != '') ? $this->post->post_id : false;
			$cache_time = MEMCACHE_SECONDS;
			if(! $post_id){
				Phalanx::loadClasses('Posts');
				$post = Posts::GetWPPost($this->get->wpid);
				$post_id = ($post) ? $post->id : false;
				$cache_time = 180;
			}
			
			$comments = array('');
			header("Content-type: text/html; charset=utf-8");
			if($post_id){
				Phalanx::loadClasses("PostComments");
				$comments = PostComments::get($post_id, $cache_time, $this->get->sort);
				foreach($comments as &$comment){
					$comment->comment = preg_replace('/(?<=|(?<=[.A-Za-z0-9_-]))@([.A-Za-z0-9_-]+[.A-Za-z0-9_-]+)/', '<a class="profile-link" href="'.HOST.'perfil/$1"e>@$1</a>', nl2br($comment->comment));
					foreach($comment->replies as &$reply){
						$reply->comment = preg_replace('/(?<=|(?<=[.A-Za-z0-9_-]))@([.A-Za-z0-9_-]+[.A-Za-z0-9_-]+)/', '<a class="profile-link" href="'.HOST.'perfil/$1"e>@$1</a>', nl2br($reply->comment));
					}
				}	
			}
			
			if($this->get->render){
				Phalanx::loadController('LoginController');
				$loginController = new LoginController();
				
				$v = new Views();
				$v->comments = $post->comment_count;
				$v->replies = $post->reply_count;
				$v->comments_array = $comments;
				$v->post_id = $post_id;
				$v->logged_in = $loginController->isLoggedIn();
				echo $v->render('post_comments.phtml');
			} else {
				die(json_encode($comments));	
			}
		}
	}
