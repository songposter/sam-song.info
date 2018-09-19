<?php

/**
 * @author Benedikt Bauer
 *
 *
 */

class myspace_model extends CI_Model {

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
    private function _myspace_create($data)
    {
        // Check if needed fields are present in data array. (Search for a bool(false) element should fail)
        if (array_search(false, elements(array('ms_userid', 'ms_token', 'ms_secret', 'username'), $data), true) === false)
        {
            $this->db->insert('myspace', array('ID' => $data['ms_userid'], 'token' => $data['ms_token'], 'secret' => $data['ms_secret'], 'username' => $data['username']));
        }
        else 
        {
            throw new InvalidArgumentException('$data array does not contain required information for user creation: '.var_export($data));
        }
    }

    /**
     * 
     * Retrieve certain parameters from myspace user table
     * @param string $section The type of data to retrieve (settings, pal, index)
     * @param array $conditions Conditions by which to identify the users whose data shall be retrieved
     */
    public function myspace_retrieve($section, $conditions)
    {
        if (!is_array($conditions))
        {
            throw new InvalidArgumentException('Condition is not an array: '.var_export($conditions, TRUE));
        }
        
        switch(strval($section))
        {
            case 'settings':
                if (FALSE === $userid = element('ms_userid', $conditions))
                {
                    throw new InvalidArgumentException('Condition must contain userid to search for: '.var_export($conditions, TRUE));
                }
                
                $settings = array('timing_value', 'prefix', 'postfix', 'picture_dir', 'website_link', 'website_title', 'website_description', 'username');
                $this->db->select($settings)->from('myspace')->where('ID', $userid)->limit(1);
                $query = $this->db->get();
                        
                if ($query->num_rows() > 1)
                {
                    throw new LengthException('Userid provided is not unique');
                }
                elseif ($query->num_rows() === 0)
                {
                    return $this->_myspace_create($conditions); 
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
                if (FALSE === $userid = element('ms_userid', $conditions))
                {
                    throw new InvalidArgumentException('Condition must contain userid to search for: '.var_export($conditions, TRUE));
                }
                
                $settings = array(
                    'timing_value',
                    'prefix',
                    'postfix',
                    'token',
                    'secret',
                    'ID',
                    'website_title',
                    'website_description',
                    'website_link',
                    'picture_dir',
                    'username',
                );
                
                $this->db->select($settings)->from('myspace')->where('ID', $userid)->limit(1);
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
     * 
     * Update user data that might have changed
     * @param array $data
     * @param int|string $user
     */    
    public function myspace_update($data, $user)
    {
        $this->db->set($data)->where('ID', $user)->update('myspace');
    }

    /**
     * 
     * Delete deauthorized users from database
     * @param int|string $user
     */    
    public function myspace_delete($user)
    {
        $this->db->where('userid', $user)->delete('myspace');
    }
}

/* End of file myspace_model.php */
/* Location: ./application/models/myspace_model.php */