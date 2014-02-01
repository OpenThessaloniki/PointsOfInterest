<?php

$themename = "Grid";
$shortname = "grid";
$settings_list = array(
    'custom_logo_url',
    'custom_background_color',
//    'disable_slideshow',
//    'slideshow_timeout',
    'facebook_link',
    'twitter_link',
    'dribbble_link',
    'google_plus_link',
    'post_hover_background_color'
);


/*function add_homestead_scripts() {
//  wp_enqueue_script('homestead-settings1', WP_CONTENT_URL.'/themes/homestead/jquery.js');
  
  wp_enqueue_script('homestead-settings2', WP_CONTENT_URL.'/themes/homestead/jquery-1.4.2.min.js');
  wp_enqueue_script('homestead-settings3', WP_CONTENT_URL.'/themes/homestead/jpicker-1.1.3.min.js');
  
  //wp_enqueue_script('homestead-settings1', WP_CONTENT_URL.'/themes/homestead/css/colorpicker.css');
}*/


function mytheme_add_admin() {

    global $themename, $shortname, $settings_list;

    if ( $_GET['page'] == basename(__FILE__) ) {
    
        if ( 'save' == $_REQUEST['action'] ) {
                    
                  foreach($settings_list as $value) {
                    //echo '<script type="text/javascript">alert("' . $value . '");</script>';
                    //if($_REQUEST[$value] != "")
                      //update_option($shortname . '_' . $value,$_REQUEST[$value]);
                      update_option($shortname . '_' . $value,mysql_real_escape_string($_REQUEST[$value]));
                  }
                  
                  header("Location: themes.php?page=settings.php&saved=true");
                  die;
                  
        }
    }
    add_theme_page($themename." Settings", $themename." Settings", 'edit_themes', basename(__FILE__), 'mytheme_admin');
    

}

function mytheme_admin() {

    global $themename, $shortname, $settings_list;

    if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';
    if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings reset.</strong></p></div>';
    
?>
<style type="text/css">

table { border: none; }
td { padding: 5px; }
.ss_text { width: 350px; }

</style>



<div class="wrap">
<h2><?php echo $themename; ?> Theme Options</h2>

<form method="post">

<table>
<tr>
  <td>Custom logo URL:</td>
  <td><input type="text" name="custom_logo_url" class="ss_text" value="<?php echo stripslashes(stripslashes(get_option($shortname.'_custom_logo_url',''))); ?>" /><br /><small><a href="<?php bloginfo('url'); ?>/wp-admin/media-new.php" target="_blank">Upload your logo</a> (800px wide x 200px high)</small></td>
</tr>
<tr>
  <td>Custom background color:</td>
  <td><input type="text" name="custom_background_color" class="ss_text" value="<?php echo stripslashes(stripslashes(get_option($shortname.'_custom_background_color',''))); ?>" /> <small>e.g.: #27292a</small></td>
</tr><!--
<tr>
  <td>Disable slideshow:</td>
  <td><input type="checkbox" name="disable_slideshow" value="Yes" <?php if(get_option($shortname.'_disable_slideshow','') == "Yes") { echo 'checked="checked"'; } ?>  /></td>
</tr>
<tr>
  <td>Timeout for slideshow (ms):</td>
  <td><input type="text" name="slideshow_timeout" class="ss_text" value="<?php echo stripslashes(stripslashes(get_option($shortname.'_slideshow_timeout',''))); ?>" /> <small>e.g.: 7000</small></td>
</tr>-->
<tr>
  <td>Facebook Link:</td>
  <td><input type="text" name="facebook_link" class="ss_text" value="<?php echo stripslashes(stripslashes(get_option($shortname.'_facebook_link',''))); ?>" /></td>
</tr>
<tr>
  <td>Twitter Link:</td>
  <td><input type="text" name="twitter_link" class="ss_text" value="<?php echo stripslashes(stripslashes(get_option($shortname.'_twitter_link',''))); ?>" /></td>
</tr>
<tr>
  <td>Pinterest Link:</td>
  <td><input type="text" name="dribbble_link" class="ss_text" value="<?php echo stripslashes(stripslashes(get_option($shortname.'_dribbble_link',''))); ?>" /></td>
</tr>
<tr>
  <td>Google Plus Link:</td>
  <td><input type="text" name="google_plus_link" class="ss_text" value="<?php echo stripslashes(stripslashes(get_option($shortname.'_google_plus_link',''))); ?>" /></td>
</tr>
<tr>
  <td>Home Post Hover Background Color:</td>
  <td><input type="text" name="post_hover_background_color" class="ss_text" value="<?php echo stripslashes(stripslashes(get_option($shortname.'_post_hover_background_color',''))); ?>" /> <small>e.g.: #27292a</small></td>
</tr>
</table>

<p class="submit">
<input name="save" type="submit" value="Save changes" />    
<input type="hidden" name="action" value="save" />
</p>
</form>
<?php
}
add_action('admin_menu', 'mytheme_add_admin');
?>