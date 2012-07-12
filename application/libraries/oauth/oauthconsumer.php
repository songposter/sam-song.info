<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class OAuthConsumer
{
	public $key;
	public $secret;

	function __construct($key, $secret='', $callback_url=NULL) {	
		if(is_array($key)) {
			$this->key = $key[0];
			$this->secret = $key[1];
			if (sizeof($key) > 2) {
				$this->callback_url = $key[2];
			}
		}
		
		$this->key = $key;
		$this->secret = $secret;
		$this->callback_url = $callback_url;
	}

	function __toString() {
		return "OAuthConsumer[key=$this->key,secret=$this->secret]";
	}
}

/* End of File: oauthconsumer.php */
/* Location: ./application/libraries/oauthconsumer.php */