<?php

/**
 * @author Benedikt Bauer
 *
 *
 */

class Facebook_model extends CI_Model {

	public function __construct() {
		parent::__construct ();
		$this->load->database();
		$this->load->helper('array');
	}

	/**
	 * Create new User record
	 * @param array $data
	 * @throws InvalidArgumentException
	 */
	private function _facebook_create($data)
	{
		if (array_key_exists('userid', $data) && array_key_exists('use_token', $data))
		{
			$this->db->insert('facebook', array('ID' => $data['userid'], 'use_token' => $data['use_token']));
		}
		else
		{
			throw new InvalidArgumentException('$data array does not contain required information for user creation: '.var_export($data));
		}
	}

	/**
	 * Retrieve certain parameters from facebook user table
	 * @param string $section The type of data to retrieve (settings, pal, index)
	 * @param array $conditions Conditions by which to identify the users whose data shall be retrieved
	 */
	public function facebook_retrieve($section, $conditions)
	{
		if (!is_array($conditions))
		{
			throw new InvalidArgumentException('Condition is not an array: '.var_export($conditions, TRUE));
		}

		switch(strval($section))
		{
			case 'settings':
                if (FALSE === $userid = element('userid', $conditions))
				{
					throw new InvalidArgumentException('Condition must contain userid to search for: '.var_export($conditions, TRUE));
				}

				$settings = array('timing', 'timing_value', 'prefix', 'postfix', 'picture_dir', 'website_link', 'website_title', 'website_description', 'action_title', 'action_link', 'ispage', 'expires');
				$this->db->select($settings)->from('facebook')->where('ID', $userid)->limit(1);
				$query = $this->db->get();

				if ($query->num_rows() > 1)
				{
					throw new LengthException('Userid provided is not unique');
				}
				elseif ($query->num_rows() === 0)
				{
                    return $this->_facebook_create($conditions);
				}
				else
                {
					return $query->row_array();
				}
				break;

			case 'pal':
				// TODO: Store seperate PAL scripts per user
				break;

			case 'index':
				if (FALSE === $userid = element('userid', $conditions))
				{
					throw new InvalidArgumentException('Condition must contain userid to search for: '.var_export($conditions, TRUE));
				}

				$settings = array(
					'timing_value',
					'prefix',
				    'postfix',
				    'picture_dir',
				    'website_link',
				    'website_title',
				    'website_description',
				    'action_title',
				    'action_link',
				    'ispage',
				    'use_token',
                    'limit_reached',
			        'expires',
				);

				$this->db->select($settings)->from('facebook')->where('ID', $userid)->limit(1);
				$query = $this->db->get();

				if ($query->num_rows() > 1)
				{
					throw new LengthException('Userid provided is not unique');
				}
		        elseif ($query->num_rows() === 0)
				{
					redirect(site_url());
				}
				else
				{
				    return $query->row_array();
				}

			    break;

			default:
			    return false;
		}
	}

	/**
	 * Update user data that might have changed
	 * @param array $data
	 * @param int|string $user
	 */
	public function facebook_update($data, $user)
	{
		$this->db->set($data)->where('ID', $user)->update('facebook');
	}

	/**
	 * Delete deauthorized users from database
	 * @param int|string $user
	 */
	public function facebook_delete($user)
	{
		$this->db->where('userid', $user)->delete('facebook');
	}
}

/* End of file facebook_model.php */
/* Location: ./application/models/facebook_model.php */