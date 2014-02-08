<?php	
	class Youtube{
		
		private $token;
		private $username;
		private $favorites;
		private $uservideos;
		
		public function __construct($token){
			$this->token = $token;
			$this->username = $this->getUserName();
		}
		
		public function get_curl($url, $params){
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, 0);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION  ,1);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $params);		
			curl_setopt($curl, CURLOPT_RETURNTRANSFER  ,1);
			
			
		  	if(USE_HTTP_PROXY == 1){
		  		curl_setopt($curl, CURLOPT_PROXY, HTTP_PROXY_HOST);
				curl_setopt($curl, CURLOPT_PROXYPORT, HTTP_PROXY_PORT);
			}
			
			return curl_exec($curl);
		}
		
		public function __get($k){
			return $this->{$k};
		}
		
		private function getUserName(){
			$url = "http://gdata.youtube.com/feeds/api/users/default?alt=json";
			$json = $this->get_curl($url, array('Authorization:AuthSub token="'.$this->token.'"'));
			$json = json_decode($json);
			return $json->entry->author[0]->name->{'$t'};
		}
		
		public function getFeed(){
			$url = "http://gdata.youtube.com/feeds/api/users/default/subscriptions?v=2&alt=json";
			$json = $this->get_curl($url, array('Authorization:AuthSub token="'.$this->token.'"'));
			return json_decode($json);
		}
		
		public function getVideosFromChannel($user_name, $start_index=1, $max_results=50){
			$url = "http://gdata.youtube.com/feeds/api/users/$user_name/uploads?max-results={$max_results}&alt=json&start-index={$start_index}";
			
			if(USE_HTTP_PROXY == 1){
				$aContext = array('http' => array('proxy' => HTTP_PROXY_HOST.':'.HTTP_PROXY_PORT, 'request_fulluri' => true,));
				$cxContext = stream_context_create($aContext);
				$json = file_get_contents($url, false, $cxContext);
			} else {
				$json = file_get_contents($url);	
			}
			
			if(! $json)
				return false;
			
			$json = json_decode($json);
			if(! $json)
				return false;
			
			if(sizeof($json->feed->entry) >= $max_results){
				$this->getVideosFromChannel($user_name, $start_index+$max_results);
			}
			
			foreach($json->feed->entry as $each){
				$this->uservideos[$user_name][] = end(explode('/', $each->id->{'$t'}));
			}
			
			return $this->uservideos[$user_name];
		}
		
		public function getFavorites($start_index=1, $max_results=50){
			$url = "http://gdata.youtube.com/feeds/api/users/{$this->username}/favorites?alt=json&max-results={$max_results}&start-index={$start_index}";
			$json = $this->get_curl($url, array('Authorization:AuthSub token="'.$this->token.'"'));
			if(! $json) return array();
			
			$json = json_decode($json);
			if(! $json) return array();
			
			if(! isset($json->feed->entry)) return array();
			$feed = $json->feed->entry;
			
			
			if(sizeof($feed)+5 >= $max_results){
				$this->getFavorites($start_index + $max_results);
			}
			
			$pattern = '/http\:\/\/www\.youtube\.com\/watch\?v\=(.*)\&feature\=youtube\_gdata/i';
			foreach($feed as $each){
				$video_info = preg_replace($pattern, "$1", $each->link[0]->href);
				$this->favorites[] = $video_info;
			}
			
			return $this->favorites;
		}
	
		public function checkSubscription($channel_name){
			$url = "http://gdata.youtube.com/feeds/api/users/{$this->username}/subscriptions?max-results=50&alt=json"; 
			$json = $this->get_curl($url, array('Authorization:AuthSub token="'.$this->token.'"'));
			if(! $json) return false;
			
			$json = json_decode($json);
			if(! $json) return false;
			
			if(! isset($json->feed->entry)) return false;
			
			foreach($json->feed->entry as $entry){
				$channel = trim(end(explode(':', $entry->title->{'$t'})));
				if($channel == $channel_name)
					return true;
			}	
			
			return false;
		}	
	}