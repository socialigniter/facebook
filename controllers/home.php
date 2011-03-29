<?php
class Home extends Dashboard_Controller
{
    function __construct()
    {
        parent::__construct();

		$this->load->library('facebook');		
		$this->data['page_title'] = 'Facebook';
	}
 
 	function timeline()
 	{		
		/* This is how to call a client API methods
		// Old Calls From Before I implemented oAuth 2
		$this->facebook_connect->client->feed_registerTemplateBundle($one_line_story_templates, $short_story_templates, $full_story_template);
		$this->data['event'] 		=  $this->facebook_connect->client->events_get($data['user_id']);		
		$this->data['user_info']		= $this->facebook->client->users_getInfo(653983917, array('uid', 'first_name','last_name', 'username', 'name', 'locale', 'pic_square', 'profile_url', 'email','sex','birthday', 'current_location'));
		$this->data['notifications']	= $this->facebook->client->notifications_get();
		$this->data['photos_get']		= $this->facebook->client->photos_get(653983917, 2808839539919945725, '');		
		$this->data['photos_get_albums']= $this->facebook->client->photos_getAlbums(653983917, '');
		$this->data['stream']			= $this->facebook->client->stream_get(653983917, '', '', '', '', '', '');
		$this->data['stream_filters']	= $this->facebook->client->stream_getFilters(653983917);
		*/
						
		$this->data['sub_title'] = 'News Feed';				
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