        <div class="section">
            <img src="<?php echo $base?>images/paypal_64.png" />
            <h2>Donations</h2>
            <p>
                This service is provided for free on an &ldquo;as is&rdquo; basis as described above.<br />
                If you like this service though, please consider donating a small amount via PayPal.<br />
                The sourcecode is available for free under the &ldquo;<a class="hiddenlink" href="<?php echo $base?>license">do what the fuck you want to public license v2</a>&rdquo;<br />
                It can be downloaded at the <a class="hiddenlink" href="https://github.com/mastacheata/song-poster">public GitHub repository</a>, though no assistance or support of any kind is given.
            </p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <fieldset class="nostyle">
                    <input type="hidden" name="cmd" value="_s-xclick" />
                    <input type="hidden" name="hosted_button_id" value="6MD7BXRHSGGWU" />
                <?php if (isset($userid) && isset($locale)): ?>
                    <input type="hidden" name="item_name" value="<?php echo $userid?>">
                    <input type="hidden" name="item_number" value="<?php echo $userid?>">
                    <input type="hidden" name="lc" value="<?php echo $locale?>">
                <?php endif;?>
                    <input type="image" src="<?php echo $base?>images/donate_32.png"  name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen mit PayPal." />
                    <img alt="" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1" />
                </fieldset>
            </form>
        </div>

        <div class="credits">
            <p>
                The copyright for all content within this website including the facebook and twitter application is held by Benedikt Bauer - Oktavianstraße 19 - 50968 Köln - Germany.<br />
                Copyright for the SAM Logo in the Twitter and Facebook Directories is held by <a href="https://store.spacial.com/545/cookie?affiliate=27245&redirectto=http%3a%2f%2fspacial.com%2f" rel="external" class="hiddenlink" target="_blank">SpacialAudio Solutions LLC</a> and used with kind permission.<br />
                The developer of this application is not affiliated with Spacial Audio Solutions LLC and the applications are neither developed by nor officially supported by them.
            </p>
        </div>
        <script type="text/javascript">
            var uvOptions = {};
            (function() {
                var uv = document.createElement('script'); uv.type = 'text/javascript'; uv.async = true;
                uv.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'widget.uservoice.com/aOrObFAKDYc7FPWQACYmg.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(uv, s);
            })();
        </script>
    </body>
</html>
