<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Facebook extends CI_Controller {

    public function __construct()
	{
		parent::__construct();

		$this->load->library('facebook_api');
		$this->load->model('facebook_model');
		$this->load->helper('facebook');
		$this->facebook_api->set_callback(site_url('facebook/settings'));
	}

	/**
	 * Just for testing purposes
	 */

	public function api()
	{
		$uri = $this->input->get('uri');
		$params = $this->input->get('params');
		$method = $this->input->get('method');
		$token = $this->input->get('token');

		$this->facebook_api->set_callback(current_url());

		// User needs to be logged in for various settings data
		if (!$this->facebook_api->logged_in())
		{
		    redirect('facebook/login');
		}

		if ($token)
		{
			$this->facebook_api->set_token($token);
		}

		if (!$params)
		{
			$params = array();
		}

		try {
			$fbResponse = $this->facebook_api->call($method, $uri, $params);
		} catch (FacebookException $e) {
			log_message('debug', $method.' /'.$uri.http_build_query($params).'&token='.$this->facebook_api->get_token());

			log_message('error', $e->getCode().' - '.$e->getMessage());
        	echo $e->getMessage();
            exit(0);
		}

		var_dump($fbResponse);
	}


	/**
	 * POST message to facebook as received via GET
	 */

	public function index()
	{
        $this->load->library('user_agent');

	    // userid from GET request
		$userid = $this->input->get('userid', TRUE);

		if ($this->agent->agent_string() !== 'Mozilla/3.0 (compatible)' && $this->input->get('override') !== 'true')
		{
		    redirect('/');
		    die();
		}

        // retrieve settings from database via model
		$data = $this->facebook_model->facebook_retrieve('index', array('userid' => $userid));

		if ($data['use_token'] === '' || ($data['expires'] != 0 && $data['expires'] < time()))
		{
			die('ERROR: REAUTHENTICATE AT SAM-SONG.INFO (Usually every 50 days)');
		}

		if ($data['limit_reached'] >= strtotime('-22 hours'))
		{
			die('ERROR: Request limit reached, increase time between posts');
		}
		else
		{
			$this->facebook_model->facebook_update(array('limit_reached' => ''), $userid);
		}

		$messages = explode("\n",$this->input->get('message', TRUE));
		$message = array_shift($messages);

		if ($message == '')
		    die('Error: No message provided');

		$description = implode('<center></center>', $messages);

		// parameters for the API call, start with the message including pre- and postfix
        $api_parameters = array('caption' =>  stripslashes(trim($description)));

        // Combine picture url from specific picture and url to directory
        if (($data['picture_dir'] !== '') && $this->input->get('picture', TRUE))
        {
            $api_parameters['picture'] = $data['picture_dir'].$this->input->get('picture', TRUE);
        }

        // Add website via seperate API parameters
        if ($data['website_link'] !== '')
        {
            $api_parameters['link'] = $data['website_link'];
            $title_seperator = empty($data['website_title']) ? '' : '<center></center>';
            $api_parameters['description'] = '<b>'.$data['website_title'].'</b>'.$title_seperator.str_replace(array("\n", "\r\n"), '<center></center>', $data['website_description']);
        }

        if ($data['website_link'] === '' && ($data['picture_dir'] === '' || $this->input->get('picture', TRUE) === false))
        {
        	$api_parameters['link'] = 'http://facebook.com/profile.php?id='.$userid;
        }

        $api_parameters['name'] = stripslashes(trim($data['prefix'].' '.$message.' '.$data['postfix']));


        // Add action link as json encoded array of title and link
        if ($data['action_link'] !== '')
        {
			$site= 'http://api.bitly.com/v3/shorten?login=mastacheata&apiKey=R_d3e1cba0def47f672087307d5ea7785d&longUrl='.$data['action_link'].'&format=txt';
            $shortlink = file_get_contents($site);

            $action = array('name' => $data['action_title'], 'link' => $shortlink);
            $api_parameters['actions'] = json_encode($action);
        }

        $this->facebook_api->set_token($data['use_token']);
        $this->facebook_api->set_expiry($data['expires']);

        // Post to a page (only if activated in database)
        if ($data['ispage'] == 1 && !$this->input->get('group'))
        {
            $api_url = '/feed';
        }
        // Group posting (not yet documented)
        elseif ($this->input->get('group'))
        {
            $api_url = $this->input->get('group', TRUE).'/feed';
        }
        // Plain old user posting
        else
        {
            $api_url = $userid.'/feed';
        }

        // try to POST the message to facebook
        try
        {
            $fbResponse = $this->facebook_api->call('post', $api_url, $api_parameters);
        }
        // if it fails return an error message to the user
        catch (FacebookException $fbe)
        {
        	if ($fbe->getCode() === 341)
        	{
        		$this->facebook_model->facebook_update(array('limit_reached' => time()), $userid);
        	}

			log_message('debug',
							'User: '.$this->input->get('userid').' '
							.'Token: '.$this->facebook_api->get_token().' '
							.'URL: '.$api_url
						);
			log_message('error', $fbe->getCode().' - '.$fbe->getMessage());
        	echo $fbe->getMessage();
            exit(0);
        }

        //POSTing the message succeeded, output the message id
        echo '##Facebook: https://graph.facebook.com/'.$fbResponse->id.'?access_token='.$this->facebook_api->get_token();
	}

	/**
	 * Set callback URL and redirect user to login
	 */

	public function login()
	{
		$this->facebook_api->login();
	}

	/**
	 * Settings
	 *
	 * Complete settings handling.
	 * From display via validation and sanitizing to storage and PAL output
	 */

	public function settings()
	{
		$this->load->helper('form');
		$this->load->library('form_validation');

		// User needs to be logged in for various settings data
		if (!$this->facebook_api->logged_in())
		{
		    redirect('facebook/login');
		}

		try
		{
		    $fbResponse = $this->facebook_api->call('get', 'me');
		}
		catch (FacebookException $e)
		{
		    log_message('error', $e->getMessage());
		    redirect('facebook/login');
		}

		$userid = $fbResponse->id;

		$user = new stdClass();
		$user->name = 'Personal Account (ID: '.$userid.')';
		$user->access_token = $this->facebook_api->get_token();
        $user->expires = $this->facebook_api->get_expiry();

		// Retrieve default settings from database
		$data = $this->facebook_model->facebook_retrieve('settings', array('userid' => $userid, 'use_token' => $user->access_token, 'expires' => $user->expires));

		// default values in case the user was just created
		if (!is_array($data))
		{
		    $data = array(
		        'ispage' => 0,
		        'timing_value' => 10,
		        'action_title' => '',
		        'action_link' => '',
		        'prefix' => '',
		        'postfix' => '',
		        'website_title' => '',
		        'website_description' => '',
		        'website_link' => '',
		        'picture_dir' => '',
	            'expires' => '',
		    );
		}

		$data['this_url'] = current_url();

		// List of songtypes might change in future versions of SAM
		$data['songtypes'] = array(
            'S' => 'S - Normal Song',
            'I' => 'I - Station ID',
            'P' => 'P - Promo',
            'J' => 'J - Jingle',
            'A' => 'A - Advertisement',
            'N' => 'N - Syndicated News',
            'V' => 'V - Interviews',
            'X' => 'X - Sound FX',
            'C' => 'C - Unknown Content',
            '?' => '? - Unknown',
		);

		// Timing types available: Time / PlayCount
		$data['timings'] = array(
			'WaitForTime' => 'By minutes between two posts',
		    'WaitForPlayCount' => 'By number of songs between two posts',
		);

		// User may post to pages...
		if ($data['ispage'] === '1')
		{
			// ...then list all his page names and tokens for select
		    try
		    {
		    	$data['accounts'] = $this->facebook_api->call('get', 'me/accounts')->data;
		    }
		    catch (FacebookException $fbe)
		    {
		    	// An error in this state should only occur when testing this myself
		    	// Especially when the token was set to something other than the user token
		    	log_message('error', $fbe->getMessage());
		    	redirect('facebook/logout');
		    }

    		// Put the user's personal account on top
		    array_unshift($data['accounts'], $user);
		}
		else
		{
		    $data['accounts'] = array($user);
		}

		// Validate settings
		// FAIL => Display errors on settings page (default)
		if ($this->form_validation->run() == FALSE)
		{
			$data['base'] = $this->config->item('base_url');
			$this->load->view('facebook_settings', $data);
			print_r($data);
			
			$this->load->view('footer');
		}
		// SUCCESS => save changes to db, load EVERYTHING from db and generate PAL
		else
		{
			$this->_save($userid);
			$data = $this->facebook_model->facebook_retrieve('settings', array('userid' => $userid));
			$this->_pal($data);
		}
	}

	/**
	 * Deauthorize callback when user removed App from account
	 * deletes the user data from the database
	 */

	public function delete()
	{
		// Signed request parameter is expected via POST
		if (($signed_request = $this->input->post('signed_request')) === false)
		{
			exit();
		}

		// record the signed request for debugging purposes
		log_message('debug', 'Signed Request: '.$signed_request);

		// Check signature to originate from facebook (only they know your application secret)
		if (($data = $this->facebook_api->parse_signedRequest($signed_request)) === NULL)
		{
			exit();
		}

		// execute deletion operation
		$this->facebook_model->facebook_delete($data['user_id']);

		// log the success message as informative
		log_message('info', 'Userid: '.$data['user_id'].' deleted successfully');
	}

	/**
	 * Check if songtype is valid
	 */

	public function songtypes_check($str = '')
	{
	    $valid_songtypes = array('S', 'I', 'P', 'J', 'A', 'N', 'V', 'X', 'C', '?');
	    return in_array($str, $valid_songtypes, TRUE);
	}

	/**
	 * Save changes to database
	 */

	private function _save($userid)
	{
		// basic values are required, but this check doesn't take much time
		if ($this->input->post('basicchanged') === '1')
		{
			$basic = array(
				'songtypes' => implode($this->input->post('songtypes')),
				'timing' => $this->input->post('timing'),
				'timing_value' => $this->input->post('timing_value'),
			);
		}
		else
		{
			$basic = array();
		}

		// only store settings from advanced section if any were changed
		if ($this->input->post('advancedchanged') === '1')
		{
			$advanced = array(
				'use_token' => $this->input->post('account'),
				'action_title' => $this->input->post('action_title'),
				'action_link' => $this->input->post('action_link'),
				'prefix' => $this->input->post('prefix'),
				'postfix' => $this->input->post('postfix'),
				'field_order' => $this->input->post('field_order'),
			    'expires' => $this->facebook_api->get_expiry(),
			);
		}
		else
		{
			$advanced = array();
		}

		// only store settings from website section if any were changed
		if ($this->input->post('websitechanged') === '1')
		{
			$website = array(
				'website_title' => $this->input->post('website_title'),
				'website_link' => $this->input->post('website_link'),
				'website_description' => $this->input->post('website_description'),
			);
		}
		else
		{
			$website = array();
		}

		// only store settings from artwork section if any were changed
		if ($this->input->post('artworkchanged') === '1')
		{
			$artwork = array(
				'picture_dir' => $this->input->post('picture_dir'),
			);
		}
		else
		{
			$artwork = array();
		}

		// merge all the section arrays to one big update array
		$update = array_merge($basic, $advanced, $website, $artwork);
		$this->facebook_model->facebook_update($update, $userid);
	}



	/**
	 * Generate PAL script from userdata
	 * @param array $data
	 */

	private function _pal(array $data)
	{
	    $this->load->helper('array');

	    // split ordering into array with numerical indices
        $sort_fields = explode('|', $this->input->post('field_order'));
        if ($this->input->post('advancedchanged') === '0')
        {
            $sort_fields = element('sort_fields', $data, array('artist', 'title'));
        }

        $timing = $this->input->post('timing');
        $timing_value = $this->input->post('timing_value');

        // Workaround so we can use the database settings in default case
        do
        {
            switch ($timing)
            {
                // PAL.WaitForTime('+00:XX:00');
            	case 'WaitForTime':
                    $interval = "PAL.WaitForTime('+00:".$this->input->post('timing_value').":00');";
                    break;

				// PAL.WaitForPlayCount(XX);
                case 'WaitForPlayCount':
                    $interval = "PAL.WaitForPlayCount(".$this->input->post('timing_value').");";
                    break;

                // Whatever was stored in the database or
                // Default: timing=WaitForPlayCount
                // Default: timing_value=10
                default:
                    $timing = element('timing', $data, 'WaitForPlayCount');
                    $timing_value = element('timing_value', $data, '10');
            }
        // we didn't create a new string, this is the default case
        // but now the timing and timing_value are filled
        } while (empty($interval));

		try
		{
		    $fbResponse = $this->facebook_api->call('get', 'me');
		}
		catch (FacebookException $e)
		{
		    redirect('facebook/login');
		}

		$userid = $fbResponse->id;

		// Songtypes implode to string with all letters glued to each other
		$songtypes = implode($this->input->post('songtypes'));

		// Get the template (uses /** ABC_XYZ **/ as patterns)
	    $pal_template = file_get_contents('pal_template.txt');

	    $picture_enabled = empty($data['picture_dir']) ? "+ ''" : "+ '&picture=' + picture";

	    // These are the patterns used inside the template
	    $patterns = array(
            '/\/\*\*FB_TWEET\*\*\//',
            '/\/\*\*Replace_Interval\*\*\//',
            '/\/\*\*First_Field\*\*\//',
            '/\/\*\*Second_Field\*\*\//',
            '/\/\*\*USER_ID\*\*\//',
            '/\/\*\*Song_Types\*\*\//',
	    	'/\/\*\*PICTURE\*\*\//',
        );

        // and here come tha values that need to be replaced
        $replacements = array(
            'facebook',
            $interval,
            "Song['".$sort_fields[0]."']",
            "Song['".$sort_fields[1]."']",
            $userid,
            $songtypes,
            $picture_enabled,
        );

        // do the replacement (this should never fail unless someone modified the server)
        $file = preg_replace($patterns, $replacements, $pal_template);

        $this->load->helper('download');
        // Serve the PAL as download
		force_download('facebook.110728a.pal', $file);
	}
}

/* End of file facebook.php */
/* Location: ./application/controllers/facebook.php */