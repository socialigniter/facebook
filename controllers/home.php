<?php
class Home extends Dashboard_Controller
{
    function __construct()
    {
        parent::__construct();

		if (config_item('facebook_enabled') == 'FALSE') redirect(base_url(), 'refresh');
		
		$this->check_connection = $this->social_auth->check_connection_user($this->session->userdata('user_id'), 'facebook', 'primary');

		$facebook_config = array(
			'client_id' 	=> config_item('facebook_app_id'),
			'client_secret'	=> config_item('facebook_secret_key'),
			'callback_url'	=> base_url().'facebook/oauth',
			'access_token'	=> $this->check_connection->auth_one
		);
					
		$this->load->library('facebook_oauth', $facebook_config);
			
		$this->data['page_title'] = 'Facebook';
	}
 
 	function timeline()
 	{					
 		if ($this->uri->segment(3) == 'wall') $api_endpoint = '/me/feed';
 		else $api_endpoint = '/me/home';
  	
		$timeline 		= $this->facebook_oauth->get($api_endpoint);		
		$timeline_view	= NULL;
	
		//echo '<pre>';
		//print_r($timeline);
	
		// Build Feed				 			
		if (!empty($timeline))
		{
			foreach ($timeline->data as $item)
			{
				// Item
				$this->data['item_id']				= $item->id;
				$this->data['item_type']			= $item->type;
				
				// Contributor
				$this->data['item_user_id']			= $item->from->id;
				$this->data['item_avatar']			= $this->social_igniter->profile_image(0, '', '');
				$this->data['item_contributor']		= $item->from->name;
				$this->data['item_profile']			= 'http://facebook.com/profile.php?id='.$item->from->id;
				
				// Activity
				$content = '';
				if (property_exists($item, 'message')) $content .= item_linkify($item->message);		
				if (property_exists($item, 'description')) $content .= item_linkify($item->description);
				
				
				$this->data['item_content']			= $content;
				$this->data['item_content_id']		= $item->id;
				$this->data['item_date']			= timezone_datetime_to_elapsed($item->created_time);			

		 		// Actions
			 	$this->data['item_comment']			= base_url().'comment/item/'.$item->id;
			 	$this->data['item_comment_avatar']	= $this->data['logged_image'];
			 	
			 	$this->data['item_can_modify']		= FALSE; //$this->social_auth->has_access_to_modify('activity', $item, $this->session->userdata('user_id'), $this->session->userdata('user_level_id'));
				$this->data['item_edit']			= ''; //base_url().'home/'.$item->module.'/manage/'.$item->content_id;
				$this->data['item_delete']			= ''; //base_url().'api/activity/destroy/id/'.$item->activity_id;

				// View
				$timeline_view .= $this->load->view(config_item('dashboard_theme').'/partials/item_timeline.php', $this->data, true);
	 		}
	 	}
	 	else
	 	{
	 		$timeline_view = '<li><p>No Facebook updates to show from anyone</p></li>';
 		}
		
	 	$this->data['social_post'] 		= $this->social_igniter->get_social_post($this->session->userdata('user_id'), 'social_post_horizontal'); 		
		$this->data['status_updater']	= $this->load->view(config_item('dashboard_theme').'/partials/status_updater', $this->data, true);
		$this->data['timeline_view'] 	= $timeline_view;
		$this->data['sub_title'] 		= 'News Feed';
				
		$this->render();
	}	    
	
	function wall()
	{
	
		$this->data['sub_title'] = 'Wall';				
		$this->render();		
	
	}


	function messages()
	{
	
		$this->data['sub_title'] = 'Messages';				
		$this->render();		
	
	}   
	
	
	// Moved from Social Ingiter Library
	function post_to_social()
	{
	
    	if (($this->config->item('facebook')) && ($this->input->post('post_to_facebook') == 1))
    	{
			$this->load->library('facebook');	
			// IS THIS TWITTER ACCOUNT ALREADY CONNECTED			
			$check_connection = $this->connections_model->check_connection_user($this->session->userdata('user_id'), 'facebook');

			if ($check_connection)
			{	
				$this->facebook->getSessionDatabase($check_connection->token_whole);	
			  	$this->facebook->api('/me/feed', 'post', array('message'=> $this->input->post('update'), 'cb' => ''));
			}
    	}

		 
    }
}