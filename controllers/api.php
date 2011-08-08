<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* 
 * Facebook API : Module : Social-Igniter
 *
 */
class Api extends Oauth_Controller
{

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
			
			// Wall Post Data
			$wall_post = array(
				'message'		=> $this->input->post('content')
			);
			
			$wall_post = $this->facebook_oauth->post($connection->connection_user_id.'/feed', $wall_post);
	
			$message = array('status' => 'success', 'message' => 'Posted to Facebook successfully', 'data' => $wall_post);
		}
		else
		{
			$message = array('status' => 'error', 'message' => 'No Facebook account connected to user');
		}
	
	    $this->response($message, 200);
	}

}