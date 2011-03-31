<?php
class Oauth extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

		if (config_item('facebook_enabled') == 'FALSE') redirect(base_url(), 'refresh');

		$facebook_config = array(
			'client_id' 	=> config_item('facebook_app_id'),
			'client_secret'	=> config_item('facebook_secret_key'),
			'callback_url'	=> base_url().'facebook/oauth',
			'access_token'	=> $this->session->userdata('access_token')
		);
			
		$this->load->library('facebook_oauth', $facebook_config);

	}
	
	function index()
	{
		if (isset($_REQUEST['code']))
		{
			if ($access_token = $this->facebook_oauth->getAccessToken($_REQUEST['code']))
			{
				$this->session->set_userdata('access_token', $access_token);
			
				redirect(base_url().'facebook/oauth/timeline');
			}

		}
		else
		{
			$auth_url = $this->facebook_oauth->getAuthorizeUrl('offline_access, user_about_me, user_activities, user_events, user_interests, user_likes, user_location, user_website, email, read_stream, read_mailbox, user_checkins, publish_stream, publish_checkins');
			echo '<a href="'.$auth_url.'">Facebook Connect</a>';	
		}
	
	
	}

	function timeline()
	{
		echo '<h2>Me</h2>';
		echo '<pre>';
		print_r($this->facebook_oauth->get('/me'));	


		echo '<h2>Timeline</h2>';
		echo '<pre>';
		print_r($this->facebook_oauth->get('/me/home'));	
	
	}

	
}