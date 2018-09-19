<?php

/**
 * @author Benedikt Bauer
 *
 *
 */

class Twitter_model extends CI_Model {

    public function __construct() {
        parent::__construct ();
        $this->load->database();
        $this->load->helper('array');
    }

    /**
     *
     * Create new User record
     * @param array $data
     * @throws InvalidArgumentException
     */
    private function _twitter_create($data)
    {
        // Check if needed fields are present in data array. (Search for a bool(false) element should fail)
        if (array_search(false, elements(array('tw_userid', 'tw_token', 'tw_secret', 'tw_screenname'), $data), true) === false)
        {
            $this->db->insert('twitter', array('ID' => $data['tw_userid'], 'token' => $data['tw_token'], 'secret' => $data['tw_secret'], 'screenname' => $data['tw_screenname']));
        }
        else
        {
            throw new InvalidArgumentException('$data array does not contain required information for user creation: '.var_export($data));
        }
    }

    /**
     *
     * Retrieve certain parameters from twitter user table
     * @param string $section The type of data to retrieve (settings, pal, index)
     * @param array $conditions Conditions by which to identify the users whose data shall be retrieved
     */
    public function twitter_retrieve($section, $conditions)
    {
        if (!is_array($conditions))
        {
            throw new InvalidArgumentException('Condition is not an array: '.var_export($conditions, TRUE));
        }

        switch(strval($section))
        {
            case 'settings':
                if (FALSE === $userid = element('tw_userid', $conditions))
                {
                    throw new InvalidArgumentException('Condition must contain userid to search for: '.var_export($conditions, TRUE));
                }

                $settings = array('timing', 'timing_value', 'prefix', 'postfix');
                if (is_numeric($userid))
                {
                    $this->db->select($settings)->from('twitter')->where('ID', $userid);
                }
                elseif (is_string($userid))
                {
                    $this->db->select($settings)->from('twitter')->like('screenname', $userid);
                }


                $query = $this->db->get();

                if ($query->num_rows() > 1)
                {
                    throw new LengthException('Userid provided is not unique');
                }
                elseif ($query->num_rows() === 0)
                {
                    return $this->_twitter_create($conditions);
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
                if (FALSE === $userid = element('tw_userid', $conditions))
                {
                    throw new InvalidArgumentException('Condition must contain userid to search for: '.var_export($conditions, TRUE));
                }

                $settings = array(
                    'timing_value',
                    'prefix',
                    'postfix',
                    'token',
                    'secret',
                    'screenname',
                    'ID',
                );

                if (is_numeric($userid))
                {
                    $this->db->select($settings)->from('twitter')->where('ID', $userid);
                }
                elseif (is_string($userid))
                {
                    $this->db->select($settings)->from('twitter')->like('screenname', $userid);
                }

                $query = $this->db->get();

                if ($query->num_rows() > 1)
                {
                    throw new LengthException('Userid provided is not unique');
                }
                elseif ($query->num_rows() === 0)
                {
                    redirect('http://sam-song.info');
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
     *
     * Update user data that might have changed
     * @param array $data
     * @param int|string $user
     */
    public function twitter_update($data, $user)
    {
        $this->db->set($data)->where('ID', $user)->update('twitter');
    }

    /**
     *
     * Delete deauthorized users from database
     * @param int|string $user
     */
    public function twitter_delete($user)
    {
        $this->db->where('userid', $user)->delete('twitter');
    }
}

/* End of file twitter_model.php */
/* Location: ./application/models/twitter_model.php */
