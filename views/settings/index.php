<form name="settings_update" id="settings_update" method="post" action="<?= base_url() ?>api/settings/modify" enctype="multipart/form-data">

<div class="content_wrap_inner">

	<div class="content_inner_top_right">
		<h3>Module</h3>
		<p><?= form_dropdown('enabled', config_item('enable_disable'), $settings['facebook']['enabled']) ?></p>
	</div>
	
	<h3>Application Keys</h3>

	<p>Facebook requires <a href="http://www.facebook.com/developers/" target="_blank">registering your application</a></p>
				
	<p><input type="text" name="api_key" value="<?= $settings['facebook']['api_key'] ?>"> API Key </p> 
	<p><input type="text" name="secret_key" value="<?= $settings['facebook']['secret_key'] ?>"> Secret Key</p>

</div>

<span class="item_separator"></span>

<div class="content_wrap_inner">

	<h3>Setup</h3>

	<p>Sign In
	<?= form_dropdown('social_login', config_item('yes_or_no'), $settings['facebook']['social_login']) ?>
	</p>

	<p>Connections
	<?= form_dropdown('social_connection', config_item('yes_or_no'), $settings['facebook']['social_connection']) ?>
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

	<input type="hidden" name="module" value="facebook">

	<p><input type="submit" name="save" value="Save" /></p>

</div>

</form>

<?= $shared_ajax ?>