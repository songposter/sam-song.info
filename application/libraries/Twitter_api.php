<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Twitter_api
{
    private $_CI = NULL;
    private $_apiURL = NULL;
    private $_oauth = NULL;
    private $_callback = '';
    private $_token = NULL;

    /**
     * Load common config options into variables
     * Load session library and URL helper
     */
    public function __construct($api = '')
    {
        $this->_CI =& get_instance();
        $this->_CI->load->library('session');
        $this->_CI->load->config('twitter');
        $this->_CI->load->helper('url');

        $consumer_key = $this->_CI->config->item('twitter_consumer_key');
        $consumer_secret = $this->_CI->config->item('twitter_consumer_secret');
        $this->_apiURL = $this->_CI->config->item('twitter_'.$api.'api_url');

        if(class_exists('OAuth')) {
            $this->_oauth = new OAuth($consumer_key, $consumer_secret);
        } else {
            $this->_CI->load->library('oauth_api', array($consumer_key, $consumer_secret));
            $this->_oauth = $this->_CI->oauth_api;
            define('OAUTH_HTTP_METHOD_GET',        'GET');
            define('OAUTH_HTTP_METHOD_POST',    'POST');
            define('OAUTH_HTTP_METHOD_PUT',        'PUT');
            define('OAUTH_HTTP_METHOD_DELETE',    'DELETE');
            define('OAUTH_HTTP_METHOD_HEAD',    'HEAD');
        }

        $this->_oauth->debug = true;
    }

    /**
     * Set callback URL
     * @param string $url
     */
    public function set_callback($url)
    {
        $this->_callback = $url;
    }

    /**
     * Check if token is set either in cookie or in classvariable
     * @return bool
     */
    public function logged_in()
    {
        if (is_array($this->_token) && array_key_exists('tw_token', $this->_token) && array_key_exists('tw_secret', $this->_token))
        {
            return true;
        }
        else
        {
            $twitterSession['tw_token'] = $this->_CI->session->userdata('tw_token');
            $twitterSession['tw_secret'] = $this->_CI->session->userdata('tw_secret');
            $twitterSession['tw_userid'] = $this->_CI->session->userdata('tw_userid');
            $twitterSession['tw_screenname'] = $this->_CI->session->userdata('tw_screenname');

            // if all array components are different from bool(false), user is logged in
            if (array_search(false, $twitterSession, true) === false)
            {
                $this->_token = $twitterSession;
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    /**
     * Login and authorize app
     * Get Request Token,
     * Send user to authorization,
     * Get Access Token
     */
    public function login()
    {
        $token = $this->_CI->input->get('oauth_token');
        $verifier = $this->_CI->input->get('oauth_verifier');

        if ($token === false || $verifier === false)
        {
            $this->_authorize();
        }
        else
        {
            $this->_exchangeToken($token, $verifier);
        }
    }

    /**
     * Logout
     * Remove classvariable and session stored token
     */
    public function logout()
    {
        $this->_token = '';
        $this->_CI->session->unset_userdata('tw_token');
        $this->_CI->session->unset_userdata('tw_secret');
        $this->_CI->session->unset_userdata('tw_userid');
        $this->_CI->session->unset_userdata('tw_screenname');
    }

    /**
     * Get Token
     * @return array the access token and username
     */
    public function get_token()
    {
        return $this->_token;
    }

    /**
     * Set token from external storage
     * @param array $token
     */
    public function set_token($token)
    {
        $this->_token = $token;
    }

    /**
     * Execute a call against the Twitter API
     * @param string $request_method
     * @param string $uri
     * @param array $params
     */
    public function call($request_method, $uri, array $params = NULL)
    {
        $uri = $this->_apiURL.'1.1/'.$uri;

        $supportedMethods = array
        (
            'GET'    => OAUTH_HTTP_METHOD_GET,
            'POST'   => OAUTH_HTTP_METHOD_POST,
            'PUT'    => OAUTH_HTTP_METHOD_PUT,
            'DELETE' => OAUTH_HTTP_METHOD_DELETE,
            'HEAD'   => OAUTH_HTTP_METHOD_HEAD,
        );

        try
        {
            if (is_array($this->_token))
            {
                $this->_oauth->setToken($this->_token['tw_token'], $this->_token['tw_secret']);
            }

            $this->_oauth->fetch($uri, $params, element(strtoupper($request_method), $supportedMethods));
        }
        catch (Exception $oEx)
        {
            // Duplicate entry => Only log for debug
            if ($oEx->getMessage() === "Invalid auth/bad request (got a 403, expected HTTP/1.1 20X or a redirect)")
            {
                log_message('error', 'Twitter-API: duplicate message for user '.$this->_token['tw_screenname']);
                return false;
            }
            // different oauth/API related debug, put into debug_log
            else
            {
                log_message('error', 'Twitter-API: User: '.$this->_token['tw_screenname']."\t".$oEx->getMessage());
                log_message('error', 'Twitter-API: '.$oEx->debugInfo);
                log_message('error', 'Twitter-API: '.$oEx->lastResponse);
                return false;
            }
        }
        // Everything fine, just decode json into stdObject
        $response = json_decode($this->_oauth->getLastResponse());

        if ($response === null)
        {
            log_message('error', 'Twitter-API: Response was NULL');
            return false;
        }
        elseif (property_exists($response, 'user'))
        {
            return $response;
        }
        elseif (property_exists($response, 'debugs'))
        {
            log_message('error', 'Twitter-API: User: '.$this->_token['tw_screenname']." : ".$response->debugs[0]->message);
            log_message('error', 'Twitter-API: '.print_r($this->_oauth->getLastResponseInfo(), true));
            return false;
        }
    }

    /**
     * Authorize
     * Get Request Token and send user to authorization
     */
    private function _authorize()
    {
        $requestURL = $this->_apiURL.$this->_CI->config->item('twitter_request_url');
        try
        {
            $requestToken = $this->_oauth->getRequestToken($requestURL, $this->_callback);
        }
        catch (Exception $e)
        {
            log_message('debug', 'Twitter-API: debug getting Request Token: '.$e->getMessage);
            log_message('debug', 'Twitter-API: '.$e->debugInfo);
            log_message('debug', 'Twitter-API: '.$e->lastResponse);
        }



        if ($requestToken !== false && is_array($requestToken))
        {
            $tempTokenStorage = array
            (
                'requestToken' => $requestToken['oauth_token'],
                'requestSecret' => $requestToken['oauth_token_secret'],
            );
            $this->_CI->session->set_userdata($tempTokenStorage);

            $authorizeURL = $this->_apiURL.$this->_CI->config->item('twitter_authorize_url');

            // redirect to authorize
            redirect($authorizeURL.'?oauth_token='.$requestToken['oauth_token']);
        }
        else
        {
            log_message('debug', 'Twitter-API: Got no request token from endpoint');
        }
    }

    /**
     * Exchange temporary request token for permanent access token
     * @param string $requestToken
     * @param string $requestVerifier
     */
    private function _exchangeToken($requestToken, $requestVerifier)
    {
        $requestToken = ($this->_CI->session->userdata('requestToken') !== $requestToken) ? false : $requestToken;
        $requestSecret = $this->_CI->session->userdata('requestSecret');
        
        if ($requestToken === false || $requestSecret === false)
        {
            log_message('debug', 'Twitter-API: Request token/secret missing');
        }
        else
        {
            $this->_oauth->setToken($requestToken, $requestSecret);
            $accessURL = $this->_apiURL.$this->_CI->config->item('twitter_access_url');
            try
            {
                $accessToken = $this->_oauth->getAccessToken($accessURL, '', $requestVerifier);
            }
            catch (Exception $e)
            {
                var_dump($e);
                log_message('debug', 'Twitter-API: debug in access token exchange: '.$e->getMessage());
                log_message('debug', 'Twitter-API: '.$e->debugInfo);
                log_message('debug', 'Twitter-API: '.$e->lastResponse);
                die();
            }
    
            if ($accessToken !== false && is_array($accessToken))
            {
                $this->_CI->session->unset_userdata(array('requestToken', 'requestSecret'));

                $twitterSession = array
                (
                    'tw_token' => $accessToken['oauth_token'],
                    'tw_secret' => $accessToken['oauth_token_secret'],
                    'tw_userid' => $accessToken['user_id'],
                    'tw_screenname' => $accessToken['screen_name'],
                );
                $this->_CI->session->set_userdata($twitterSession);

                $this->_token = $twitterSession;
            }
            else
            {
                log_message('debug', 'Twitter-API: Missing access token response');
            }
        }
    }
}
