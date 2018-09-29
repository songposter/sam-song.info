<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    $config['twitter_consumer_key'] = $_ENV['twitter_api_key'];;
    $config['twitter_consumer_secret'] = $_ENV['twitter_api_secret'];
    $config['twitter_api_url'] = 'https://api.twitter.com/1.1/';
    $config['twitter_streamapi_url'] = 'https://stream.twitter.com/';
    $config['twitter_request_url'] = 'https://api.twitter.com/oauth/request_token';
    $config['twitter_authorize_url'] = 'https://api.twitter.com/oauth/authorize';
    $config['twitter_access_url'] = 'https://api.twitter.com/oauth/access_token';


/* End of file twitter.php */
/* Location: ./application/config/twitter.php */
