<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    $config = array(
        // Song Types
		array(
			'field' => 'songtypes[]',
			'label' => 'Songtypes',
			'rules' => 'required|callback_songtypes_check',
		),
        // Timing type
		array(
			'field' => 'timing',
			'label' => 'Post Timing',
			'rules' => 'required',
		),
        // Timing interval
		array(
			'field' => 'timing_value',
			'label' => 'Interval',
			'rules' => 'trim|required|is_natural',
		),
        // Action Title
		array(
			'field' => 'action_title',
			'label' => 'Action Title',
			'rules' => 'trim|xss_clean',
		),
        // Action Link
		array(
			'field' => 'action_link',
			'label' => 'Action Link',
			'rules' => 'trim|prep_url',
		),
        // Prefix
		array(
			'field' => 'prefix',
			'label' => 'Prefix',
			'rules' => 'trim|xss_clean',
		),
        // Postfix
		array(
			'field' => 'postfix',
			'label' => 'Postfix',
			'rules' => 'trim|xss_clean',
		),
		// Field order
		array(
		    'field' => 'field_order',
		    'label' => 'Field Order',
		    'rules' => 'regex_match[/(artist|title)\|(artist|title)]',
		),
        // Website Title
		array(
			'field' => 'website_title',
			'label' => 'Website Title',
			'rules' => 'trim|xss_clean',
		),
        // Website Link
		array(
			'field' => 'website_link',
			'label' => 'Website Link',
			'rules' => 'trim|prep_url',
		),
        // Website Description
		array(
			'field' => 'website_description',
			'label' => 'Description',
			'rules' => 'trim|prep_for_form',
		),
        // Picture Dir
		array(
			'field' => 'picture_dir',
			'label' => 'Picture URL',
			'rules' => 'trim|prep_url',
		),
    );

/* End of file facebook_rules.php */
/* Location: ./application/config/facebook_rules.php */