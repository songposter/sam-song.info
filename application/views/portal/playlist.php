<?php header('Content-type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html <?php echo facebook_xmlns()?>>
    <head>
        <title><?php echo $user; ?><?php echo (ENVIRONMENT != 'production') ? ' Testing' : ''; ?></title>
        <!-- <link rel="stylesheet" type="text/css" href="<?php echo $base?>lib/layout.css" />
        <link rel="shortcut icon" href="<?php echo $base?>favicon.ico" /> -->
        <?php if (!empty($opengraph)):?>
            <?php echo facebook_opengraph_meta($opengraph);?>
        <?php endif;?>
    </head>
    <body>
        <div class="navigation">
            <?php foreach ($navigation as $item): ?>
                <?=$item ?>
            <?php endforeach; ?>
        </div>
        <div class="playlist">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Picture</th>
                        <th>Artist</th>
                        <th>Title</th>
                        <th>Album</th>
                    </tr>
                    <tr>
                        <th colspan="5"><?=$pagination; ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th colspan="5"><?=$pagination; ?></th>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Picture</th>
                        <th>Artist</th>
                        <th>Title</th>
                        <th>Album</th>
                    </tr>
                </tfoot>
                <tbody>
                <?php foreach ($songinfos as $song): ?>
                    <tr>
                        <td><?=$song->ID; ?></td>
                        <td><?=$song->picture;?></td>
                        <td><a href="<?=$base.'portal/'.$user.'/artist/'.urlencode($song->artist); ?>"><?=$song->artist;?></a></td>
                        <td><a href="<?=$base.'portal/'.$user.'/song/'.$song->ID;?>"><?=$song->title;?></a></td>
                        <td><a href="<?=$base.'portal/'.$user.'/album/'.urlencode($song->artist).'___---'.urlencode($song->album); ?>"><?=$song->album;?></a></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
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
            });
        </script>
    </body>
</html>