<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Portal extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('portal_model');
        $this->load->library('facebook_api');
        $this->load->helper('facebook');
    }

    public function index() {
        // TODO Here goes the general message and signup infos

        echo "SIGNUP";
    }

    public function users($user) {
        try {
            $connection = $this->portal_model->getConnectionForUser($user);
        } catch (Exception $e) {
            if ($e->getMessage() == 'Unknown User') {
                redirect('portal');
            }
        }

        $args = array_slice(func_get_args(), 1);

        if (empty($args)) {
            $args = array('playlist');
        }

        switch ($args[0]) {
            case 'artist':
                $this->_artist($connection, $args);
                break;

            case 'album':
                $this->_album($connection, $args);
                break;

            case 'song':
                $this->_song($connection, $args);
                break;

            default:
                $this->_playlist($connection, $args);
        }
    }

    private function _artist(array $connection, array $args) {
        // TODO show a single artist

        echo "ARTIST";
    }

    private function _album(array $connection, array $args) {
        // TODO show a single album

        echo "ALBUM";
    }

    private function _song(array $connection, array $args) {
        // TODO show a single song

        echo "SONG";
    }

    private function _playlist(array $connection, array $args) {
        $connection = array_reverse($connection);
        $id = array_pop($connection);

        $connection['dbdriver'] = 'mysql';
        $connection['dbprefix'] = '';
        $connection['pconnect'] = FALSE;
        $connection['db_debug'] = FALSE;

        $data = $this->portal_model->getPlaylist($connection);
//        $data['size'] = count($data);

        print_r($connection);
        print_r($data);

        echo "PLAYLIST";
    }

    public function _remap($user, $params = array()) {
        if ($user == 'index') {
            $this->index();
        } else {
            if ($user != 'users') {
                $params = array_merge(array($user), $params);
            }

            call_user_func_array(array($this, 'users'), $params);
        }
    }
}

/* End of file portal.php */
/* Location: ./application/controllers/portal.php */