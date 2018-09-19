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
                SongPoster connects your <a href="https://store.spacial.com/545/cookie?affiliate=27245&redirectto=http%3a%2f%2fspacial.com%2fsam-broadcaster" rel="external" class="hiddenlink" target="_blank">SAM</a> to popular social networks, posting the tracks your station plays.<br />
            </p>
        </div>
        <div class="section">
            <h1 class="orange">Relaunch in 2017</h1>
            <p>
                Thanks for your interest in this service and the support over the last 7 years since starting this project.<br />
                After adding more and more features to Facebook over the first years, we had to drop that platform due to policy changes.<br />
                Now we're glad to announce that Facebook support will return and with Google+ we'll even add another new platform for your stations.<br />
                <br />
                We plan to release a new application to the public before the end of this year.<br />
                This webinterface will be shut down closely thereafter, but we'll keep the posting/PAL API around for some more.<br />
                <br />
                If you'd like to be notified when the new site is donw, <a href="http://eepurl.com/bGU2mL" target="_blank" rel="external">sign up here</a> and we'll send you an email when we're ready.<br />
                In order to keep yourself up-to-date, please check out our <a href="https://www.facebook.com/songposter" target="_blank" rel="external">Facebook</a> and <a href="https://twitter.com/samsonginfo" target="_blank" rel="external">Twitter</a> accounts where we regularly post updates on the relaunch progress.
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

<!--        <div class="section">
            <img src="<?php echo $base?>images/facebook_64.png" />
            <h2>Facebook</h2>
            <p>
                We're really sorry, but facebook will not be available here anymore.<br />
                At first this was only a technical problem and with enough time it would've probably been possible to return, <br />
                however facebook changed their rules for developers dramatically over the last few years. <br /> 
                In fact all automated posting is now forbidden for anyone but large platform partners (which we're not)<br />
            </p>
        </div>
-->
<div id="fb-root"></div>
<!-- <script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId=364278193648166";
      fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script> -->
