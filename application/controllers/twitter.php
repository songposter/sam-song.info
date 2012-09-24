<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author benedikt
 *
 *
 */
class Twitter extends CI_Controller
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library('twitter_api');
        $this->load->model('twitter_model');
    }

    public function login()
    {
        // If user is not yet logged in, (re)authorize
        if ($this->twitter_api->logged_in() === false)
        {
            $this->twitter_api->set_callback(site_url('twitter/login'));
            $this->twitter_api->login();
        }

        redirect('twitter/settings');
    }

    public function index()
    {
        // userid from GET request
		$userid = $this->input->get('userid', TRUE);

		$this->load->library('user_agent');
		if ($this->agent->agent_string() !== 'Mozilla/3.0 (compatible)' && $this->input->get('override') !== 'true')
		{
		    redirect('/');
		    die();
		}

        // retrieve settings from database via model
		$data = $this->twitter_model->twitter_retrieve('index', array('tw_userid' => $userid));

		// parameters for the API call, start with the message including pre- and postfix
        $api_parameters = array('status' => stripslashes(trim($data['prefix'].' '.$this->input->get('message', TRUE).' '.$data['postfix'])));

        // post new messages to the json endpoint
        $api_url = 'statuses/update.json';

        // put token info from database in expected format
        $tokenKeys = array('tw_token', 'tw_secret', 'tw_screenname', 'tw_userid');
        $tokenVals = elements(array('token', 'secret', 'screenname', 'ID'), $data);
        $token = array_combine($tokenKeys, $tokenVals);
        $this->twitter_api->set_token($token);

        $twResponse = $this->twitter_api->call('post', $api_url, $api_parameters);

        if ($twResponse === false || $twResponse === NULL)
        {
        	die("An error occured, your request could not be completed.");
        }
        else
        {
            echo '##Twitter: http://twitter.com/#!/'.$twResponse->user->name.'/status/'.$twResponse->id_str;
        }
    }

    public function settings()
    {
		$this->load->helper('form');
		$this->load->library('form_validation');

		// User needs to be logged in for various settings data
		if (!$this->twitter_api->logged_in())
		{
		    redirect('twitter/login');
		}

		$userData = $this->twitter_api->get_token();

		// Retrieve default settings from database
		$data = $this->twitter_model->twitter_retrieve('settings', $userData);

		// default values in case the user was just created
		if (!is_array($data))
		{
		    $data = array(
		        'timing_value' => 10,
    	        'prefix' => '',
		        'postfix' => '',
		    	'timing' => 'WaitForTime',	
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


		// Validate settings
		// FAIL => Display errors on settings page (default)
		if ($this->form_validation->run() == FALSE)
		{
			$data['base'] = $this->config->item('base_url');
			$this->load->view('twitter_settings', $data);
			$this->load->view('footer');
		}
		// SUCCESS => save changes to db, load EVERYTHING from db and generate PAL
		else
		{
			$this->_save();
			$data = $this->twitter_model->twitter_retrieve('settings', array('tw_userid' => $this->session->userdata('tw_userid')));
			$this->_pal($data);
		}
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

	private function _save()
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
				'prefix' => $this->input->post('prefix'),
				'postfix' => $this->input->post('postfix'),
				'field_order' => $this->input->post('field_order'),
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

		$tokens = array
		(
		    'token' => $this->session->userdata('tw_token'),
		    'secret' => $this->session->userdata('tw_secret'),
		    'screenname' => $this->session->userdata('tw_screenname'),
	    );

		// merge all the section arrays to one big update array
		$update = array_merge($basic, $advanced, $website, $artwork, $tokens);
		$this->twitter_model->twitter_update($update, $this->session->userdata('tw_userid'));
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

	    $tokens = array();
	    // extract userid from token
		if (false === $userid = element('tw_userid', $this->twitter_api->get_token()))
		{
		    redirect('twitter/login');
		}

		// Songtypes implode to string with all letters glued to each other
		$songtypes = implode($this->input->post('songtypes'));

		// Get the template (uses /** ABC_XYZ **/ as patterns)
	    $pal_template = file_get_contents('pal_template.txt');

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
            'twitter',
            $interval,
            "Song['".$sort_fields[0]."']",
            "Song['".$sort_fields[1]."']",
            $userid,
            $songtypes,
            "+ ''",
        );

        // do the replacement (this should never fail unless someone modified the server)
        $file = preg_replace($patterns, $replacements, $pal_template);

        $this->load->helper('download');
        // Serve the PAL as download
		force_download('twitter.110728a.pal', $file);
	}
}