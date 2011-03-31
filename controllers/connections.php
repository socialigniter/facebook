<?php
class Connections extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

		if (config_item('facebook_enabled') == 'FALSE') redirect(base_url(), 'refresh');

		// Load Library
		$facebook_config = array(
			'client_id' 	=> config_item('facebook_app_id'),
			'client_secret'	=> config_item('facebook_secret_key'),
			'callback_url'	=> base_url().trim_slashes($this->uri->uri_string()),
			'access_token'	=> $this->session->userdata('access_token')
		);
			
		$this->load->library('facebook_oauth', $facebook_config);
		
		$this->module_site = $this->social_igniter->get_site_view_row('module', 'facebook');		   		
	}
			
	function index()
	{
		// User Is Logged In
		if ($this->social_auth->logged_in()) redirect('connections/facebook/add');	
	
		$me 				= NULL;
		$album				= NULL;
		$profile_picture	= NULL;

		// Not Set go to Facebook
		if (!isset($_REQUEST['code']))
		{
			redirect($this->facebook_oauth->getAuthorizeUrl(config_item('facebook_extended_options')));
		}				
		else
		{			
			// Get the goods
			$access_token		= $this->facebook_oauth->getAccessToken($_REQUEST['code']);
			$facebook_user		= $this->facebook_oauth->get('/me');
			$check_connection	= $this->social_auth->check_connection_user_id($facebook_user->id, "facebook");

			// Check Connection
			if ($check_connection)
			{					
				// Login
				if ($this->social_auth->social_login($check_connection->user_id, 'facebook')) 
	        	{ 
		        	$this->session->set_flashdata('message', "Login with Facebook Success");
		        	redirect(base_url().'home', 'refresh');
		        }
		        else 
		        { 
		        	$this->session->set_flashdata('message', "Login with Facebook Did Not Work");
		        	redirect("login", 'refresh');
		        }
			}
			else
			{	
				// Email
				if (property_exists($facebook_user, 'email')) $email = $facebook_user->email;
				else $email = $facebook_user->username.'@facebook.com';
				
				// Check
				if ($user_check = $this->social_auth->get_user('email', $email))
				{
					// Set
					$user_id = $user_check->user_id;
				
					// Add Connection
			   		$connection_data = array(
			   			'site_id'				=> $this->module_site->site_id,
			   			'user_id'				=> $user_id,
			   			'module'				=> 'facebook',
			   			'type'					=> 'primary',
			   			'connection_user_id'	=> $facebook_user->id,
			   			'connection_username'	=> $facebook_user->name,
			   			'auth_one'				=> $access_token
			   		);

					$connection = $this->social_auth->add_connection($connection_data);
				}
				else
				{
					// Convert Time					
					foreach(timezones() as $key => $zone)
					{
						if ($facebook_user->timezone === $zone) $time_zone = $key;						
					}	
					
		        	$utc_offset	= $facebook_user->timezone * 60 * 60;		        	
		
					// Create User
			    	$additional_data = array(
						'name' 		 	=> $facebook_user->name,
						'image'		 	=> '',
						'language'		=> config_item('languages_default'),
						'time_zone'		=> $time_zone,
						'geo_enabled'	=> 0
			    	);
			    			       			      				
			    	// Register User
			  		$user_id = $this->social_auth->social_register($facebook_user->username, $email, $additional_data);
		        	
		        	if ($user_id)
		        	{
						// Add Meta
						$user_meta_data = array(
							'location'	=> $facebook_user->location->name,
							'url'		=> $facebook_user->link,
						);
						
						$this->social_auth->update_user_meta(config_item('site_id'), $user_id, 'users', $user_meta_data);					
						
						// Add Connection
				   		$connection_data = array(
				   			'site_id'				=> $this->module_site->site_id,
				   			'user_id'				=> $created_user_id,
				   			'module'				=> 'facebook',
				   			'type'					=> 'primary',
				   			'connection_user_id'	=> $facebook_user->id,
				   			'connection_username'	=> $facebook_user->name,
				   			'auth_one'				=> $access_token
				   		);
				   							
						$connection = $this->social_auth->add_connection($connection_data);
		       		}
		       		else
		       		{
		        		$this->session->set_flashdata('message', "Error creating user & logging in");
		        		redirect("login", 'refresh');
		       		}
		       	}	
		       		
				// Login
				if ($this->social_auth->social_login($user_id, 'facebook'))
	        	{
        			$this->session->set_flashdata('message', "User created and logged in");
		        	redirect(base_url().'home', 'refresh');
		        }
		        else 
		        {
		        	$this->session->set_flashdata('message', "Login with Facebook in-correct");
		        	redirect("login", 'refresh');
		        }		       		
			}
		}				
	}
	
	function add()
	{		
		if (!$this->social_auth->logged_in()) redirect('connections/facebook');	
		
		$check_connection = $this->social_auth->check_connection_user($this->session->userdata('user_id'), 'facebook', 'primary');
		
		// Is this account connected			
		if ($check_connection)
		{							
			$this->session->set_flashdata('message', "You already have a Facebook account connected");
			redirect('settings/connections', 'refresh');
		}
		else
		{
			// Not Set go to Facebook
			if (!isset($_REQUEST['code']))
			{
				redirect($this->facebook_oauth->getAuthorizeUrl('offline_access, user_about_me, user_activities, user_events, user_interests, user_likes, user_location, user_website, email, read_stream, read_mailbox, user_checkins, publish_stream, publish_checkins'));
			}	
			else
			{
				// Get the goods
				$access_token		= $this->facebook_oauth->getAccessToken($_REQUEST['code']);					
				$facebook_user		= $this->facebook_oauth->get('/me');
				$check_connection	= $this->social_auth->check_connection_user_id($facebook_user->id, "facebook");
				
				// Added
				if ($check_connection)
				{
				 	$this->session->set_flashdata('message', "This Facebook account is already connected to another user");
				 	redirect('settings/connections', 'refresh');
				}
				else
				{					
					// Add Connection
			   		$connection_data = array(
			   			'site_id'				=> $this->module_site->site_id,
			   			'user_id'				=> $this->session->userdata('user_id'),
			   			'module'				=> 'facebook',
			   			'type'					=> 'primary',
			   			'connection_user_id'	=> $facebook_user->id,
			   			'connection_username'	=> $facebook_user->name,
			   			'auth_one'				=> $access_token
			   		);
			   							
					$connection = $this->social_auth->add_connection($connection_data);					
					
					$this->session->set_flashdata('message', "Facebook account connected");
				 	
				 	redirect('settings/connections', 'refresh');	
				}
			}
		}	
	}	
	
	function extended()
	{
		if (!config_item('facebook_extended')) redirect(base_url(), 'refresh');
		if (!$check_connection = $this->social_auth->check_connection_user_id($this->uri->segment(3))) redirect(base_url(), 'refresh');
		
		$user_update = FALSE;

		// SETS SESSION VALUES THAT ALLOWS Facebook API calls
		$this->facebook->getSessionDatabase($check_connection->auth_one);
		
		// GET USER INFO
		$facebook_user = $this->facebook->api('/me');
		
		// CONNECTION IS FROM EXISTING USER
		if ($check_connection->user_id != 0)
		{
			$user 			= $this->social_auth->get_user('user_id', $check_connection->user_id);
			$user_update 	= TRUE;
			$user_id		= $user->user_id;
		}
	
		// ELSE CREATE USER ACCOUNT AND UPDATE CONNECTION
		$username = url_username($facebook_user['name'], 'none', true);		
		
		// IMAGE IS BLANK GRAB FROM FBOOK
		if (($check_connection->user_id == 0) || ($user->image == ''))
		{
			// GET ALL PHOTO ALBUMS
			$get_albums = $this->facebook->api(array('query' => 'SELECT name, aid FROM album WHERE owner='.$facebook_user['id'], 'method' => 'fql.query'));    
			$album				= array();
			$album_id			= NULL;
			$profile_picture	= array();
			
			// FIND THE aid  		
			foreach ($get_albums as $album)
			{				
				if ($album['name'] == 'Profile Pictures')
				{
					$album_id = $album['aid'];				
					break;
				}
			}	
						
			// GET ALBUM Profile Pictures
			if ($album_id != '')
			{
				$get_profile_album = $this->facebook->api(array('query' => 'SELECT src_big FROM photo WHERE aid='.$album['aid'], 'method' => 'fql.query',));
										
				foreach ($get_profile_album as $profile_picture)
				{			
					$user_new_picture = $profile_picture['src_big'];
					break;					
				}

				if ($profile_picture['src_big'] != '')
				{
	        		$this->load->model('image_model');
	
	        		// Snatch Facebook Image
					$image_full		= $user_new_picture;
					$image_name		= $username.'.'.pathinfo($image_full, PATHINFO_EXTENSION);
	        		$image_save		= $image_name;
					$this->image_model->get_external_image($image_full, config_item('uploads_folder').$image_save);
	
					// Process New Images
					$image_size 	= getimagesize(config_item('uploads_folder').$image_save);
					$file_data		= array('file_name'	=> $image_save, 'image_width' => $image_size[0], 'image_height' => $image_size[1]);
					$image_sizes	= array('full', 'large', 'medium', 'small');
					$create_path	= config_item('users_images_folder').$user_id.'/';
	
					$this->image_model->make_images($file_data, 'users', $image_sizes, $create_path, TRUE);					
				}
			}
		}
		
		if ($user_update)
		{
	    	$update_data = array(
				'name' 		 	=> $facebook_user['name'],
				'email'			=> $facebook_user['email'],
				'location'	 	=> $facebook_user['location']['name'],
				'bio' 		 	=> '',
				'url'	 	 	=> $facebook_user['link'],
				'image'		 	=> $image_name,
				'home_base'		=> 'facebook',
				'language'		=> config_item('languages_default'),
				'time_zone'		=> $time_zone,
				'geo_enabled'	=> 0,
				'utc_offset' 	=> $utc_offset				        	
			);

	    	$this->social_auth->update_user($this->session->userdata('user_id'), $update_data);
		}
		// IS A NEW USER SO CREATE
		else
		{					
			// CONVERTS FACEBOOK TIMEZONE TO STANDARD
			foreach(timezones() as $key => $zone)
			{
				if ($facebook_user['timezone'] === $zone) $time_zone = $key;						
			}
			
	    	$utc_offset	= $facebook_user['timezone'] * 60 * 60;
	    						
	    	$additional_data = array(
				'name' 		 	=> $facebook_user['name'],
				'location'	 	=> $facebook_user['location']['name'],
				'bio' 		 	=> '',
				'url'	 	 	=> $facebook_user['link'],
				'image'		 	=> $image_name,
				'home_base'		=> 'facebook',
				'language'		=> config_item('languages_default'),
				'time_zone'		=> $time_zone,
				'geo_enabled'	=> 0,
				'utc_offset' 	=> $utc_offset
			);

	    	$created_user_id = $this->social_auth->social_register($username, $additional_data);
	    	
	  		$user_id = $created_user_id;				
		}
		
    	if ($user_id)
    	{
			// LOG USER IN WITH SOCIAL
        	if ($this->social_auth->social_login('facebook', $user_id, $check_connection->access_token, $check_connection->secret, $check_connection->connection_password)) 
        	{
    			$this->session->set_flashdata('message', "User created and logged in");
	        	redirect(base_url().'home', 'refresh');
	        }
	        else 
	        {
	        	$this->session->set_flashdata('message', "Login with Facebook in-correct");
	        	redirect("login", 'refresh');
	        }
   		}
   		else
   		{
    		$this->session->set_flashdata('message', "Error creating user & logging in");
    		redirect("login", 'refresh');
   		}		
	}	

}