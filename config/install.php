<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:			Social Igniter : Facebook : Install
* Author: 		Brennan Novak
* 		  		contact@social-igniter.com
*         		@brennannovak
*          
* Created: 		Brennan Novak
*
* Project:		http://social-igniter.com/
* Source: 		http://github.com/socialigniter/facebook
*
* Description: 	Install values for Facebook App for Social Igniter 
*/
/* Settings */
$config['facebook_settings']['widgets'] 			= 'TRUE';
$config['facebook_settings']['categories'] 			= 'FALSE';
$config['facebook_settings']['enabled']				= 'TRUE';
$config['facebook_settings']['app_id'] 				= '';
$config['facebook_settings']['api_key'] 			= '';
$config['facebook_settings']['secret_key'] 			= '';
$config['facebook_settings']['social_login'] 		= 'TRUE';
$config['facebook_settings']['social_connection'] 	= 'FALSE';
$config['facebook_settings']['social_post'] 		= 'TRUE';
$config['facebook_settings']['auto_publish'] 		= 'FALSE';
$config['facebook_settings']['archive'] 			= 'TRUE';
$config['facebook_settings']['login_redirect']		= '';
$config['facebook_settings']['connections_redirect']= '';
$config['facebook_settings']['extended_options'] 	= 'offline_access, user_about_me, user_activities, user_events, user_interests, user_likes, user_location, user_website, email, read_stream, read_mailbox, user_checkins, publish_stream, publish_checkins';

/* Sites */
$config['facebook_sites'][] = array(
	'url'		=> 'http://facebook.com/', 
	'module'	=> 'facebook', 
	'type' 		=> 'remote', 
	'title'		=> 'Facebook', 
	'favicon'	=> 'http://static.ak.fbcdn.net/rsrc.php/yi/r/q9U99v3_saj.ico'
);