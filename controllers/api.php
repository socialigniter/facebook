<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* 
 * Facebook API : Module : Social-Igniter
 *
 */
class Api extends Oauth_Controller
{
	protected $module_site;

    function __construct()
    {
        parent::__construct(); 
		
		// Get Site Facebook
		$this->module_site = $this->social_igniter->get_site_view_row('module', 'facebook');		
	}
	
	function install_authd_get()
	{
		// Load
		$this->load->library('installer');
		$this->load->config('install');        

		// Settings & Create Folders
		$settings = $this->installer->install_settings('facebook', config_item('facebook_settings'));
	
		// Site
		$site = $this->installer->install_sites(config_item('facebook_sites'));
	
		if ($settings == TRUE AND $site == TRUE)
		{
            $message = array('status' => 'success', 'message' => 'Yay, the Facebook App was installed');
        }
        else
        {
            $message = array('status' => 'error', 'message' => 'Dang Facebook App could not be uninstalled');
        }		
		
		$this->response($message, 200);
	}

	function reinstall_authd_get()
	{
		// Load
		$this->load->library('installer');
		$this->load->config('install');        

		// Settings & Create Folders
		$settings = $this->installer->install_settings('facebook', config_item('facebook_settings'), TRUE);

		if ($settings == TRUE)
		{
            $message = array('status' => 'success', 'message' => 'Yay, the Facebook App was reinstalled');
        }
        else
        {
            $message = array('status' => 'error', 'message' => 'Dang Facebook App could not be uninstalled');
        }		
		
		$this->response($message, 200);
	}

	function social_post_authd_post()
	{
		if ($connection = $this->social_auth->check_connection_user($this->oauth_user_id, 'facebook', 'primary'))
		{	
			// Load Library
			$facebook_config = array(
				'client_id' 	=> config_item('facebook_app_id'),
				'client_secret'	=> config_item('facebook_secret_key'),
				'callback_url'	=> base_url().'connections/facebook',
				'access_token'	=> $connection->auth_one
			);			
					
			$this->load->library('facebook_oauth', $facebook_config);	

			// Uses Short URL if exists
			if ($this->input->post('short_url'))
			{
				$post_url = $this->input->post('short_url');
			}
			else
			{
				$post_url = base_url().$this->input->post('module').'/view/'.$this->input->post('content_id');				
			}

			// Uses Short URL if exists						
			if ($this->input->post('short_url')):
				$post_url = $this->input->post('short_url');
			else:
				$post_url = $this->input->post('long_url');				
			endif;

			// Wall Post Data
			$wall_post = array(
				'message'	=> truncator($this->input->post('content'), 140),
				'link'		=> $post_url,
				'name'		=> truncator($this->input->post('content'), 45),
				'caption'	=> ucwords($this->input->post('module')).' by '.$this->input->post('author')
			);

			$wall_post = $this->facebook_oauth->post($connection->connection_user_id.'/feed', $wall_post);

			if (isset($wall_post->id))
			{
				// Add to Meta
				$post_result = explode('_', $wall_post->id);

				$content_meta = array(
					'site_id'		=> $this->module_site->site_id,
					'content_id'	=> $this->input->post('content_id'),
					'meta'			=> 'facebook_wall_post_id',
					'value'			=> $post_result[1]
				);

				$this->social_igniter->add_meta($content_meta);

				$message = array('status' => 'success', 'message' => 'Posted to Facebook successfully', 'data' => $wall_post);
			}
			else
			{
				$message = array('status' => 'error', 'message' => 'Could not post message to Facebook', 'data' => $wall_post);			
			}
		}
		else
		{
			$message = array('status' => 'error', 'message' => 'No Facebook account connected to user');
		}
	
	    $this->response($message, 200);
	}
	
	function social_comments_get()
	{

		if ($connection = $this->social_auth->check_connection_user(1, 'facebook', 'primary'))
		{	
			// Load Library
			$facebook_config = array(
				'client_id' 	=> config_item('facebook_app_id'),
				'client_secret'	=> config_item('facebook_secret_key'),
				'callback_url'	=> base_url().'connections/facebook',
				'access_token'	=> $connection->auth_one
			);			
					
			$this->load->library('facebook_oauth', $facebook_config);	
		
		
			
					
			$comments = $this->facebook_oauth->get('10151186868398918');
		
			echo '<pre>';
			print_r($comments);
			echo '</pre>';
		
		}
		else
		{
			echo 'No tokens found';
			
		}		
		
		
		
		
		
	}

}