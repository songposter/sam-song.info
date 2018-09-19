<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Implementing the Facebook API as a native CI Library
 *
 * @package        CodeIgniter
 * @author        Benedikt Bauer <support@sam-song.info>
 * @copyright    Copyright (c) 2011, Benedikt Bauer
 * @license        http://dev.sam-song.info/license do what the fuck you want to public license v2
 */
class Facebook_api
{
    /**
     * Internal storage of the access_token
     * @var string
     */
    private $_token;

    /**
     * Internal storage of token expiry
     * @var integer
     */
    private $_expires = 0;

    /**
     * cURL handle
     * @var resource
     */
    private $_ch = NULL;

    /**
     * Callback URL
     * @var string
     */
    private $_callback;

    /**
     * Application ID
     * @var string
     */
    private $_appId;

    /**
     * Application Key
     * @var string
     */
    private $_appKey;

    /**
     * Application Secret
     * @var string
     */
    private $_appSecret;

    /**
     * API Base URL
     * @var string
     */
    private $_apiURL;

    /**
     * Instance of the CodeIgniter Main Class
     * @var CodeIgniter
     */
    private $_CI = NULL;


    /**
     * Load common config options into variables
     * Load session library and URL helper
     */
    public function __construct()
    {
        $this->_CI =& get_instance();
        $this->_CI->load->library('session');
        $this->_CI->load->config('facebook');
        $this->_CI->load->helper('url');

        $this->_appId = $this->_CI->config->item('facebook_app_id');
        $this->_appKey = $this->_CI->config->item('facebook_app_key');
        $this->_appSecret = $this->_CI->config->item('facebook_app_secret');
        $this->_apiURL = $this->_CI->config->item('facebook_api_url');

        $this->_token = $this->_CI->session->userdata('fb_token');
        $this->_expires = $this->_CI->session->userdata('fb_token_expires');
    }

    /**
     * Initialize cURL with some default options
     */
    private function _initCurl()
    {
        $this->_ch = curl_init();
        $config = array
        (
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_USERAGENT      => 'facebook-php-2.0',
            CURLOPT_HTTPHEADER     => array('Expect:'),
        );

        curl_setopt_array($this->_ch, $config);
    }

    /**
     * Get access token from supplied code
     * @param string $code
     * @return bool
     */
    private function _authorize($code, $state, $exchange = false)
    {
        // Get stored state info in order to prevent CSRF
        $expected_state = $this->_CI->session->userdata('facebook_state');

        // If states don't match this is probably an illegit request
        if ($expected_state != $state)
        {
            log_message('error', '0 - Probably CSRF, states don\'t match');

            //start over
            $this->login();
        }

        // set parameters for oauth2.0 request
        $options = array
        (
            'client_id' => $this->_appId,
            'client_secret' => $this->_appSecret,
        );

        $newTokenOptions = array('code' => $code, 'redirect_uri' => $this->_callback);
        $extendTokenOptions = array('grant_type' => 'fb_exchange_token', 'fb_exchange_token' => $this->_token);

        $options = array_merge($options, $exchange ? $extendTokenOptions : $newTokenOptions);

        try
        {
            $token_reply = $this->call('get', 'oauth/access_token', $options);
        }
        catch (FacebookException $e)
        {
            log_message('debug', 'Get Token failed in _authorize');
            log_message('error', $e->getCode().' - '.$e->getMessage());
            // start over
            $this->login();
        }

        $token_array = array();
        parse_str($token_reply, $token_array);

        if (array_key_exists('access_token', $token_array))
        {
            $this->_token = $token_array['access_token'];
        }
        else
        {
            log_message('error', 'no access_token supplied in _authorize response');
            //start over
            $this->login();
        }

        $this->_CI->load->helper('array');
        $this->_expires = element('expires', $token_array, 5184000);

        // If token expires in less than 24 hours, exchange it for a long-lived one
        if ($this->_expires < 3600 && !$exchange)
        {
            $this->_authorize($code, $state, true);
        }

        $this->_expires += time();

        // set session storage to newly acquired token
        $this->_CI->session->set_userdata(array('fb_token' => $this->_token, 'fb_token_expires' => $this->_expires));

        redirect($this->_callback);
    }

    private function _base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Redirect to login URL
     * @return bool
     */
    public function login()
    {
        redirect($this->get_loginURL());
    }

    /**
     * Unset session data and local token variable
     */
    public function logout()
    {
        $this->_token = '';
        $this->_CI->session->unset_userdata('fb_token');
        $this->_CI->session->unset_userdata('fb_token_expires');
        $this->_CI->session->unset_userdata('facebook_state');
    }

    /**
     * Check if token is set
     * @return bool
     */
    public function logged_in()
    {
        if (($code = $this->_CI->input->get_post('code')) !== false)
        {
            $this->_authorize($code, $this->_CI->input->get_post('state'));
        }
        elseif (($error = $this->_CI->input->get_post('error')) !== false)
        {
            throw new FacebookException($this->_CI->input->get_post('error').$this->_CI->input->get_post('error_description'), 401);
        }

        // get token from cookie
        $cookie_token = $this->_CI->session->userdata('fb_token');
        // check if token is empty (both local and cookie)
        $empty_token = empty($this->_token) && empty($cookie_token);
        // get expiry from cookie
        $cookie_expiry = $this->_CI->session->userdata('fb_token_expires');
        // check if token expires at all
        $token_expires = $this->_expires > 0 || $cookie_expiry > 0;

        // Token empty, login required
        if ($empty_token)
        {
            return false;
        }
        elseif ($token_expires)
        {
            // check if token is still valid
            $token_valid = ($this->_expires > time()) || ($cookie_expiry && ($cookie_expiry > time()));

            // return token_valid state
            return $token_valid;
        }
        else
        {
            // token exists and never expires
            return true;
        }
    }

    /**
     * Retrieve Access Token for external storage
     * @return string
     */
    public function get_token()
    {
        return $this->_token;
    }

    /**
     * Set Access Token from external source
     * @param string $token
     */
    public function set_token($token)
    {
        $this->_token = $token;
    }

    /**
     * Retrieve expiry date for external storage
     * @return int
     */
    public function get_expiry()
    {
        return $this->_expires;
    }

    /**
     * Set expiry date from external storage
     * @param int $expiry
     */
    public function set_expiry($expiry)
    {
        $this->_expires = $expiry;
    }

    /**
     * Issue API call via GET/POST (request_method)
     *
     * @param string $request_method
     * @param string $uri
     * @param array $params
     * @return mixed
     */
    public function call($request_method, $uri, array $params = NULL)
    {
        $this->_initCurl();
        // Set the Query URL to the requested option
        $apiURL = $this->_apiURL.$uri.'?access_token='.$this->_token;
        // build a querystring from the parameters array
        $paramString = $params === NULL ? '' : http_build_query($params);

        if ($request_method === 'post')
        {
            curl_setopt($this->_ch, CURLOPT_POST, TRUE);
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $paramString);
        }
        else
        {
            $apiURL .= '&'.$paramString;
        }

    curl_setopt($this->_ch, CURLOPT_CAINFO, dirname(__FILE__).'/fb-ca.crt');
        curl_setopt($this->_ch, CURLOPT_URL, $apiURL);
        $response = curl_exec($this->_ch);


        // cURL Error
        if ($response === false)
        {
            $e = new FacebookException(curl_error($this->_ch), curl_errno($this->_ch));
            curl_close($this->_ch);

            throw $e;
        }

        curl_close($this->_ch);

        if (($decoded_response = json_decode($response)) == NULL)
        {
            return $response;
        }
        else
        {
            if(is_object($decoded_response) && isset($decoded_response->error))
            {
                throw new FacebookException($decoded_response->error);
            }

            return $decoded_response;
        }
    }

    /**
     * Retrieve URL to Login dialog
     * @return string
     */
    public function get_loginURL()
    {
        if (($state = $this->_CI->session->userdata('facebook_state')) === false)
        {
            $state = md5(uniqid(rand(), TRUE));
            $this->_CI->session->set_userdata('facebook_state', $state);
        }

        return 'https://www.facebook.com/dialog/oauth?client_id='.$this->_appId
            .'&redirect_uri='.urlencode($this->_callback)
            .'&scope='.$this->_CI->config->item('facebook_default_scope')
            .'&state='.$state;
    }

    /**
     * Set Callback URL
     * @param string $url
     */
    public function set_callback($url)
    {
        $this->_callback = $url;
    }

    public function parse_signedRequest($signedRequest)
    {
        list($encoded_sig, $payload) = explode('.', $signedRequest, 2);
        // decode the data
        $sig = $this->_base64UrlDecode($encoded_sig);
        $data = json_decode($this->_base64UrlDecode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256')
        {
            log_message('error', 'Unknown algorithm. Expected HMAC-SHA256');
            log_message('debug', 'Bad signature algorithm: '.$data['algorithm']);
            return null;
        }

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $this->_appSecret, $raw = true);
        if ($sig !== $expected_sig)
        {
            log_message('error', 'Bad signature!');
            log_message('debug', 'Expected: '.$expected_sig.' Received: '.$sig);
            return null;
        }
        return $data;
    }
}


/**
 *
 * Custom Exception that parses the Facebook Response object
 * @author Benedikt Bauer <support@sam-song.info>
 *
 */
class FacebookException extends Exception
{
    public function __construct()
    {
        if (func_num_args() == 1 && is_object(func_get_arg(0)))
        {
            $error = func_get_arg(0);
            $code = substr($error->message, 2, 3);
            $message = substr($error->message, 6);
            parent::__construct($message, intval($code));
        }
        elseif (func_num_args() == 2 && is_string(func_get_arg(0)) && is_numeric(func_get_arg(1)))
        {
            parent::__construct(func_get_arg(0), func_get_arg(1), NULL);
        }
    }
}

/* End of File: facebook_api.php */
/* Location: ./application/libraries/facebook_api.php */
