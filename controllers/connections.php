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
			'access_token'	=> NULL
		);
			
		$this->load->library('facebook_oauth', $facebook_config);
		
		$this->module_site = $this->social_igniter->get_site_view_row('module', 'facebook');		   		
	}
			
	function index()
	{		
		// Is Logged In
		if ($this->social_auth->logged_in()) redirect('connections/facebook/add');
	
		$me 				= NULL;
		$album				= NULL;
		$profile_picture	= NULL;

		// Go to Facebook
		if (!isset($_GET['code']))
		{
			redirect('http://www.facebook.com/dialog/oauth?client_id='.config_item('facebook_app_id')."&display=popup&method=permissions.request&redirect_uri=".urlencode(base_url().'connections/facebook').'&scope='.config_item('facebook_extended_options'));
		}				
		else
		{			
			// Get the goods
			$access_token		= $this->facebook_oauth->getAccessToken($_GET['code']);
			$facebook_user		= $this->facebook_oauth->get('/me');
	
			// Error Redirect
			if (!isset($facebook_user->id)) redirect('signup', 'refresh');
			
			$check_connection	= $this->social_auth->check_connection_user_id($facebook_user->id, 'facebook');

			// Check Connection
			if ($check_connection)
			{					
				// Login
				if ($this->social_auth->social_login($check_connection->user_id, 'facebook')) 
	        	{ 
		        	$this->session->set_flashdata('message', 'Login with Facebook Success');
		        	redirect(login_redirect(config_item('facebook_login_redirect')), 'refresh');
		        }
		        else 
		        { 
		        	$this->session->set_flashdata('message', 'Login with Facebook Did Not Work');
		        	redirect('login', 'refresh');
		        }
			}
			else
			{
				// Email
				if (property_exists($facebook_user, 'email')) $email = $facebook_user->email;
				else $email = $facebook_user->username.'@facebook.com';

				// Check Exisitng
				if ($user_check = $this->social_auth->get_user('email', $email))
				{
					// Set
					$user_id = $user_check->user_id;
					
					// Username
					if (property_exists($facebook_user, 'username')) $username = $facebook_user->username;
					else $username = $facebook_user->id;	
									
					// Add Connection
			   		$connection_data = array(
			   			'site_id'				=> $this->module_site->site_id,
			   			'user_id'				=> $user_id,
			   			'module'				=> 'facebook',
			   			'type'					=> 'primary',
			   			'connection_user_id'	=> $facebook_user->id,
			   			'connection_username'	=> $username,
			   			'auth_one'				=> $access_token
			   		);

					$connection = $this->social_auth->add_connection($connection_data);
				}
				else
				{	
					// Username
					if (property_exists($facebook_user, 'username')) $username = $facebook_user->username;
					else $username = url_username($facebook_user->name, 'none', true);
					
					// Picture
					if ($profile_picture = $this->facebook_oauth->getProfilePictureUrl($facebook_user->id)) $picture = $username.'.jpg';
					else $picture = '';

					// Convert Time
					if (property_exists($facebook_user, 'timezone'))
					{					
						foreach(timezones() as $key => $zone)
						{
							if ($facebook_user->timezone === $zone) $time_zone = $key;						
						}						
					}
					else
					{
						$time_zone	= '';
					}

					// Create User
			    	$additional_data = array(
						'name' 		 	=> $facebook_user->name,
						'image'		 	=> $picture,
						'language'		=> config_item('languages_default'),
						'time_zone'		=> $time_zone,
						'geo_enabled'	=> 0,
						'connection'	=> 'Facebook'
			    	);
			    			       			      				
			    	// Register User
			  		$user_id = $this->social_auth->social_register($username, $email, $additional_data);
		        	
		        	if ($user_id)
		        	{
		        		$user_meta_data = array();
		        	
						// Add Meta
						if (property_exists($facebook_user, 'location'))
						{	
							$user_meta_data['location']	= $facebook_user->location->name;
						}

						if (property_exists($facebook_user, 'link'))
						{							
							$user_meta_data['url'] = $facebook_user->link;
						}
						
						$this->social_auth->update_user_meta(config_item('site_id'), $user_id, 'users', $user_meta_data);					
						
						// Add Connection
				   		$connection_data = array(
				   			'site_id'				=> $this->module_site->site_id,
				   			'user_id'				=> $user_id,
				   			'module'				=> 'facebook',
				   			'type'					=> 'primary',
				   			'connection_user_id'	=> $facebook_user->id,
				   			'connection_username'	=> $username,
				   			'auth_one'				=> $access_token
				   		);
				   							
						$connection = $this->social_auth->add_connection($connection_data);
						
			    		// Process Image	        	
						if ($profile_picture)
			    		{
			        		$this->load->model('image_model');

							// Delete / Make Folder
							$create_path	= config_item('users_images_folder').$user_id.'/';
							delete_files($create_path);
							make_folder($create_path);

			        		// Get Facebook Image
							$this->image_model->get_external_image($profile_picture, $create_path.$picture);

							// Make Sizes
							$this->image_model->make_thumbnail($create_path, $picture, 'users', 'small');
						}
		       		}
		       		else
		       		{
		        		$this->session->set_flashdata('message', 'Error creating user & logging in');
		        		redirect('login', 'refresh');
		       		}		       	
		       	}	
		       		
				// Login
				if ($this->social_auth->social_login($user_id, 'facebook'))
	        	{
        			$this->session->set_flashdata('message', 'User created and logged in');
		        	redirect(login_redirect(config_item('facebook_login_redirect')), 'refresh');
		        }
		        else 
		        {
		        	$this->session->set_flashdata('message', 'Login with Facebook in-correct');
		        	redirect('login', 'refresh');
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
			redirect(connections_redirect(config_item('facebook_connections_redirect')), 'refresh');
		}
		else
		{
			// Not Set go to Facebook
			if (!isset($_GET['code']))
			{	
				redirect($this->facebook_oauth->getAuthorizeUrl(config_item('facebook_extended_options')));
			}	
			else
			{			
				// Get the goods
				$access_token		= $this->facebook_oauth->getAccessToken($_GET['code']);					
				$facebook_user		= $this->facebook_oauth->get('/me');

				// Error Redirect
				if (!isset($facebook_user->id))
				{
				 	$this->session->set_flashdata('message', 'Oops, could not get account data for that Facebook account');
					redirect(connections_redirect(config_item('facebook_connections_redirect')), 'refresh');
				}

				// Check
				$check_connection	= $this->social_auth->check_connection_user_id($facebook_user->id, "facebook");
				
				// Added
				if ($check_connection)
				{
				 	$this->session->set_flashdata('message', 'This Facebook account is already connected to another user');
				 	redirect(connections_redirect(config_item('facebook_connections_redirect')), 'refresh');
				}
				else
				{
					// Username
					if (property_exists($facebook_user, 'username')) $username = $facebook_user->username;
					else $username = $facebook_user->id;					
								
					// Add Connection
			   		$connection_data = array(
			   			'site_id'				=> $this->module_site->site_id,
			   			'user_id'				=> $this->session->userdata('user_id'),
			   			'module'				=> 'facebook',
			   			'type'					=> 'primary',
			   			'connection_user_id'	=> $facebook_user->id,
			   			'connection_username'	=> $username,
			   			'auth_one'				=> $access_token
			   		);

					$connection = $this->social_auth->add_connection($connection_data);

					$this->social_auth->set_userdata_connections($this->session->userdata('user_id'));

					$this->session->set_flashdata('message', 'Facebook account connected');

				 	redirect(connections_redirect(config_item('facebook_connections_redirect')), 'refresh');
				}
			}
		}	
	}

}