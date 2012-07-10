<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Portal extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('portal_model');

        $this->load->library('facebook_api');
        $this->load->library('pagination');

        $this->load->helper('facebook');
        $this->load->helper('url');
        $this->load->helper('array');
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

        // ARGS = PLAYLIST(/INDEX/{0,A-Z}[/OFFSET/{0-99999...}])
        $indexkey = array_search('index', $args);
        $offsetkey = array_search('offset', $args);

        $index = '';
        $indexs = '';
        $offset = 0;
        $config['uri_segment'] = 5;
        $url = 'offset/';

        if ($indexkey !== false) {
            $key = element($indexkey+1, $args, 'offset');
            $index = $key == 'offset' ? '' : $key;
            $indexs = $index.'/';
            $config['uri_segment'] = $index == '' ? 5 : 7;
            $url = 'index/'.$indexs.'offset/';
        }

        if ($offsetkey !== false) {
            $key = element($offsetkey+1, $args, 'index');
            $offset = $key == 'index' ? '' : $key;
            $config['uri_segment'] = $offsetkey+4;
            $url = 'index/'.$indexs.'offset/';
        }

        $user = $connection['user'];

        $songinfos = $this->portal_model->getPlaylist($connection, $index == '' ? null : $index, $offset);

        $songs = $songinfos['songs'];
        $count = $songinfos['count'];

        $navigation []= '<a href="'.site_url('portal/'.$user.'/playlist/index/0').'">#</a>';

        foreach (range('A', 'Z') as $letter)
        {
            $navigation []= '<a href="'.site_url('portal/'.$user.'/playlist/index/'.$letter).'">'.$letter.'</a>';
        }

        $config['base_url'] = site_url('portal/'.$user.'/playlist/'.$url);
        $config['total_rows'] =  $count;
        $config['per_page'] = 20;

        $this->pagination->initialize($config);

        $data = array
        (
            'songinfos' => $songs,
            'base' => $this->config->item('base_url'),
            'user' => $user,
            'navigation' => $navigation,
            'pagination' => $this->pagination->create_links(),
        );

        $this->load->view('portal/playlist', $data);
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