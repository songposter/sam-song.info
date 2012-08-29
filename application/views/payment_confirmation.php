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
			<a href="<?php echo $base?>"><img src="<?php echo $base?>images/sam_64.png" /></a>
			<a class="logout orange" href="/logout">Logout</a>
			<h1>SAM <span class="orange">Broadcaster</span> Song Info Poster<?php echo (ENVIRONMENT != 'production') ? ' Testing' : ''; ?></h1>
			<p>
				Thanks for your donation.<br />
				Your donation of <?php echo $mc_gross.' '.$mc_currency ?> for the Account Number <?php echo $item_name ?> was received on <?php echo $payment_date ?>.<br />
				The current status of your transaction is <?php echo $payment_status?>.<br />
				Page Posting for your Facebook Account <?php echo ($ispage == 1) ? 'is already enabled' : 'will be enabled when your donation was reviewed by me.' ?><br />
				If you have questions regarding any aspects of this application, feel free to send me a message to <a href="mailto:support@sam-song.info">support@sam-song.info</a>
			</p>
		</div>