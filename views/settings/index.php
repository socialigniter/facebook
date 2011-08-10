<form name="settings_update" id="settings_update" method="post" action="<?= base_url() ?>api/settings/modify" enctype="multipart/form-data">

<div class="content_wrap_inner">

	<div class="content_inner_top_right">
		<h3>App</h3>
		<p><?= form_dropdown('enabled', config_item('enable_disable'), $settings['facebook']['enabled']) ?></p>
		<p><a href="<?= base_url() ?>api/<?= $this_module ?>/reinstall" id="app_reinstall" class="button_action">Reinstall</a>
		<a href="<?= base_url() ?>api/<?= $this_module ?>/uninstall" id="app_uninstall" class="button_delete">Uninstall</a></p>
	</div>
	
	<h3>Application Keys</h3>

	<p>Facebook requires <a href="http://www.facebook.com/developers/" target="_blank">registering your application</a></p>
				
	<p><input type="text" name="app_id" value="<?= $settings['facebook']['app_id'] ?>"> App ID</p>
	<p><input type="text" name="api_key" value="<?= $settings['facebook']['api_key'] ?>"> API Key </p> 
	<p><input type="text" name="secret_key" value="<?= $settings['facebook']['secret_key'] ?>"> Secret Key</p>
	
	<p>Extended Options</p>
	<p><input type="text" name="extended_options" class="input_large" value="<?= $settings['facebook']['extended_options'] ?>"></p>

</div>

<span class="item_separator"></span>

<div class="content_wrap_inner">

	<h3>Setup</h3>

	<p>Login
	<?= form_dropdown('social_login', config_item('yes_or_no'), $settings['facebook']['social_login']) ?>
	</p>
	
	<p>Login Redirect<br>
	<?= base_url() ?> <input type="text" size="30" name="login_redirect" value="<?= $settings['facebook']['login_redirect'] ?>" />
	</p>	

	<p>Connections
	<?= form_dropdown('social_connection', config_item('yes_or_no'), $settings['facebook']['social_connection']) ?>
	</p>

	<p>Connections Redirect<br>
	<?= base_url() ?> <input type="text" size="30" name="connections_redirect" value="<?= $settings['facebook']['connections_redirect'] ?>" />
	</p>	

	<p>Post
	<?= form_dropdown('social_post', config_item('yes_or_no'), $settings['facebook']['social_post']) ?>	
	</p>

	<p>Archive
	<?= form_dropdown('archive', config_item('yes_or_no'), $settings['facebook']['archive']) ?>	
	</p>

	<p>Auto Publish
	<?= form_dropdown('auto_publish', config_item('yes_or_no'), $settings['facebook']['auto_publish']) ?>
	</p>

	<p><a href="#">Connect</a> a Facebook account for this site to generate automatic updates.</p>

	<input type="hidden" name="module" value="<?= $this_module ?>">

	<p><input type="submit" name="save" value="Save" /></p>

</div>

</form>

<?= $shared_ajax ?>