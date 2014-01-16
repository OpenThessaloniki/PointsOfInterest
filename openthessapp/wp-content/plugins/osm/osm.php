<?php
/*
Plugin Name: OSM
Plugin URI: http://wp-osm-plugin.HanBlog.net
Description: Embeds maps in your blog and adds geo data to your posts.  Find samples and a forum on the <a href="http://wp-osm-plugin.HanBlog.net">OSM plugin page</a>.  Simply create the shortcode to add it in your post at [<a href="options-general.php?page=osm.php">Settings</a>]
Version: 2.4.1
Author: MiKa
Author URI: http://www.HanBlog.net
Minimum WordPress Version Required: 2.8
*/

/*  (c) Copyright 2013  Michael Kang

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
load_plugin_textdomain('OSM-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');

define ("PLUGIN_VER", "V2.4.1");

// modify anything about the marker for tagged posts here
// instead of the coding.
define ("POST_MARKER_PNG", "marker_posts.png");
define (POST_MARKER_PNG_HEIGHT, 2);
define (POST_MARKER_PNG_WIDTH, 2);

define ("GCSTATS_MARKER_PNG", "geocache.png");
define (GCSTATS_MARKER_PNG_HEIGHT, 25);
define (GCSTATS_MARKER_PNG_WIDTH, 25);

define ("INDIV_MARKER", "marker_blue.png");
define (INDIV_MARKER_PNG_HEIGHT, 25);
define (INDIV_MARKER_PNG_WIDTH, 25);

// these defines are given by OpenStreetMap.org
define ("URL_INDEX", "http://www.openstreetmap.org/index.html?");
define ("URL_LAT","&mlat=");
define ("URL_LON","&mlon=");
define ("URL_ZOOM_01","&zoom=[");
define ("URL_ZOOM_02","]");
define (ZOOM_LEVEL_MAX,18); // standard is 17, only mapnik is 18
define (ZOOM_LEVEL_MIN,1);

// other geo plugin defines
// google-maps-geocoder
define ("WPGMG_LAT", "lat");
define ("WPGMG_LON", "lng");

// some general defines
define (LAT_MIN,-90);
define (LAT_MAX,90);
define (LON_MIN,-180);
define (LON_MAX,180);

// tracelevels
define (DEBUG_OFF, 0);
define (DEBUG_ERROR, 1);
define (DEBUG_WARNING, 2);
define (DEBUG_INFO, 3);
define (HTML_COMMENT, 10);

// Load OSM library mode
define (SERVER_EMBEDDED, 1);
define (SERVER_WP_ENQUEUE, 2);

define('OSM_PRIV_WP_CONTENT_URL', site_url() . '/wp-content' );
define('OSM_PRIV_WP_CONTENT_DIR', ABSPATH . 'wp-content' );
define('OSM_PRIV_WP_PLUGIN_URL', OSM_PRIV_WP_CONTENT_URL. '/plugins' );
define('OSM_PRIV_WP_PLUGIN_DIR', OSM_PRIV_WP_CONTENT_DIR . '/plugins' );
define('OSM_PLUGIN_URL', OSM_PRIV_WP_PLUGIN_URL."/osm/");
define('OSM_PLUGIN_ICONS_URL', OSM_PLUGIN_URL."icons/");
define('URL_POST_MARKER', OSM_PLUGIN_URL.POST_MARKER_PNG);
define('OSM_PLUGIN_THEMES_URL', OSM_PLUGIN_URL."themes/");
define('OSM_OPENLAYERS_THEMES_URL', WP_CONTENT_URL. '/uploads/osm/theme/' );
define('OSM_PLUGIN_JS_URL', OSM_PLUGIN_URL."js/");

global $wp_version;
if (version_compare($wp_version,"2.5.1","<")){
  exit('[OSM plugin - ERROR]: At least Wordpress Version 2.5.1 is needed for this plugin!');
}
	
// get the configuratin by
// default or costumer settings
if (@(!include('osm-config.php'))){
  include ('osm-config-sample.php');
}

// do not edit this
define ("Osm_TraceLevel", DEBUG_ERROR);

// If the function exists this file is called as upload_mimes.
// We don't do anything then.
if ( ! function_exists( 'fb_restrict_mime_types' ) ) {
  add_filter( 'upload_mimes', 'fb_restrict_mime_types' );
  /**
  * Retrun allowed mime types
  *
  * @see function get_allowed_mime_types in wp-includes/functions.php
  * @param array Array of mime types
  * @return array Array of mime types keyed by the file extension regex corresponding to those types.
  */
  function fb_restrict_mime_types( $mime_types ) {
    $mime_types['gpx'] = 'text/gpx';
    $mime_types['kml'] = 'text/kml';
    return $mime_types;
  }
}



// If the function exists this file is called as post-upload-ui.
// We don't do anything then.
if ( ! function_exists( 'fb_restrict_mime_types_hint' ) ) {
	// add to wp
	add_action( 'post-upload-ui', 'fb_restrict_mime_types_hint' );
	/**
	 * Get an Hint about the allowed mime types
	 *
	 * @return  void
	 */
	function fb_restrict_mime_types_hint() {
	  echo '<br />';
	  _e( 'OSM plugin added: GPX / KML' );
	}
}

//hook to create the meta box
add_action( 'add_meta_boxes', 'osm_map_create' );

function osm_map_create() {
  //create a custom meta box
  $screens = array( 'post', 'page' );
  foreach ($screens as $screen) {
    add_meta_box( 'osm-sc-meta', 'WP OSM Plugin shortcode generator', 'osm_map_create_function', $screen, 'normal', 'high' );
  }
}

function osm_map_create_function( $post ) {
?>
    <p>
    <b>Generate</b>:
    <select name="osm_mode">
        <option value="sc_gen">OSM shortcode</option>
        <option value="geotagging">geotag</option>
    </select><br>
    OSM shortcode options: <br>
    <b>OSM control theme</b>: 
    <select name="osm_theme">
        <option value="none">none</option>
        <option value="blue">blue</option>
        <option value="dark">dark</option>
        <option value="orange">orange</option>
    </select>
    <b>OSM marker</b>:
    <select name="osm_marker">
        <option value="none">none</option>
        <option value="wpttemp-green.png">Waypoint Green</option>
        <option value="wpttemp-red.png">Waypoint Red</option>
        <option value="marker_blue.png">Marker Blue</option>
        <option value="wpttemp-yellow.png">Marker Yellow</option>
        <option value="car.png">Marker Car</option>
        <option value="bus.png">Marker Bus</option>
        <option value="bicycling.png">Marker Bicycling</option>
        <option value="airport.png">Marker Airport</option>
        <option value="motorbike.png">Marker Motorbike</option>
        <option value="hostel.png">Marker Hostel</option>
        <option value="guest_house.png">Marker Guesthouse</option>
        <option value="camping.png">Marker Camping</option>
        <option value="geocache.png">Geocache</option>
        <option value="styria_linux.png">Styria Tux</option>
    </select>
    </p>

<?php echo Osm::sc_showMap(array('msg_box'=>'metabox_sc_gen','lat'=>'50','long'=>'18.5','zoom'=>'3', 'type'=>'AllOsm', 'width'=>'450','height'=>'300', 'map_border'=>'thin solid grey', 'theme'=>'dark', 'control'=>'mouseposition,scaleline')); ?>
  <br>
  <h3><span style="color:green"><?php _e('Copy the generated shortcode/customfield/argument: ','OSM-plugin') ?></span></h3>
  <div id="ShortCode_Div"><?php _e('If you click into the map this text is replaced','OSM-plugin') ?>
  </div><br>
  <?php
}

include('osm-openlayers.php');
    	
// let's be unique ... 
// with this namespace
class Osm
{ 

  function Osm() {
    $this->localizionName = 'Osm';
    //$this->TraceLevel = DEBUG_INFO;
	$this->ErrorMsg = new WP_Error();
	$this->initErrorMsg();
    
    // add the WP action
    add_action('wp_head', array(&$this, 'wp_head'));
    add_action('admin_menu', array(&$this, 'admin_menu'));
    add_action('wp_print_scripts',array(&$this, 'show_enqueue_script'));

    // add the WP shortcode
    add_shortcode('osm_map',array(&$this, 'sc_showMap'));
    add_shortcode('osm_image',array(&$this, 'sc_showImage'));
  }

  function initErrorMsg()
  {
    include('osm-error-msg.php');	
  }

  function traceErrorMsg($e = '')
  {
   if ($this == null){
     return $e;
   }
   $EMsg = $this->ErrorMsg->get_error_message($e);
   if ($EMsg == null){
     return $e;
     //return__("Unknown errormessage",$this->localizionName); 
   }
   return $EMsg;
  }
  
  function traceText($a_Level, $a_String)
  {
    $TracePrefix = array(
    DEBUG_ERROR =>'[OSM-Plugin-Error]:',
    DEBUG_WARNING=>'[OSM-Plugin-Warning]:',
    DEBUG_INFO=>'[OSM-Plugin-Info]:');
      
    if ($a_Level == DEBUG_ERROR){     
      echo '<div class="osm_error_msg"><p><strong style="color:red">'.$TracePrefix[$a_Level].Osm::traceErrorMsg($a_String).'</strong></p></div>';
    }
    else if ($a_Level <= Osm_TraceLevel){
      echo $TracePrefix[$a_Level].$a_String.'<br>';
    }
    else if ($a_Level == HTML_COMMENT){
      echo "<!-- ".$a_String." --> \n";
    }
  }

	// add it to the Settings page
  function options_page_osm()
  {
    if(isset($_POST['Options'])){
      // 0 = no error; 
      // 1 = error occured
      $Option_Error = 0; 
			
      // get the zoomlevel for the external link
      // and inform the user if the level was out of range     
      // update_option('osm_custom_field',$_POST['osm_custom_field']);
     
      if ($_POST['osm_zoom_level'] >= ZOOM_LEVEL_MIN && $_POST['osm_zoom_level'] <= ZOOM_LEVEL_MAX){
        update_option('osm_zoom_level',$_POST['osm_zoom_level']);
      }
      else { 
        $Option_Error = 1;
        Osm::traceText(DEBUG_ERROR, "e_zoomlevel_range");
      }
      // Let the user know whether all was fine or not
      if ($Option_Error  == 0){ 
        Osm::traceText(DEBUG_INFO, "i_options_updated");
      }
      else{
        Osm::traceText(DEBUG_ERROR, "e_options_not_updated");
      }
	}
    else{
	  //add_option('osm_custom_field', 0);
	  add_option('osm_zoom_level', 0);
	}
	
    // name of the custom field to store Long and Lat
    // for the geodata of the post
	$osm_custom_field  = get_option('osm_custom_field','OSM_geo_data');                                                  

    // zoomlevel for the link the OSM page
    $osm_zoom_level    = get_option('osm_zoom_level','7');
			
    include('osm-options.php');	
  }
	
  // put meta tags into the head section
  function wp_head($not_used)
  { 
	global $wp_query;
	global $post;

    $lat = '';
    $lon = '';
    $CustomField =  get_option('osm_custom_field','OSM_geo_data');
    if (($CustomField != false) && (get_post_meta($post->ID, $CustomField, true))){
      $PostLatLon = get_post_meta($post->ID, $CustomField, true);
      if (!empty($PostLatLon)) {
        list($lat, $lon) = explode(',', $PostLatLon); 
      }
    }   

    if(is_single() && ($lat != '') && ($lon != '')){
      $title = convert_chars(strip_tags(get_bloginfo("name")))." - ".$wp_query->post->post_title;
      $this->traceText(HTML_COMMENT, 'OSM plugin '.PLUGIN_VER.': adding geo meta tags:');
    }
    else{
      $this->traceText(HTML_COMMENT, 'OSM plugin '.PLUGIN_VER.': did not add geo meta tags.');
    return;
    } 
    
    // let's store geo data with W3 standard
	echo "<meta name=\"ICBM\" content=\"{$lat}, {$lon}\" />\n";
	echo "<meta name=\"DC.title\" content=\"{$wp_query->post->post_title}\" />\n";
        echo "<meta name=\"geo.placename\" content=\"{$wp_query->post->post_title}\"/>\n"; 
	echo "<meta name=\"geo.position\"  content=\"{$lat};{$lon}\" />\n";
  }
    
 
  function createMarkerList($a_import, $a_import_UserName, $a_Customfield, $a_import_osm_cat_incl_name,  $a_import_osm_cat_excl_name, $a_post_type, $a_import_osm_custom_tax_incl_name, $a_custom_taxonomy)
  {
     $this->traceText(DEBUG_INFO, "createMarkerList(".$a_import.",".$a_import_UserName.",".$a_Customfield.")");
	 global $post;
     $post_org = $post;
      
     // make a dummymarker to you use icon.clone later
     if ($a_import == 'gcstats'){
       $this->traceText(DEBUG_INFO, "Requesting data from gcStats-plugin");
       include('osm-import.php');
     }
     else if ($a_import == 'ecf'){
       $this->traceText(DEBUG_INFO, "Requesting data from comments");
       include('osm-import.php');
     }
     else if ($a_import == 'osm' || $a_import == 'osm_l'){
       // let's see which posts are using our geo data ...
       $this->traceText(DEBUG_INFO, "check all posts for osm geo custom fields");
       $CustomFieldName = get_option('osm_custom_field','OSM_geo_data');        
       $recentPosts = new WP_Query();
       $recentPosts->query('meta_key='.$CustomFieldName.'&post_status=publish'.'&showposts=-1'.'&post_type='.$a_post_type.'');
//     $recentPosts->query('meta_key='.$CustomFieldName.'&post_status=publish'.'&post_type=page');
       while ($recentPosts->have_posts()) : $recentPosts->the_post();
  	     list($temp_lat, $temp_lon) = explode(',', get_post_meta($post->ID, $CustomFieldName, true)); 
//         echo $post->ID.'Lat: '.$temp_lat.'Long '.$temp_lon.'<br>';

         // check if a filter is set and geodata are set
         // if filter is set and set then pretend there are no geodata
       if (($a_import_osm_cat_incl_name  != 'Osm_All' || $a_import_osm_cat_excl_name  != 'Osm_None' || $a_import_osm_custom_tax_incl_name != 'Osm_All')&&($temp_lat != '' && $temp_lon != '')){
         $categories = wp_get_post_categories($post->ID);
         foreach( $categories as $catid ) {
	       $cat = get_category($catid);
           if (($a_import_osm_cat_incl_name  != 'Osm_All') && (strtolower($cat->name) != (strtolower($a_import_osm_cat_incl_name)))){
             $temp_lat = '';
             $temp_lon = '';
            }
            if (strtolower($cat->name) == (strtolower($a_import_osm_cat_excl_name))){
              $temp_lat = '';
              $temp_lon = '';
            }
         }    
         if ($a_import_osm_custom_tax_incl_name != 'Osm_All')
           $mycustomcategories = get_the_terms( $post->ID, $a_import_osm_custom_tax_incl_name);
         foreach( $mycustomcategories as $term ) {
           $taxonomies[0] = $term->term_taxonomy_id;
           // Get rid of the other data stored in the object
           unset($term);
         }
         foreach( $taxonomies as $taxid ) {
           $termsObjects = wp_get_object_terms($post->ID, $a_custom_taxonomy);
           foreach ($termsObjects as $termsObject) {
             $currentCustomCat[] = $termsObject->name;
           }
           if (($a_import_osm_custom_tax_incl_name  != 'Osm_All') &&  ! in_array($a_import_osm_custom_tax_incl_name, $currentCustomCat)) {
             $temp_lat = '';
             $temp_lon = '';
           }
           if (strtolower($currentCustomCat) == (strtolower($a_import_osm_cat_excl_name))){
             $temp_lat = '';
             $temp_lon = '';
           }
         }
       }
       if ($temp_lat != '' && $temp_lon != '') {
         list($temp_lat, $temp_lon) = $this->checkLatLongRange('$marker_all_posts',$temp_lat, $temp_lon);
         if ($a_import == 'osm_l' ){   
           $categories = wp_get_post_categories($post->ID);
	       // take the last one but ignore those without a specific category
           foreach( $categories as $catid ) {
	         $cat = get_category($catid);
             if ((strtolower($cat->name) == 'uncategorized') || (strtolower($cat->name) == 'allgemein')){
               $Category_Txt = '';
             }
             else{
               $Category_Txt = $cat->name.': ';
             }
           }
           $Marker_Txt = '<a href="'.get_permalink($post->ID).'">'.$Category_Txt.get_the_title($post->ID).'  </a>';
           $MarkerArray[] = array('lat'=> $temp_lat,'lon'=>$temp_lon,'popup_height'=>'100', 'popup_width'=>'150', 'marker'=>$Icon[name], 'text'=>$Marker_Txt);
         }	 
         else{ // plain osm without link to the post
           $Marker_Txt = ' ';
           $MarkerArray[] = array('lat'=> $temp_lat,'lon'=>$temp_lon,'popup_height'=>'100', 'popup_width'=>'150', 'marker'=>$Icon[name], 'text'=>$Marker_Txt);
         }
	   }  
       endwhile;
     }
     else if ($a_import == 'wpgmg'){
       // let's see which posts are using our geo data ...
       $this->traceText(DEBUG_INFO, "check all posts for wpgmg geo custom fields");
       $recentPosts = new WP_Query();
       $recentPosts->query('meta_key='.WPGMG_LAT.'&meta_key='.WPGMG_LON.'&showposts=-1');
       while ($recentPosts->have_posts()) : $recentPosts->the_post();
         include('osm-import.php');
         if ($temp_lat != '' && $temp_lon != '') {
           list($temp_lat, $temp_lon) = $this->checkLatLongRange('$marker_all_posts',$temp_lat, $temp_lon);          
           $MarkerArray[] = array('lat'=> $temp_lat,'lon'=>$temp_lon,'marker'=>$Icon[name],'popup_height'=>'100', 'popup_width'=>'200');
         }  
       endwhile;
     }
     $post = $post_org;
     return $MarkerArray;
  }

  // if you miss a colour, just add it
  function checkStyleColour($a_colour){
    if ($a_colour != 'red' && $a_colour != 'blue' && $a_colour != 'black' && $a_colour != 'green' && $a_colour != 'orange'){
      return "blue";
    }
    return $a_colour;
  }

  // get the layer for the markers
  function getImportLayer($a_type, $a_UserName, $Icon, $a_osm_cat_incl_name, $a_osm_cat_excl_name, $a_line_color, $a_line_width, $a_line_opacity, $a_post_type, $a_import_osm_custom_tax_incl_name, $a_custom_taxonomy){

    if ($a_type  == 'osm_l'){
      $LayerName = 'TaggedPosts';
      if ($Icon[name] != 'NoName'){ // <= ToDo
        $PopUp = 'true';     
      }
      else {
        $PopUp = 'false';
      }
      
    }    
    
    // import data from tagged posts
    else if ($a_type  == 'osm'){
      $LayerName = 'TaggedPosts';
      $PopUp = 'false';
    }

    // import data from wpgmg
    else if ($a_type  == 'wpgmg'){
      $LayerName = 'TaggedPosts';
      $PopUp = 'false';
    }
    // import data from gcstats
    else if ($a_type == 'gcstats'){
      $LayerName     = 'GeoCaches';
      $PopUp = 'true';
      $Icon = Osm::getIconsize(GCSTATS_MARKER_PNG);
      $Icon[name] = GCSTATS_MARKER_PNG;
    }
    // import data from ecf
    else if ($a_type == 'ecf'){
      $LayerName = 'Comments';
      $PopUp = 'true';
      $Icon = Osm::getIconsize(INDIV_MARKER);
      $Icon[name] = INDIV_MARKER;
    }
    else{
      $this->traceText(DEBUG_ERROR, "e_import_unknwon");
    }
    $MarkerArray = $this->createMarkerList($a_type, $a_UserName,'Empty', $a_osm_cat_incl_name,  $a_osm_cat_excl_name, $a_post_type, $a_import_osm_custom_tax_incl_name, $a_custom_taxonomy);
    if ($a_line_color != 'none'){
      $line_color = Osm::checkStyleColour($a_line_color);
      $txt = Osm_OpenLayers::addLines($MarkerArray, $line_color, $a_line_width);
    }
    $txt .= Osm_OpenLayers::addMarkerListLayer($LayerName, $Icon, $MarkerArray, $PopUp);
    return $txt;
  }

 // check Lat and Long
  function getMapCenter($a_Lat, $a_Long, $a_import, $a_import_UserName){
    if ($a_import == 'wpgmg'){
      $a_Lat  = OSM_getCoordinateLat($a_import);
      $a_Long = OSM_getCoordinateLong($a_import);
    }
    else if ($a_import == 'gcstats'){
      if (function_exists('gcStats__getInterfaceVersion')) {
        $Val = gcStats__getMinMaxLat($a_import_UserName);
        $a_Lat = ($Val[min] + $Val[max]) / 2;
        $Val = gcStats__getMinMaxLon($a_import_UserName);
        $a_Long = ($Val[min] + $Val[max]) / 2;
      }
      else{
       $this->traceText(DEBUG_WARNING, "getMapCenter() could not connect to gcStats plugin");
       $a_Lat  = 0;$a_Long = 0;
      }
    }
    else if ($a_Lat == '' || $a_Long == ''){
      $a_Lat  = OSM_getCoordinateLat('osm');
      $a_Long = OSM_getCoordinateLong('osm');
    }
    return array($a_Lat,$a_Long);
  }
    
  // check Lat and Long
  function checkLatLongRange($a_CallingId, $a_Lat, $a_Long)
  {
    if ($a_Lat >= LAT_MIN && $a_Lat <= LAT_MAX && $a_Long >= LON_MIN && $a_Long <= LON_MAX &&
                    preg_match('!^[^0-9]+$!', $a_Lat) != 1 && preg_match('!^[^0-9]+$!', $a_Long) != 1){
      return array($a_Lat,$a_Long);              
    }
    else{
      $this->traceText(DEBUG_ERROR, "e_lat_lon_range");
      $this->traceText(DEBUG_INFO, "Error: ".$a_CallingId." Lat".$a_Lat." or Long".$a_Long);
      $a_Lat  = 0;$a_Long = 0;
    }
  }

 function isOsmIcon($a_IconName)
 {

   if ($a_IconName == "airport.png" || $a_IconName == "bicycling.png" ||
    $a_IconName == "bus.png" || $a_IconName == "camping.png" ||
    $a_IconName == "car.png" || $a_IconName == "friends.png" ||
    $a_IconName == "geocache.png" || $a_IconName == "guest_house.png" ||
    $a_IconName == "home.png" || $a_IconName == "hostel.png" ||
    $a_IconName == "hotel.png"|| $a_IconName == "marker_blue.png" ||
    $a_IconName == "motorbike.png" || $a_IconName == "restaurant.png" ||
    $a_IconName == "services.png" || $a_IconName == "styria_linux.png" ||
    $a_IconName == "marker_posts.png" || $a_IconName == "restaurant.png" ||
    $a_IconName == "toilets.png" || $a_IconName == "wpttemp-yellow.png" ||
    $a_IconName == "wpttemp-green.png" || $a_IconName == "wpttemp-red.png"){
    return 1;
   }
   else {
    return 0;
   }
 } 

 function getIconsize($a_IconName)
 {
  $Icons = array(
    "airport.png"        => array("height"=>32,"width"=>"31","offset_height"=>"-16","offset_width"=>"-16"),
    "bicycling.png"      => array("height"=>19,"width"=>"32","offset_height"=>"-9","offset_width"=>"-16"),
    "bus.png"            => array("height"=>32,"width"=>"26","offset_height"=>"-16","offset_width"=>"-13"),
    "camping.png"        => array("height"=>32,"width"=>"32","offset_height"=>"-16","offset_width"=>"-16"),
    "car.png"            => array("height"=>18,"width"=>"32","offset_height"=>"-16","offset_width"=>"-9"),
    "friends.png"        => array("height"=>32,"width"=>"32","offset_height"=>"-16","offset_width"=>"-16"),
    "geocache.png"       => array("height"=>25,"width"=>"25","offset_height"=>"-12","offset_width"=>"-12"),
    "guest_house.png"    => array("height"=>32,"width"=>"32","offset_height"=>"-16","offset_width"=>"-16"),
    "home.png"           => array("height"=>32,"width"=>"32","offset_height"=>"-16","offset_width"=>"-16"),
    "hostel.png"         => array("height"=>24,"width"=>"24","offset_height"=>"-12","offset_width"=>"-12"),
    "hotel.png"          => array("height"=>32,"width"=>"32","offset_height"=>"-16","offset_width"=>"-16"),
    "marker_blue.png"    => array("height"=>24,"width"=>"24","offset_height"=>"-12","offset_width"=>"-12"),
    "motorbike.png"      => array("height"=>23,"width"=>"32","offset_height"=>"-12","offset_width"=>"-16"),
    "restaurant.png"     => array("height"=>24,"width"=>"24","offset_height"=>"-12","offset_width"=>"-12"),
    "services.png"       => array("height"=>28,"width"=>"32","offset_height"=>"-14","offset_width"=>"-16"),
    "styria_linux.png"   => array("height"=>50,"width"=>"36","offset_height"=>"-25","offset_width"=>"-18"),
    "marker_posts.png"   => array("height"=>2,"width"=>"2","offset_height"=>"-1","offset_width"=>"-1"),
    "restaurant.png"     => array("height"=>24,"width"=>"24","offset_height"=>"-12","offset_width"=>"-12"),
    "toilets.png"        => array("height"=>32,"width"=>"32","offset_height"=>"-16","offset_width"=>"-16"),
    "wpttemp-yellow.png" => array("height"=>24,"width"=>"24","offset_height"=>"-24","offset_width"=>"0"),
    "wpttemp-green.png"  => array("height"=>24,"width"=>"24","offset_height"=>"-24","offset_width"=>"0"),
    "wpttemp-red.png"    => array("height"=>24,"width"=>"24","offset_height"=>"-24","offset_width"=>"0"),
  );

  if ($Icons[$a_IconName][height] == ''){
    $Icon = array("height"=>24,"width"=>"24");
    $this->traceText(DEBUG_ERROR, "e_unknown_icon");
    $this->traceText(DEBUG_INFO, "Error: (marker_name: ".$a_IconName.")!"); 
  }
  else {
    $Icon = $Icons[$a_IconName];
  }
  return $Icon;
 }

  function getGPXName($filepath){
    $file = basename($filepath, ".gpx"); // $file is set to "index"
    return $file;
  }

  // execute the java script to display 
  // the OpenStreetMap
  function sc_showMap($atts) {
    // let's get the shortcode arguments
  	extract(shortcode_atts(array(
    // size of the map
    'width'     => '450', 'height' => '300', 
    // address of the center in the map
		'lat'       => '', 'long'  => '',    
    // the zoomlevel of the map 
    'zoom'      => '7',     
    // Mapnik, CycleMap, ...           
    'type'      => 'AllOsm',
    // track info
    'gpx_file'  => 'NoFile',           // 'absolut address'          
    'gpx_file_proxy'  => 'NoFile',     // 'absolut address'          
    'gpx_colour'=> 'NoColour',
    'gpx_file_list'   => 'NoFileList',
    'gpx_colour_list' => 'NoColourList',
    'kml_file'  => 'NoFile',           // 'absolut address'          
    'kml_colour'=> 'NoColour',
    // are there markers in the map wished loaded from a file
    'marker_file'     => 'NoFile', // 'absolut address'
    'marker_file_proxy' => 'NoFile', // 'absolut address'
  	'marker_file_list' => 'NoFileList', // 'absolut address for a list of marker files''
    // are there markers in the map wished loaded from post tags
    'marker_all_posts'=> 'n',      // 'y' or 'Y'
    'marker_name'     => 'NoName',
    'marker_height'   => '0',
    'marker_width'    => '0',
    'marker_focus'    => '0',
    'ov_map'          => '-1',         // zoomlevel of overviewmap
    'import'          => 'No',
    'import_osm_cat_incl_name'  => 'Osm_All',
    'import_osm_cat_excl_name'  => 'Osm_None',
    'import_osm_line_color' => 'none', 
    'import_osm_line_width' => '4',
    'import_osm_line_opacity' => '0.9',
    'post_type' => 'post',
    'custom_taxonomy' => 'none',
    'import_osm_custom_tax_incl_name'  => 'Osm_All',
    'marker'          => 'No',
    'marker_routing'  => 'No',
    'msg_box'         => 'No',
    'custom_field'    => 'No',
    'control'         => 'No',
    'extmap_type'     => 'No',
    'extmap_name'     => 'No',
    'extmap_address'  => 'No',
    'extmap_init'     => 'No',
    'map_border'      => 'none',
    'z_index'         => 'none',
    'm_txt_01'        => 'none',
    'm_txt_02'        => 'none',
    'm_txt_03'        => 'none',
    'm_txt_04'        => 'none',
    'theme'           => 'ol',
    'disc_center_list'          => '',          // in decimal degrees
    'disc_radius_list'          => '',          // in meters
    'disc_center_opacity_list'  => '0.5',       // float 0->1
    'disc_center_color_list'    => 'red',       // html name or #rvb or #rrvvbb
    'disc_border_width_list'    => '3',         // integer
    'disc_border_color_list'    => 'blue',      // html name or #rvb or #rrvvbb
    'disc_border_opacity_list'  => '0.5',      // float 0->1
    'disc_fill_color_list'      => 'lightblue',// html name or #rvb or #rrvvbb
    'disc_fill_opacity_list'    => '0.5'       // float 0->1

	  ), $atts));
   
    if (($zoom < ZOOM_LEVEL_MIN || $zoom > ZOOM_LEVEL_MAX) && ($zoom != 'auto')){
      $this->traceText(DEBUG_ERROR, "e_zoomlevel_range");
      $this->traceText(DEBUG_INFO, "Error: (Zoomlevel: ".$zoom.")!");
      $zoom = 0;   
    }
    if ($width < 1 || $height < 1){
      Osm::traceText(DEBUG_ERROR, "e_map_size");
      Osm::traceText(DEBUG_INFO, "Error: ($width: ".$width." $height: ".$height.")!");
      $width = 450; $height = 300;
    }

    if ($marker_name == 'NoName'){
      $marker_name  = POST_MARKER_PNG;
    }

    if (Osm::isOsmIcon($marker_name) == 1){
       $Icon = Osm::getIconsize($marker_name);
       $Icon[name]  = $marker_name;
    }
    else  {
      $Icon[height] = $marker_height;
      $Icon[width]  = $marker_width; 
      $Icon[name]  = $marker_name;
      if ($marker_focus == 0){ // center is default
        $Icon[offset_height] = round(-$marker_height/2);
        $Icon[offset_width] = round(-$marker_width/2);
      }
      else if ($marker_focus == 1){ // left bottom
        $Icon[offset_height] = -$marker_height;
        $Icon[offset_width]  = 0;
      }
      else if ($marker_focus == 2){ // left top
        $Icon[offset_height] = 0;
        $Icon[offset_width]  = 0;
      }
      else if ($marker_focus == 3){ // right top
        $Icon[offset_height] = 0;
        $Icon[offset_width]  = -$marker_width;
      }
      else if ($marker_focus == 4){ // right bottom
        $Icon[offset_height] = -$marker_height;
        $Icon[offset_width]  = -$marker_width;
      }
      else if ($marker_focus == 5){ // center bottom
        $Icon[offset_height] = -$marker_height;
        $Icon[offset_width] = round(-$marker_width/2);
      }
      if ($Icon[height] == 0 || $Icon[width] == 0){
        Osm::traceText(DEBUG_ERROR, "e_marker_size"); //<= ToDo
        $Icon[height] = 24;
        $Icon[width]  = 24;
      }
    }

    list($import_type, $import_UserName) = explode(',', $import);
    if ($import_UserName == ''){
      $import_UserName = 'DummyName';
    }
    $import_type = strtolower($import_type);
	  $array_control = explode( ',', $control);
   
    list($lat, $long) = Osm::getMapCenter($lat, $long, $import_type, $import_UserName);
    if ($lat != 'auto' && $long != 'auto'){
      list($lat, $long) = Osm::checkLatLongRange('MapCenter',$lat, $long);
    }
    $gpx_colour       = Osm::checkStyleColour($gpx_colour); 
    $kml_colour       = Osm::checkStyleColour($kml_colour);
    $type             = Osm_OpenLayers::checkMapType($type);
    $ov_map           = Osm_OpenLayers::checkOverviewMapZoomlevels($ov_map);
	  
    $array_control    = Osm_OpenLayers::checkControlType($array_control);

    // to manage several maps on the same page
    // create names with index
    static  $MapCounter = 0;
    $MapCounter += 1;
    $MapName = 'map_'.$MapCounter;
    $GpxName = 'GPX_'.$MapCounter;
    $KmlName = 'KML_'.$MapCounter;
	
    Osm::traceText(DEBUG_INFO, "MapCounter = ".$MapCounter);
      
    // if we came up to here, let's load the map
    $output = '';	
    $output .= '<link rel="stylesheet" type="text/css" href="'.OSM_PLUGIN_URL.'/css/osm_map.css" />';
    $output .= '<style type="text/css">';
    if ($z_index != 'none'){ // fix for NextGen-Gallery
      $output .= '.entry .olMapViewport img {z-index: '.$z_index.' !important;}';   
      $output .= '.olControlNoSelect {z-index: '.$z_index.'+1.'.' !important;}';    
      $output .= '.olControlAttribution {z-index: '.$z_index.'+1.'.' !important;}';
    }
    $output .= '.olTileImage { max-width: none !important; max-height: none !important; vertical-align: none;}';
    $output .= '.OSM_Map img { max-width: none !important; max-height: none !important; vertical-align: none;}';      

    $output .= '#'.$MapName.' {clear: both; padding: 0px; margin: 0px; border: 0px; width: 100%; height: 100%; margin-top:0px; margin-right:0px;margin-left:0px; margin-bottom:0px; left: 0px;}';
    $output .= '#'.$MapName.' img{clear: both; padding: 0px; margin: 0px; border: 0px; width: 100%; height: 100%; position: absolute; margin-top:0px; margin-right:0px;margin-left:0px; margin-bottom:0px;}';
    $output .= '</style>';

    $output .= '<div id="'.$MapName.'" class="OSM_Map" style="width:'.$width.'px; height:'.$height.'px; overflow:hidden;padding:0px;border:'.$map_border.';">';

    
	if (Osm_LoadLibraryMode == SERVER_EMBEDDED){
	  if (OL_LIBS_LOADED == 0) {
        $output .= '<script type="text/javascript" src="'.Osm_OL_LibraryLocation.'"></script>';
        define (OL_LIBS_LOADED, 1);
      }
  
      if ($type == 'Mapnik' || $type == 'Osmarender' || $type == 'CycleMap' || $type == 'All' || $type == 'AllOsm' || $type == 'Ext'){
	    if (OSM_LIBS_LOADED == 0) {
          $output .= '<script type="text/javascript" src="'.Osm_OSM_LibraryLocation.'"></script>';
          define (OSM_LIBS_LOADED, 1);
        }
      }
      elseif ($type == 'OpenSeaMap'){
	    if (OSM_LIBS_LOADED == 0) {
          $output .= '<script type="text/javascript" src="'.Osm_OSM_LibraryLocation.'"></script>';
          $output .= '<script type="text/javascript" src="'.Osm_harbours_LibraryLocation.'"></script>';
          $output .= '<script type="text/javascript" src="'.Osm_map_utils_LibraryLocation.'"></script>';
          $output .= '<script type="text/javascript" src="'.Osm_utilities_LibraryLocation.'"></script>';
          define (OSM_LIBS_LOADED, 1);
        }
      }
      elseif ($type == 'OpenWeatherMap'){
      	if (OSM_LIBS_LOADED == 0) {
      		$output .= '<script type="text/javascript" src="'.Osm_OSM_LibraryLocation.'"></script>';
      		$output .= '<script type="text/javascript" src="'.Osm_openweather_LibraryLocation.'"></script>';
      		define (OSM_LIBS_LOADED, 1);
      	}
      }
      if ($type == 'GooglePhysical' || $type == 'GoogleStreet' || $type == 'GoogleHybrid' || $type == 'GoogleSatellite' || $type == 'All' || $type == 'AllGoogle' || $a_type == 'Ext' || $type == 'Google Physical' || $type == 'Google Street' || $type == 'Google Hybrid' || $type == 'Google Satellite'){
	    if (GOOGLE_LIBS_LOADED == 0) {
          $output .= '<script type="text/javascript" src="'.Osm_GOOGLE_LibraryLocation.'"></script>';
          define (GOOGLE_LIBS_LOADED, 1);
        }
      }
      $output .= '<script type="text/javascript" src="'.OSM_PLUGIN_JS_URL.'osm-plugin-lib.js"></script>';
    }
      elseif (Osm_LoadLibraryMode == SERVER_WP_ENQUEUE){
      // registered and loaded by WordPress
      }
      else{
        $this->traceText(DEBUG_ERROR, "e_library_config");
      }
      $output .= '<script type="text/javascript">';
      $output .= '/* <![CDATA[ */';
      //$output .= 'jQuery(document).ready(';
      //$output .= 'function($) {';
      $output .= '(function($) {';
      $output .= Osm_OpenLayers::addOsmLayer($MapName, $type, $ov_map, $array_control, $extmap_type, $extmap_name, $extmap_address, $extmap_init, $theme);

    // add a clickhandler if needed
    $msg_box = strtolower($msg_box);
    if ( $msg_box == 'sc_gen' || $msg_box == 'lat_long' || $msg_box == 'metabox_sc_gen'){
      $output .= Osm_OpenLayers::AddClickHandler($msg_box);
    }
    // set center and zoom of the map
    $output .= Osm_OpenLayers::setMapCenterAndZoom($lat, $long, $zoom);

    // Add the Layer with GPX Track
    if ($gpx_file_proxy != 'NoFile'){ 
      $GpxName = basename($gpx_file_proxy, ".gpx");
      $output .= Osm_OpenLayers::addGmlLayer($GpxName, OSM_PLUGIN_URL."osm-proxy.php?url=".$gpx_file_proxy, $gpx_colour,'GPX');
    }

    if ($gpx_file != 'NoFile'){ 
      $GpxName = basename($gpx_file, ".gpx");
      $output .= Osm_OpenLayers::addGmlLayer($GpxName, $gpx_file,$gpx_colour,'GPX');
    }

    if ($gpx_file_list != 'NoFileList'){
      $GpxFileListArray   = explode( ',', $gpx_file_list ); 
      $GpxColourListArray = explode( ',', $gpx_colour_list);
      $this->traceText(DEBUG_INFO, "(NumOfGpxFiles: ".sizeof($GpxFileListArray)." NumOfGpxColours: ".sizeof($GpxColourListArray).")!");
      if (sizeof($GpxFileListArray) == sizeof($GpxColourListArray)){
        for($x=0;$x<sizeof($GpxFileListArray);$x++){
          $GpxName = basename($GpxFileListArray[$x], ".gpx");
          $output .= Osm_OpenLayers::addGmlLayer($GpxName, $GpxFileListArray[$x],$GpxColourListArray[$x],'GPX');
        }
      }
      else {
        $this->traceText(DEBUG_ERROR, "e_gpx_list_error");
      }
    }
    
    // Add the Layer with KML Track
    if ($kml_file != 'NoFile'){ 
      $output .= Osm_OpenLayers::addGmlLayer($KmlName, $kml_file,$kml_colour,'KML');
    }

    // Add the marker here which we get from the file
    if ($marker_file_proxy != 'NoFile'){
      $MarkerName = basename($marker_file_proxy, ".txt");
      $output .= Osm_OpenLayers::addTextLayer($MarkerName, OSM_PLUGIN_URL."osm-proxy.php?url=".$marker_file_proxy);
    }  
    
    if ($marker_file != 'NoFile'){    
      $MarkerName = basename($marker_file, ".txt");
      $output .= Osm_OpenLayers::addTextLayer($MarkerName, $marker_file);
    }  
    if ($marker_file_list != 'NoFileList'){
      $MarkerFileListArray = explode( ',', $marker_file_list );
      $this->traceText(DEBUG_INFO, "(NumOfMarkerFiles: ".sizeof($MarkerFileListArray)."!");
      for($x=0;$x<sizeof($MarkerFileListArray);$x++){
        $MarkerLstName = basename($MarkerFileListArray[$x], ".txt");
      	$output .= Osm_OpenLayers::addTextLayer($MarkerLstName, $MarkerFileListArray[$x]);
      }
     }      	
      	
    $marker_all_posts = strtolower($marker_all_posts);
    if ($marker_all_posts == 'y'){
      //$this->traceText(DEBUG_ERROR, "e_use_marker_all_posts");
      $import_type  = 'osm';
    }

    if ($import_type  != 'no'){
  $output .= Osm::getImportLayer($import_type, $import_UserName, $Icon, $import_osm_cat_incl_name,  $import_osm_cat_excl_name, $import_osm_line_color, $import_osm_line_width, $import_osm_line_opacity, $post_type, $import_osm_custom_tax_incl_name, $custom_taxonomy);
    }
    if ($disc_center_list != ''){
      $centerListArray        = explode( ',', $disc_center_list );
      $radiusListArray        = explode( ',', $disc_radius_list );
      $centerOpacityListArray = explode( ',', $disc_center_opacity_list);
      $centerColorListArray   = explode( ',', $disc_center_color_list );
      $borderWidthListArray   = explode( ',', $disc_border_width_list );
      $borderColorListArray   = explode( ',', $disc_border_color_list );
      $borderOpacityListArray = explode( ',', $disc_border_opacity_list);
      $fillColorListArray     = explode( ',', $disc_fill_color_list );
      $fillOpacityListArray   = explode( ',', $disc_fill_opacity_list);
      $this->traceText(DEBUG_INFO, "(NumOfdiscs: ".sizeof($centerListArray)." NumOfradius: ".sizeof($radiusListArray).")!");

      if (sizeof($centerListArray) == sizeof($radiusListArray) && !empty($centerListArray) && !empty($radiusListArray)   ) {
        $output .= Osm_OpenLayers::addDiscs($centerListArray,$radiusListArray,$centerOpacityListArray,$centerColorListArray, $borderWidthListArray,$borderColorListArray,$borderOpacityListArray,$fillColorListArray,$fillOpacityListArray);
      } else {
        $this->traceText(DEBUG_ERROR, "Discs parameters error");
      }
    }
  
   // just add single marker 
   if ($marker  != 'No'){  
     global $post;
     $DoPopUp = 'true';
     list($temp_lat, $temp_lon, $temp_popup_custom_field) = explode(',', $marker);
	   if ($temp_popup_custom_field == ''){
		   $temp_popup_custom_field = 'osm_dummy';
	   }

     $temp_popup_custom_field = trim($temp_popup_custom_field);
     $temp_popup = get_post_meta($post->ID, $temp_popup_custom_field, true); 
 
     if ($m_txt_01 != 'none'){
       $temp_popup .= '<br>'.$m_txt_01;
     }
     if ($m_txt_02 != 'none'){
       $temp_popup .= '<br>'.$m_txt_02;
     }
     if ($m_txt_03 != 'none'){
       $temp_popup .= '<br>'.$m_txt_03;
     }	   
     if ($m_txt_04 != 'none'){
       $temp_popup .= '<br>'.$m_txt_04;
     }

     $marker_routing = strtolower($marker_routing);
     if ($marker_routing != 'no') { 
       $temp_popup .= '<br><div class="route"><a href="';
       if ($marker_routing == 'ors' || $marker_routing == 'openrouteservice') {  
         $temp_popup .= 'http://openrouteservice.org/index.php?end=' . $temp_lon . ',' . $temp_lat . '&zoom=' . $zoom . '&pref=Fastest&lang=' . substr(get_locale(),0,2) . '&noMotorways=false&noTollways=false';
       } 
       elseif ($marker_routing == 'cm' || $marker_routing == 'cloudmade') {  
         $temp_popup .= 'http://maps.cloudmade.com/?lat=' . $temp_lat . '&lng=' . $temp_lon . '&zoom=' . $zoom . '&directions=' . $temp_lat . ',' . $temp_lon . '&travel=car&styleId=1&active_page=0&opened_tab=1';
       }
       elseif ($marker_routing == 'yn' || $marker_routing == 'yournavigation') {  
         $temp_popup .= 'http://yournavigation.org/?tlat=' . $temp_lat . '&tlon=' . $temp_lon;
       }
       else {
         $temp_popup .= 'missing routing service!'.$marker_routing;
         Osm::traceText(DEBUG_ERROR, "e_missing_rs_error");
       }
       $temp_popup .= '">' . __("Route from your location to this place", "Osm") . '</a></div>';
     }

     if (($temp_popup_custom_field == 'osm_dummy') && ($m_txt_01 == 'none') && ($marker_routing == 'no')){
       $DoPopUp = 'false';
     }

     list($temp_lat, $temp_lon) = Osm::checkLatLongRange('Marker',$temp_lat, $temp_lon); 
     $MarkerArray[] = array('lat'=> $temp_lat,'lon'=>$temp_lon,'text'=>$temp_popup,'popup_height'=>'150', 'popup_width'=>'150');
     $output .= Osm_OpenLayers::addMarkerListLayer('Marker', $Icon,$MarkerArray,$DoPopUp);
    }

    // set center and zoom of the map
    $output .= Osm_OpenLayers::setMapCenterAndZoom($lat, $long, $zoom);

    //$output .= '}';
    //$output .= ');';
    $output .= '})(jQuery)';
    $output .= '/* ]]> */';
    $output .= ' </script>';
    $output .= '</div>';
    return $output;
	}

  // execute the java script to display 
  // the zoomify 
  function sc_showImage($atts) {
    // let's get the shortcode arguments
  	extract(shortcode_atts(array(
    // size of the map
    'width'     => '450', 'height' => '300',  
    // the zoomlevel of the map 
    'zoom'      => '7',     
    // track info
    'control'     => 'No',
    'map_border'      => 'none',
    'z_index'         => 'none',
    'extmap_address'  => 'No',
    'theme'           => 'ol'
	  ), $atts));
   
    if (($zoom < ZOOM_LEVEL_MIN || $zoom > ZOOM_LEVEL_MAX) && ($zoom != 'auto')){
      $this->traceText(DEBUG_ERROR, "e_zoomlevel_range");
      $this->traceText(DEBUG_INFO, "Error: (Zoomlevel: ".$zoom.")!");
      $zoom = 0;   
    }
    if ($width < 1 || $height < 1){
      Osm::traceText(DEBUG_ERROR, "e_map_size");
      Osm::traceText(DEBUG_INFO, "Error: ($width: ".$width." $height: ".$height.")!");
      $width = 450; $height = 300;
    }


	  $array_control = explode( ',', $control);
	  
    $array_control    = Osm_OpenLayers::checkControlType($array_control);

    // to manage several maps on the same page
    // create names with index
    static  $MapCounter = 0;
    $MapCounter += 1;
    $MapName = 'map_'.$MapCounter;
	
    Osm::traceText(DEBUG_INFO, "MapCounter = ".$MapCounter);
      
    // if we came up to here, let's load the image
    $output = '';	
    $output .= '<link rel="stylesheet" type="text/css" href="'.OSM_PLUGIN_URL.'/css/osm_map.css" />';
    $output .= '<style type="text/css">';
    if ($z_index != 'none'){ // fix for NextGen-Gallery
      $output .= '.entry .olMapViewport img {z-index: '.$z_index.' !important;}';   
      $output .= '.olControlNoSelect {z-index: '.$z_index.'+1.'.' !important;}';    
      $output .= '.olControlAttribution {z-index: '.$z_index.'+1.'.' !important;}';
    }
     
	$output .= '#'.$MapName.' {clear: both; padding: 0px; margin: 0px; border: 0px; width: 100%; height: 100%; margin-top:0px; margin-right:0px;margin-left:0px; margin-bottom:0px; left: 0px;}';
    $output .= '#'.$MapName.' img{clear: both; padding: 0px; margin: 0px; border: 0px; width: 100%; height: 100%; position: absolute; margin-top:0px; margin-right:0px;margin-left:0px; margin-bottom:0px;}';
	$output .= '</style>';

    $output .= '<div id="'.$MapName.'" class="OSM_IMG" style="width:'.$width.'px; height:'.$height.'px; overflow:hidden;padding:0px;border:'.$map_border.';">';

    
	if (Osm_LoadLibraryMode == SERVER_EMBEDDED){
	  if (OL_LIBS_LOADED == 0) {
    	$output .= '<script type="text/javascript" src="'.Osm_OL_LibraryLocation.'"></script>';
        define (OL_LIBS_LOADED, 1);
      }
  
    if ($type == 'Mapnik' || $type == 'Osmarender' || $type == 'CycleMap' || $type == 'All' || $type == 'AllOsm' || $type == 'Ext'){
	  if (OSM_LIBS_LOADED == 0) {
        $output .= '<script type="text/javascript" src="'.Osm_OSM_LibraryLocation.'"></script>';
        define (OSM_LIBS_LOADED, 1);
      }
    }
    elseif ($type == 'OpenSeaMap'){
	  if (OSM_LIBS_LOADED == 0) {
        $output .= '<script type="text/javascript" src="'.Osm_OSM_LibraryLocation.'"></script>';
        $output .= '<script type="text/javascript" src="'.Osm_harbours_LibraryLocation.'"></script>';
        $output .= '<script type="text/javascript" src="'.Osm_map_utils_LibraryLocation.'"></script>';
        $output .= '<script type="text/javascript" src="'.Osm_utilities_LibraryLocation.'"></script>';
        define (OSM_LIBS_LOADED, 1);
      }
    }
    elseif ($type == 'OpenWeatherMap'){
    	if (OSM_LIBS_LOADED == 0) {
    		$output .= '<script type="text/javascript" src="'.Osm_OSM_LibraryLocation.'"></script>';
    		$output .= '<script type="text/javascript" src="'.Osm_openweather_LibraryLocation.'"></script>';
    		define (OSM_LIBS_LOADED, 1);
    	}
    }    
    if ($type == 'GooglePhysical' || $type == 'GoogleStreet' || $type == 'GoogleHybrid' || $type == 'GoogleSatellite' || $type == 'All' || $type == 'AllGoogle' || $a_type == 'Ext' || $type == 'Google Physical' || $type == 'Google Street' || $type == 'Google Hybrid' || $type == 'Google Satellite'){
	  if (GOOGLE_LIBS_LOADED == 0) {
        $output .= '<script type="text/javascript" src="'.Osm_GOOGLE_LibraryLocation.'"></script>';
        define (GOOGLE_LIBS_LOADED, 1);
      }
    }
    $output .= '<script type="text/javascript" src="'.OSM_PLUGIN_JS_URL.'osm-plugin-lib.js"></script>';
  }
  elseif (Osm_LoadLibraryMode == SERVER_WP_ENQUEUE){
  // registered and loaded by WordPress
  }
  else{
    $this->traceText(DEBUG_ERROR, "e_library_config");
  }
      
  $extmap_init = 'new OpenLayers.Size('.width.', '.height.' )';

  $output .= '<script type="text/javascript">';
  $output .= '/* <![CDATA[ */';
  //$output .= 'jQuery(document).ready(';
  //$output .= 'function($) {';
  $output .= '(function($) {';
  $output .= Osm_OpenLayers::addOsmLayer("Zoomify", "ext", "0", "ext", "Zoomify", "Zoomify", $extmap_address, $extmap_init, $theme);

  // set center and zoom of the map
  //$output .= Osm_OpenLayers::setMapCenterAndZoom($lat, $long, $zoom);
  
  //$output .= '}';
  //$output .= ');';
  $output .= '})(jQuery)';
  $output .= '/* ]]> */';
  $output .= ' </script>';
  $output .= '</div>';
  return $output;
}	

 // add OSM-config page to Settings
  function admin_menu($not_used){
  // place the info in the plugin settings page
    add_options_page(__('OpenStreetMap Manager', 'Osm'), __('OSM', 'Osm'), 5, basename(__FILE__), array('Osm', 'options_page_osm'));
  }
  
  // ask WP to handle the loading of scripts
  // if it is not admin area
  function show_enqueue_script() {
    wp_enqueue_script(array ('jquery'));
	
	if (Osm_LoadLibraryMode == SERVER_EMBEDDED){
      // it is loaded when the map is displayed
	}
	elseif (Osm_LoadLibraryMode == SERVER_WP_ENQUEUE){
      //wp_enqueue_script('OlScript', 'http://www.openlayers.org/api/OpenLayers.js');
      //wp_enqueue_script('OsnScript', 'http://www.openstreetmap.org/openlayers/OpenStreetMap.js');
	  wp_enqueue_script('OlScript',Osm_OL_LibraryLocation);
      wp_enqueue_script('OsnScript',Osm_OSM_LibraryLocation);
      wp_enqueue_script('OsnScript',Osm_GOOGLE_LibraryLocation);
      wp_enqueue_script('OsnScript',OSM_PLUGIN_JS_URL.'osm-plugin-lib.js');
      define (OSM_LIBS_LOADED, 1);
      define (OL_LIBS_LOADED, 1);
      define (GOOGLE_LIBS_LOADED, 1);
	}
	else{
	  // Errormsg is traced at another place
	}	
  }
}	// End class Osm

$pOsm = new Osm();

// This is meant to be the interface used
// in your WP-template

// returns Lat data of coordination
function OSM_getCoordinateLat($a_import)
{
  global $post;

  $a_import = strtolower($a_import);
  if ($a_import == 'osm' || $a_import == 'osm_l'){
	list($lat, $lon) = explode(',', get_post_meta($post->ID, get_option('osm_custom_field','OSM_geo_data'), true));
  }
  else if ($a_import == 'wpgmg'){
	$lat = get_post_meta($post->ID, WPGMG_LAT, true);
  }
  else {
    $this->traceText(DEBUG_ERROR, "e_php_getlat_missing_arg");
    $lat = 0;
  }
  if ($lat != '') {
    return trim($lat);
  } 
  return '';
}

// returns Lon data
function OSM_getCoordinateLong($a_import)
{
	global $post;
  
  $a_import = strtolower($a_import);
  if ($a_import == 'osm' || $a_import == 'osm_l'){
	  list($lat, $lon) = explode(',', get_post_meta($post->ID, get_option('osm_custom_field','OSM_geo_data'), true));
  }
  else if ($a_import == 'wpgmg'){
	  list($lon) = get_post_meta($post->ID,WPGMG_LON, true);
  }
  else {
    $this->traceText(DEBUG_ERROR, "e_php_getlon_missing_arg");
    $lon = 0;
  }
  if ($lon != '') {
	  return trim($lon);
  } 
  return '';
}

function OSM_getOpenStreetMapUrl() {
  $zoom_level = get_option('osm_zoom_level','7');  
  $lat = $lat == ''? OSM_getCoordinateLat('osm') : $lat;
  $lon = $lon == ''? OSM_getCoordinateLong('osm'): $lon;
  return URL_INDEX.URL_LAT.$lat.URL_LON.$lon.URL_ZOOM_01.$zoom_level.URL_ZOOM_02;
}

function OSM_echoOpenStreetMapUrl(){
  echo OSM_getOpenStreetMapUrl() ;
}
// functions to display a map in your theme 
// by using the custom fields
// default values should be set only at sc_showMap()
function OSM_displayOpenStreetMap($a_widht, $a_hight, $a_zoom, $a_type){

  $atts = array ('width'        => $a_widht,
                 'height'       => $a_hight,
                 'type'         => $a_type,
                 'zoom'         => $a_zoom,
	               'control'		  => 'off');

  if ((OSM_getCoordinateLong("osm"))&&(OSM_getCoordinateLat("osm"))) { 
    echo OSM::sc_showMap($atts);
  }
}

function OSM_displayOpenStreetMapExt($a_widht, $a_hight, $a_zoom, $a_type, $a_control, $a_marker_name, $a_marker_height, $a_marker_width, $a_marker_text, $a_ov_map, $a_marker_focus = 0, $a_routing = 'No', $a_theme = 'dark'){

  $atts = array ('width'          => $a_widht,
                 'height'         => $a_hight,
                 'type'           => $a_type,
                 'zoom'           => $a_zoom,
                 'ov_map'         => $a_ov_map,
                 'marker_name'    => $a_marker_name,
                 'marker_height'  => $a_marker_height,
                 'marker_width'   => $a_marker_width,
                 'marker'         => OSM_getCoordinateLat("osm") . ',' . OSM_getCoordinateLong("osm") . ',' . $a_marker_text,
	         	 'control'        => $a_control,
                 'marker_focus'   => $a_marker_focus,
                 'theme'          => $a_theme,
                 'marker_routing' => $a_routing);

  if ((OSM_getCoordinateLong("osm"))&&(OSM_getCoordinateLat("osm"))) { 
    echo OSM::sc_showMap($atts);
  }
}
?>
