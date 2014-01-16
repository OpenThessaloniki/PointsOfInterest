<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<head> 
  <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>          
  <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" title="no title" charset="utf-8"/>
  <?php wp_head(); ?>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js" type="text/javascript" charset="utf-8"></script>
  <script src="<?php bloginfo('stylesheet_directory'); ?>/js/jquery.hover_caption.js" type="text/javascript" charset="utf-8"></script>    
</head>
<body>

<?php $shortname = "grid"; ?>

<?php if(get_option($shortname.'_custom_background_color','') != "") { ?>
<style type="text/css">
  body { background-color: <?php echo get_option($shortname.'_custom_background_color',''); ?>; }
  .content_divider_inside { background-color: <?php echo get_option($shortname.'_custom_background_color',''); ?>; }
</style>
<?php } ?>

<div id="main_container">

    <div id="header">
        <div align="center">
        <?php if(get_option($shortname.'_custom_logo_url','') != "") { ?>
          <a href="<?php bloginfo('url'); ?>"><img src="<?php echo stripslashes(stripslashes(get_option($shortname.'_custom_logo_url',''))); ?>" class="logo" /></a>
        <?php } else { ?>
          <a href="<?php bloginfo('url'); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/logo.png" class="logo" /></a>
        <?php } ?>                
        </div>
    </div><!--//header-->