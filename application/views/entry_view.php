<?php header('Content-type: text/html; charset=UTF-8'); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo facebook_xmlns()?>>
	<head>
		<title>SAM Song Info Poster<?php echo (ENVIRONMENT != 'production') ? ' Testing' : ''; ?></title>
		<link rel="stylesheet" type="text/css" href="<?php echo $base?>lib/layout.css" />
		<link rel="shortcut icon" href="<?php echo $base?>favicon.ico" />
		<?php if (!empty($opengraph)):?>
			<?php echo facebook_opengraph_meta($opengraph);?>
		<?php endif;?>
	</head>
	<body>
		<div class="section">
			<img src="<?php echo $base?>images/sam_64.png" />
			<a class="logout orange" href="/logout">Logout</a>
			<h1>SAM <span class="orange">Broadcaster</span> Song Info Poster<?php echo (ENVIRONMENT != 'production') ? ' Testing' : ''; ?></h1>
			<p>
				Song Info Poster connects your <a href="http://spacialaudio.com/?page=sam-broadcaster&ref=A3773&redirect=SAM_Broadcaster" rel="external" class="hiddenlink" target="_blank">SAM</a> to popular social networks Facebook, Twitter and MySpace.<br />
				The basic functionality included in both networks is sending messages to either or both platforms,<br />
				mentioning Artist and Title that just played on your station.
			</p>
			<p>
				Default features for all networks include standard texts you can attach to the plain Song Info<br />
				and automatic detection of Ads, Liners etc. which will not be announced to your followers.
			</p>
			<p>
				For Network specific features, registration and settings please see the corresponding section below.
			</p>
		</div>

		<div class="section">
			<img src="<?php echo $base?>images/twitter_64.png" />
			<h2>Twitter</h2>
			<p>
				Brilliant in simplicity &mdash; mere 140 characters that mean the world. <br/>
				The service supports static text &ldquo;glued&rdquo; to the front and back of the default "Artist - Title" message.
			</p>
			<a class="login" href="twitter/<?php echo $twitter_loggedin ? 'settings' : 'login'?>">
				<img src="<?php echo $base?>images/<?php echo $twitter_loggedin ? 'settings' : 'login'?>_32TW.png" />
			</a>
		</div>

		<div class="section">
			<img src="<?php echo $base?>images/facebook_64.png" />
			<h2>Facebook</h2>
			<p>
				Millions of friends just a click away &mdash; the biggest social network today, even beating ex-leader MySpace.<br />
				This networks feature set is optionally extended by cover art from SAM's Web Feature (PHP/HTML),<br />
				a link to your station's homepage including a multilined description of it and a fully customizable Action link.<br />
				All the possibilities are rounded off with an online settings dialog that remembers your preferences<br />
				and live preview of how the wall post would look with your current settings.
			</p>

		<?php if ($facebook_loggedin): ?>
			<span class="login">
				<a href="facebook/settings"><img src="<?php echo $base?>images/settings_32.png" /></a>
				<fb:like class="like"></fb:like>
			</span>
		<?php else: ?>
			<span class="login">
				<a href="facebook/login"><img src="<?php echo $base?>images/login_32.png" /></a>
				<fb:facepile class="facepile"></fb:facepile>
			</span>
		<?php endif; ?>
		</div>

		<div id="fb-root"></div>
		<script src="https://connect.facebook.net/en_US/all.js" type="text/javascript"></script>
		<script type="text/javascript">
			FB.init({appId: '<?php echo facebook_app_id()?>', status: true, cookie: true, xfbml: true});
			FB.Event.subscribe('auth.login', function(response) {
				window.location.reload();
			});
		</script>
		<script type="text/javascript">$.ready(function () {
			FB.Canvas.setDoneLoading();
			});</script>