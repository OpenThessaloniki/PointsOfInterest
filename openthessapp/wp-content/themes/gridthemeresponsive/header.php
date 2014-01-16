<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<head> 
  <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>          
  <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" title="no title" charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<!--[if lt IE 9]>
	<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->  
  <?php wp_head(); ?>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js" type="text/javascript" charset="utf-8"></script>
  <script src="<?php bloginfo('stylesheet_directory'); ?>/js/jquery.hover_caption.js" type="text/javascript" charset="utf-8"></script>    
  <script src="<?php bloginfo('stylesheet_directory'); ?>/js/jquery.infinitescroll.js" type="text/javascript" charset="utf-8"></script>    

<?php if ( is_page_template('tpl-blog.php') ) { ?>
<script type="text/javascript">
jQuery(document).ready(
function($){
  $('#content').infinitescroll({
    navSelector  : "div.load_more_text",            
                   // selector for the paged navigation (it will be hidden)
    nextSelector : "div.load_more_text a:first",    
                   // selector for the NEXT link (to page 2)
    itemSelector : "#content_inside .post_box"          
                   // selector for all items you'll retrieve
  },function(arrayOfNewElems){
      //$('.home_post_cont img').hover_caption();
  });  
}  
);
</script>
<?php } else { ?>
<script type="text/javascript">
jQuery(document).ready(
function($){
  $('#content').infinitescroll({
    navSelector  : "div.load_more_text",            
                   // selector for the paged navigation (it will be hidden)
    nextSelector : "div.load_more_text a:first",    
                   // selector for the NEXT link (to page 2)
    itemSelector : "#content_inside .post_box"          
                   // selector for all items you'll retrieve
  },function(arrayOfNewElems){
      $('.home_post_cont img').hover_caption();
  });  
}  
);
</script>
<?php } ?>

</head>
<body>

<?php $shortname = "grid"; ?>

<?php if(get_option($shortname.'_custom_background_color','') != "") { ?>
<style type="text/css">
  body { background-color: <?php echo get_option($shortname.'_custom_background_color',''); ?>; }
  .content_divider_inside { background-color: <?php echo get_option($shortname.'_custom_background_color',''); ?>; }
</style>
<?php } ?>

<?php if(get_option($shortname.'_post_hover_background_color','') != "") { ?>
<style type="text/css">
  .hover_caption { background-color: <?php echo get_option($shortname.'_post_hover_background_color',''); ?>; }
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
    
    <div id="menu_container">
    <!--
        <ul>
          <li><a href="#">Home</a></li>
          <li><a href="#">About</a></li>
          <li><a href="#">Blog</a></li>
          <li><a href="#">Contact</a></li>
        </ul>-->
        <?php wp_nav_menu('menu=header_menu&container=false'); ?>
        <!--
        <ul>
          <li><a href="#">Architecture</a></li>
          <li><a href="#">Graphic Design</a></li>
          <li><a href="#">Print</a></li>
          <li><a href="#">Typography</a></li>
          <li><a href="#">Web Design</a></li>
        </ul>-->
        <?php wp_nav_menu('menu=category_menu&container=false'); ?>                    
        
        <form role="search" method="get" id="searchform" action="<?php echo home_url( '/' ); ?>">
        <input type="text" name="s" id="s" value="Search" onclick="if(this.value == 'Search') this.value='';" onblur="if(this.value == '') this.value='Search';" />
        </form>
        
        <div class="header_social_icons_cont">
        
            <?php if(get_option($shortname.'_twitter_link','') != "") { ?>
                <a href="<?php echo get_option($shortname.'_twitter_link',''); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/twitter-icon.png" /></a>
            <?php } ?>                    

            <?php if(get_option($shortname.'_facebook_link','') != "") { ?>
                <a href="<?php echo get_option($shortname.'_facebook_link',''); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/facebook-icon.png" /></a>
            <?php } ?>        
            
            <?php if(get_option($shortname.'_google_plus_link','') != "") { ?>
                <a href="<?php echo get_option($shortname.'_google_plus_link',''); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/google-plus-icon.png" /></a>
            <?php } ?>            
            
            <?php if(get_option($shortname.'_dribbble_link','') != "") { ?>
                <a href="<?php echo get_option($shortname.'_dribbble_link',''); ?>"><img src="<?php bloginfo('stylesheet_directory'); ?>/images/dribbble-icon.png " /></a>
            <?php } ?>

            <div class="clear"></div>
        </div><!--//header_social_icons_cont-->
        
        <div class="clear"></div>
    </div><!--//menu_container-->