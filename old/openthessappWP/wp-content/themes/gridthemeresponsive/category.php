<?php get_header(); ?>

  <script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
      $('.home_post_cont img').hover_caption();
//      $('.home_small_post_box img').hover_caption();
//      $('.side_box img').hover_caption();
    });
  </script>    
    
    <div id="content">
    
    <div id="content_inside">

    <?php
    global $wp_query;
    $args = array_merge( $wp_query->query, array( 'posts_per_page' => 8 ) );
    query_posts( $args );    
    while (have_posts()) : the_post(); ?>                            
    
        <?php
          $cat_text = '';
          foreach((get_the_category()) as $category) { 
              if($cat_text != '')
                $cat_text .= ' / ';
          
              $cat_text .= '<a href="' . get_category_link($category->term_id ) . '">' . $category->cat_name . '</a>'; 
          } 

        ?>
    
        <?php if($x == 3) { ?>
        <div class="home_post_cont home_post_cont_last post_box">
        <?php } else { ?>
        <div class="home_post_cont post_box">
        <?php } ?>
            <?php $temp_content = explode(" ",substr(strip_tags(get_the_content()),0,175)); $temp_content[(count($temp_content)-1)] = ''; $new_content = implode(" ",$temp_content); ?>
            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('home-post',array('alt' => 'post image', 'class' => '', 'title' => '<div class="home_post_content"><h4><a href="' . get_permalink() . '">' . get_the_title() . '</a></h4><p>' . $new_content . '...</p></div><div class="home_post_cat">' . $cat_text . '</div>')); ?></a>
        </div><!--//home_post_cont-->
        
        <?php if($x == 3) { $x = -1; echo '<div class="clear"></div>'; } ?>

    <?php $x++; ?>
    <?php endwhile; ?>
    
    <div class="clear"></div>
            
    </div><!--//content_inside-->    
    
    <div class="clear"></div>
    <div class="load_more_cont">
        <div align="center"><div class="load_more_text">
        
        <?php
        
        ob_start();
	next_posts_link('<img src="' . get_bloginfo('stylesheet_directory') . '/images/loading-button.png" />');
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) echo $buffer;
        ?>
        
        </div></div>
    </div><!--//load_more_cont-->                    
    
    <?php wp_reset_query(); ?>                            
    
    </div><!--//content-->
    
<?php get_footer(); ?> 