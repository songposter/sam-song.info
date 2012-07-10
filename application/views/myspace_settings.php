<?php header('Content-type: text/html; charset=UTF-8'); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>SAM Song Info Poster<?php echo (ENVIRONMENT != 'production') ? ' Testing' : ''; ?> - MySpace Settings</title>
		<script src="<?php echo $base?>js/jquery.js"" type="text/javascript"></script>
		<script src="<?php echo $base?>js/jquery-ui-1.8.16.custom.min.js" type="text/javascript"></script>
		<script src="<?php echo $base?>lib/display.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $base?>lib/layout.css" />
		<link rel="shortcut icon" href="<?php echo $base?>favicon.ico" type="image/x-icon" />
	</head>
	<body>
		<div class="intro">
    		<img src="<?php echo $base ?>images/sam_64.png" alt="SAM Broadcaster Logo" />
    		<img src="<?php echo $base ?>images/myspace_64.png" alt="MySpace Logo" />
    		<a class="logout orange" href="/logout">Logout</a>
    		<h1>SAM <span class="orange">Broadcaster</span> Song Info Poster<?php echo (ENVIRONMENT != 'production') ? ' Testing' : ''; ?>: <span class="facebook-blue">MySpace</span> Settings</h1>
		</div>

		<?php if ('' !== validation_errors()):?>
		<div class="section">
			<h2>Errors</h2>
		    <?php echo validation_errors()?>
		</div>
		<?php endif;?>

		<?php echo form_open($this_url)?>
		    <?php echo form_fieldset('Basic', array('class' => 'collapseable expanded', 'id' => 'basic'))?>
				<p>Basic settings needed to generate a custom PAL script for you.
					<span class="emphasize">All fields required</span>, but you're good to go with the default values.
					<a class="expandbutton">Show/Hide Options</a></p>
		        <?php echo form_fieldset('Songtypes', array('class' => 'nostyle'))?>
                    <?php echo form_multiselect('songtypes[]', $songtypes, array('S'), 'id="songtypes"')?>
					<?php echo form_label('Select songtypes to be sent (Drag or hold ctrl when clicking to select multiple)', 'songtypes')?>
				</fieldset>
                <?php echo form_fieldset('Post Timing', array('class' => 'nostyle'))?>
                    <?php
                        foreach ($timings as $key => $value)
                        {
            				$timings_radio = '<input type="radio" class="radio" name="timing"  id="'.$key.'" value="'.$key.'"'.set_radio('timing', $key).' />';
            			    echo form_label($timings_radio.$value, $key);
                        }
                    ?>
                </fieldset>
                <?php echo form_fieldset('Interval', array('class' => 'nostyle'))?>
					<input type="text" name="timing_value" id="timing_value" value="<?php echo set_value('timing_value', $timing_value)?>" maxlength="2" />
                    <?php echo form_label('Skip all songs within the specified interval', 'timing_value')?>
                </fieldset>
			</fieldset>
			<?php echo form_fieldset('Advanced', array('class' => 'collapseable expanded', 'id' => 'advanced'))?>
				<p>Slightly more advanced settings regarding the layout of your posts on MySpace.
					If you leave this section closed your previously stored settings will be used.
					Default behavior on first setup is to only post {Artist} - {Title}
					<a class="expandbutton">Show/Hide Options</a></p>
				<?php echo form_fieldset('Prefix', array('class' => 'nostyle'))?>
					<input type="text" name="prefix" id="prefix" value="<?php echo set_value('prefix', $prefix)?>" />
					<?php echo form_label('Text in front of the actual Song Info (ex. Now Playing)', 'prefix')?>
				</fieldset>
				<?php echo form_fieldset('Postfix', array('class' => 'nostyle'))?>
					<input type="text" name="postfix" id="postfix" value="<?php echo set_value('postfix', $postfix)?>" />
					<?php echo form_label('Text behind the actual Song Info (ex. presented by station)', 'postfix')?>
				</fieldset>
				<?php echo form_fieldset('Fieldorder', array('class' => 'nostyle'))?>
					<ul id="sortable">
                    	<li class="ui-state-default" id="artist">Artist</li>
                    	<li class="ui-state-default" id="title">Title</li>
                    </ul>
				    <input type="hidden" name="field_order" id="field_order" value="<?php echo set_value('field_order', 'artist|title')?>" />
				    <?php echo form_label('Order the SongInfo Fields by drag and drop (requires JavaScript)', 'field_order')?>
				</fieldset>
			</fieldset>
			<?php echo form_fieldset('Website', array('class' => 'collapseable expanded', 'id' => 'website'))?>
				<p>Attach a link to your website and include a longer description of your station.
					If you leave this section closed your previously stored settings will be used.
					Default behavior on first setup is no website attached.
				<a class="expandbutton">Show/Hide Options</a></p>
				<?php echo form_fieldset('Title', array('class' => 'nostyle'))?>
					<input type="text" name="website_title" id="website_title" value="<?php echo set_value('website_title', $website_title)?>" />
					<?php echo form_label('The name of your station', 'website_title')?>
				</fieldset>
				<?php echo form_fieldset('Link', array('class' => 'nostyle'))?>
					<input type="text" name="website_link" id="website_link" value="<?php echo set_value('website_link', $website_link)?>" />
					<?php echo form_label('URL (webaddress) of your website', 'website_link')?>
				</fieldset>
				<?php echo form_fieldset('Description', array('class' => 'nostyle'))?>
					<textarea name="website_description" id="website_description" cols="30" rows="4"><?php echo set_value('website_description', $website_description)?></textarea>
					<?php echo form_label('Describe your station in short words', 'website_description')?>
				</fieldset>
			</fieldset>
			<?php echo form_fieldset('Album Art', array('class' => 'collapseable expanded', 'id' => 'artwork'))?>
				<p>Show Album Art for the Song
					Uses the pictures from SAM if you have the PHP/HTMLweb Template set up
					If you leave this section closed your previously stored settings will be used.
					Default behavior on first setup is no pictures.
					<a class="expandbutton">Show/Hide Options</a></p>
				<?php echo form_fieldset('Picturedir', array('class' => 'nostyle'))?>
					<input type="text" name="picture_dir" id="picture_dir" value="<?php echo set_value('picture_dir', $picture_dir)?>" />
					<?php echo form_label('Webaddress to a folder containing album pictures (with trailing /)', 'picture_dir')?>
				</fieldset>
			</fieldset>
			<?php echo form_fieldset('', array('class' => 'nostyle'));?>
				<?php echo form_submit('submit', 'Save Settings').' '.form_reset('reset', 'Reset'); ?>
			</fieldset>
			<fieldset class="hidden">
				<input type="hidden" name="advancedchanged" id="advancedchanged" value="0" />
				<input type="hidden" name="websitechanged" id="websitechanged" value="0" />
				<input type="hidden" name="artworkchanged" id="artworkchanged" value="0" />
				<input type="hidden" name="basicchanged" id="basicchanged" value="0" />
			</fieldset>
		</form>