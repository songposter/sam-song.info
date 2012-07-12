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
        
        $this->_CI->load->library('OAuthConsumer', array($consumer_key, $consumer_secret));
        
        echo $this->OAuthConsumer;
        
        $this->_oauth = new OAuth($consumer_key, $consumer_secret);
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
                $this->_oauth->setToken($this->_token['tw_token'], $this->_token['tw_secret']);
            }

            $this->_oauth->fetch($uri, $params, element(strtoupper($request_method), $supportedMethods));
        }
        catch (OAuthException $oEx)
        {
            // Duplicate entry => Only log for debug
            if ($oEx->getMessage() === "Invalid auth/bad request (got a 403, expected HTTP/1.1 20X or a redirect)")
            {
                log_message('debug', 'duplicate message for user '.$this->_token['tw_screenname']);
                return false;
            }
            // different oauth/API related error, put into error_log
            else
            {
                log_message('error', 'User: '.$this->_token['tw_screenname']."\n".$oEx->getMessage());
                log_message('debug', $oEx->debugInfo);
                log_message('debug', $oEx->lastResponse);
                return false;
            }
        }
        // Everything fine, just decode json into stdObject
        return json_decode($this->_oauth->getLastResponse());
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
        catch (OAuthException $e)
        {
            log_message('error', 'Error getting Request Token: '.$e->getMessage);
            log_message('debug', $e->debugInfo);
            log_message('debug', $e->lastResponse);
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
            log_message('error', 'Got no request token from endpoint');
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
            log_message('error', 'Request token/secret missing');
        }
        else
        {
            $this->_oauth->setToken($requestToken, $requestSecret);
            $accessURL = $this->_apiURL.$this->_CI->config->item('twitter_access_url');
            try
            {
                $accessToken = $this->_oauth->getAccessToken($accessURL, '', $requestVerifier);
            }
            catch (OAuthException $e)
            {
                log_message('error', 'Error in access token exchange: '.$e->getMessage());
                log_message('debug', $e->debugInfo);
                log_message('debug', $e->lastResponse);
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
                log_message('error', 'Missing access token response');
            }
        }
    }
    
    /*
     Function: dl_local()
    Reference: http://us2.php.net/manual/en/function.dl.php
    Author: Brendon Crawford <endofyourself |AT| yahoo>
    Usage: dl_local( "mylib.so" );
    Returns: Extension Name (NOT the extension filename however)
    NOTE:
    This function can be used when you need to load a PHP extension (module,shared object,etc..),
    but you do not have sufficient privelages to place the extension in the proper directory where it can be loaded. This function
    will load the extension from the CURRENT WORKING DIRECTORY only.
    If you need to see which functions are available within a certain extension,
    use "get_extension_funcs()". Documentation for this can be found at
    "http://us2.php.net/manual/en/function.get-extension-funcs.php".
    */
    
    private function _dl_local( $extensionFile ) 
    {
    	//make sure that we are ABLE to load libraries
    	if (!(bool)ini_get( "enable_dl" ) || (bool)ini_get( "safe_mode" )) 
    	{
    		die("dh_local(): Loading extensions is not permitted.\n");
    	}
    	
    	chdir(dirname(__FILE__));
    	
    	//check to make sure the file exists
    	if (!file_exists( $extensionFile ))
    	{
    		die("dl_local(): File '$extensionFile' does not exist.\n");
    	}
    	 
    	//check the file permissions
    	if (!is_readable( $extensionFile )) 
    	{
    		die("dl_local(): File '$extensionFile' is not readable.\n");
    	}
    
    	//we figure out the path
    	$currentDir = getcwd() . "/";
    	$currentExtPath = ini_get( "extension_dir" );
    	$subDirs = preg_match_all( "/\//" , $currentExtPath , $matches );
    	unset( $matches );
    
    	//lets make sure we extracted a valid extension path
    	if (!(bool)$subDirs)
    	{
    		die("dl_local(): Could not determine a valid extension path [extension_dir].\n");
    	}
    
    	$extPathLastChar = strlen( $currentExtPath ) - 1;
    
    	if ($extPathLastChar == strrpos( $currentExtPath , "/" ))
    	{
    		$subDirs--;
    	}
    
    	$backDirStr = "";
    	for ($i = 1; $i <= $subDirs; $i++)
    	{
    		$backDirStr .= "..";
    		if ($i != $subDirs)
    		{
    			$backDirStr .= "/";
    		}
    	}
    
    	//construct the final path to load
    	$finalExtPath = $backDirStr . $currentDir . $extensionFile;
    
    	//now we execute dl() to actually load the module
    	if (!dl( $finalExtPath ))
    	{
    		die();
    	}
    
    	//if the module was loaded correctly, we must bow grab the module name
    	$loadedExtensions = get_loaded_extensions();
    	$thisExtName = $loadedExtensions[ sizeof( $loadedExtensions ) - 1 ];
    
    	//lastly, we return the extension name
    	return $thisExtName;
    
    } //end _dl_local()
}