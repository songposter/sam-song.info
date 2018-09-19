<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Myspace_api
{
    private $_CI = NULL;
    private $_ch = null;
    private $_oauth = null;
    private $_callback = '';
    private $_token = null;
    
    public function __construct()
    {
        $this->_CI =& get_instance();
        $this->_CI->load->library('session');
        $this->_CI->load->config('myspace');
        $this->_CI->load->helper('url');
        $consumer_key = $this->_CI->config->item('myspace_consumer_key');
        $consumer_secret = $this->_CI->config->item('myspace_consumer_secret');
        $this->_apiURL = $this->_CI->config->item('myspace_api_url');
        //$this->_oauth = new OAuth($consumer_key, $consumer_secret);
        $this->_oauth->debug = true;
    }
    
    public function set_callback($url)
    {
        $this->_callback = $url;    
    }
    
    public function logged_in()
    {      
        if (is_array($this->_token) && array_key_exists('ms_token', $this->_token) && array_key_exists('ms_secret', $this->_token))
        {
            return true;
        }
        else
        {         
            $myspaceSession['ms_token'] = $this->_CI->session->userdata('ms_token');
            $myspaceSession['ms_secret'] = $this->_CI->session->userdata('ms_secret');
            
            
            // if all array components are different from bool(false), user is logged in
            if (array_search(false, $myspaceSession, true) === false)
            {
                $this->_token = $myspaceSession;
                               
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    
    /**
     * Execute a call against the Twitter API
     * @param string $request_method
     * @param string $uri
     * @param array $params
     */
    public function call($request_method, $uri, array $params = NULL)
    {
        $uri = $this->_apiURL.$uri;
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
                $this->_oauth->setToken($this->_token['ms_token'], $this->_token['ms_secret']);
            }
            
            if($params === NULL)
            {
                $paramString = '';    
            }
            else
            {
                $paramString = json_encode($params);
                log_message('info', 'JSON:'.$paramString);
            }
            
            $this->_oauth->fetch($uri, $paramString, element(strtoupper($request_method), $supportedMethods));
        }
        catch (OAuthException $oEx)
        {
            // Duplicate entry => Only log for debug
            if ($oEx->getMessage() === "Invalid auth/bad request (got a 403, expected HTTP/1.1 20X or a redirect)")
            {
                log_message('debug', 'duplicate message');
                return false;
            }
            // different oauth/API related error, put into error_log
            else
            {
                log_message('error', $oEx->getMessage());
                log_message('debug', $oEx->debugInfo);
                log_message('debug', $oEx->lastResponse);
                return false;
            }
        }
        log_message('debug', var_export($this->_oauth->debugInfo, true));
        // Everything fine, just decode json into stdObject
        return json_decode($this->_oauth->getLastResponse());       
    }
    
    
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
    
    public function logout()
    {
        $this->_token = '';
        $this->_CI->session->unset_userdata('ms_token');
        $this->_CI->session->unset_userdata('ms_secret');
    }
    
    public function get_token()
    {
        return $this->_token;
    }
    
    public function set_token($token)
    {
        $this->_token = $token;
    }
    
    private function _authorize()
    {
        $requestURL = $this->_CI->config->item('myspace_request_url');
        $requestToken = $this->_oauth->getRequestToken($requestURL, $this->_callback);
       
        if ($requestToken !== false && is_array($requestToken))
        {
            $tempTokenStorage = array
            (
                'requestToken' => $requestToken['oauth_token'],
                'requestSecret' => $requestToken['oauth_token_secret'],
            );
            $this->_CI->session->set_userdata($tempTokenStorage);
            
            $authorizeURL = $this->_CI->config->item('myspace_authorize_url');
            // redirect to authorize
            redirect($authorizeURL.'?oauth_token='.$requestToken['oauth_token'].'&oauth_callback='.rawurldecode($this->_callback).'&myspaceid.permissions=UpdateMoodStatus|AllowActivitiesAutoPublish');
        }
        else
        {
            // TODO: Error handling
        }
    }
    
    private function _exchangeToken($requestToken, $requestVerifier)
    {
        $requestSecret = $this->_CI->session->userdata('requestSecret');
        
        if ($requestToken === false || $requestSecret === false)
        {
            //TODO: Error handling            
        }
        else
        {
            $this->_oauth->setToken($requestToken, $requestSecret);
            $accessURL = $this->_CI->config->item('myspace_access_url');
            
            $accessToken = $this->_oauth->getAccessToken($accessURL);
            
            if ($accessToken !== false && is_array($accessToken))
            {
                $this->_CI->session->unset_userdata(array('requestToken', 'requestSecret'));
                               
                $this->_token = array
                (
                    'ms_token' => $accessToken['oauth_token'],
                    'ms_secret' => $accessToken['oauth_token_secret'],
                );
                
                $this->_CI->session->set_userdata($this->_token);
            }
            else
            {
                //TODO: Error handling
            } 
        }
    }
}