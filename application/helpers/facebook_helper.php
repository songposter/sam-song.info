<?php

    function facebook_xmlns()
    {
        return 'xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:og="http://ogp.me/ns#"';
    }

    function facebook_app_id()
    {
        $ci =& get_instance();

        return $ci->config->item('facebook_app_id');
    }

    function facebook_picture($who = 'me')
    {
        $ci =& get_instance();

        return $ci->facebook_api->append_token($ci->config->item('facebook_api_url').$who.'/picture');
    }

    function facebook_opengraph_meta($opengraph)
    {
        $ci =& get_instance();

        $return = '<meta property="fb:admins" content="'.$ci->config->item('facebook_admins').'" />';
        $return .= "\n";
        $return .= '<meta property="fb:app_id" content="'.$ci->config->item('facebook_app_id').'" />';
        $return .= "\n";
        $return .= '<meta property="og:site_name" content="'.$ci->config->item('facebook_site_name').'" />';
        $return .= "\n";

        foreach ( $opengraph as $key => $value )
        {
            $return .= '<meta property="'.$key.'" content="'.$value.'" />';
            $return .= "\n";
        }

        return $return;
    }

/* End of file facebook_helper.php */
/* Location: ./application/helpers/facebook_helper.php */