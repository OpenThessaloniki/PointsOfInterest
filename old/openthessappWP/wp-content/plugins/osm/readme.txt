=== OSM - OpenStreetMap ===
Contributors: MiKa
Tags: map, OpenStreetMap, Google Maps, googlemaps, geo, KML, GPX, geotag, geolocation, geocache, geocaching, OSM, travelogue, template tag, travelblog, OpenLayers, Open Layers, Open Street Map, CloudMade, YourNavigation, OpenRouteService, marker, POI, geocode, geotagging, google earth, Leaflet, location, Route, Tracks, WMS, Open Sea Map, OpenWeatherMap, Weather, OpenSeaMap
Requires at least: 2.8
Tested up to: 3.8
Stable tag: 2.4.1

OpenStreetMap / OpenSeaMap plugin to embed maps. No API key! No Google API!
Customize your maps with routes, marker, geotagged posts, weather, icons ... 

== Description ==
If you want to download the OSM-plugin you are right here!

If you want to get detailed information about the OSM-plugin visit these pages:

* Homepage: [WP-OSM-Plugin](http://wp-osm-plugin.hanblog.net/ "OSM-plugin")
* Forum EN: [Forum EN](http://wp-osm-plugin.hanblog.net/forum/forum-en/ "OSM-plugin forum EN")
* Forum DE: [Forum DE](http://wp-osm-plugin.hanblog.net/forum/forum-de/ "OSM-plugin forum DE")
* Blog: [HanBlog.net](http://wp-osm-plugin.hanblog.net/blog "WP OSM Plugin Blog")

If you are facing difficulties after an update, get the previous OSM Plugin version at [WP-OSM-Plugin Page](http://wp-osm-plugin.hanblog.net/ "OSM-plugin").

Features of the OSM-plugin:

* embeds OpenStreetMap, OpenSeaMap and Google Maps maps to your posts/pages
* embeds external maps to your posts/pages
* visualizes weather in a map
* visualizes several tracks / routes in different colours (gpx and kml)
* visualizes popup-html-markers (list in txt-file or single in the shortcode)
* visualize all geotagged posts of your blog in one map with/without a link to the post
* use custom field to add geolocation to your blog
* geo data are written to html-meta tags of your blog
* uses OpenLayers Library
* extends Mediathek rights to upload GPX files

Languages:

* English
* Deutsch
* Japanese [by Sykane]
* French [by Tounoki]

Licenses of the maps:

* OpenStreetMap: [OpenStreetMap License](http://wiki.openstreetmap.org/wiki/OpenStreetMap_License) 
* Google Maps: [Google Maps Terms of Service](http://code.google.com/intl/de-DE/apis/maps/terms.html)
* OpenWeatherMap: [OpenWeatherMap License](http://openweathermap.org/copyright)
* Ext Maps: Depends on the map you are including - check it before including it!

== Installation ==

1. Upload OSM folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Generate the OSM shortcode when you write your post / page 

IMPORTANT: 
Personal data (eg. gpx files) must not be stored in the plugins/osm folder but in the upload folder!

== Frequently Asked Questions ==

= Do I need any key or any registration to show maps in my blog =

No!

= I do not see my gpx file / marker file in the map ? =

The file has to be located at the same adress as your blog.
There must be not format tag (like href ...) in the shortcode.


== Changelog ==
= 2.4.1 =
* FIX: New Server for OpenSeaMap
* NEW: load OpenLayers locally (preperatin for SSL in backend)
= 2.4 =
* NEW: OpenWeatherMap integration
* NEW: geotag custom field name predefined to OSM_geo_data
* NEW: Default value for php function zoomlevel
= 2.3 =
* NEW: php call supports theme for map integration
* NEW: marker_focus (5) added for map collection added
* NEW: New arguments to filter:
       post_type, custom_taxonomy, import_osm_custom_tax_incl_name
* NEW: add several txt files in one map:
       marker_file_list 
= 2.2 =
* FIX: style rules are limited for OSM images
* FIX: html comment is set if post / page is geotagged
= 2.1 =
* NEW: ShortCode Generator also at pages
* FIX: Pink Stripes at adaptive WP theme
* FIX: Center of the map is not influenced by marker anymore
= 2.0 =
* NEW: ShortCode Generator at edit posts
* NEW: High precision to set a marker at the backend
* NEW: Display circles / discs in your map
* NEW: Upload and display KML files (Popup Points)
* NEW: Draw line between geotagged posts automatically 
* NEW: OpenLayers 2.12
* NEW: French translation
* NEW: Predefined OSM themes for control and map border
= 1.3 =
* NEW: Support of OpenSeaMap
= 1.2.3 =
* FIX: Link OpenLayers 2.11 as GML layer is not supported anymore
= 1.2.2 =
* FIX: Shortcode generator did not show anything since Osmarender service is not supported anymore.
= 1.2.1 =
* FIX: Warning: explode() expects parameter 2 ... on line 276
= 1.2 =
* FIX: Warning: explode() expects parameter 2 ... on line 272
* NEW: use private theme for cntrols in /wp-content/uploads/osm/theme/
* NEW: language: Japan
= 1.1.1 =
* NEW: extended rights to upload GPX files in Mediathek
* NEW: CSS file 
* FIX: WP-Theme zBench fix
* FIX: HTML tag if post is geotagged
* FIX: z-index at shortcode generator
= 1.1 =
* NEW: add the text for popup marker directly in the shortcode generator (settings => OSM)
* NEW: add a link to a routing service (settings => OSM)
* NEW: set the z-index if needed (eg. for Next Gen Gallery)
* NEW: choose a theme for the control icons
* NEW: add the mouse position directly in the shortcode generator (settins => OSM)
* NEW: plugin size less than 100kB
= 1.0 =
* NEW: Internationalization (languages: EN, DE)
* FIX: HTML code for geotagged posts
* FIX: WP-Theme Twenty Eleven
= 0.9.6 =
* NEW: shortcode generator at Settings=>OSM extented
* NEW: marker filename is used for the map picker
* FIX: Warning: split() expects parameter 2 .. on line 211
* FIX: car icon size corrected
= 0.9.5 =
* NEW: mark all geotagged posts can be filtered by category
* NEW: map_border tag added to set border around the OSM map
* NEW: marker_focus tag added to adjust the marker
* NEW: gpx filename is used for the map picker
* FIX: style correction for some WP-themes (eg Suffusion)
= 0.9.4 =
* NEW: LIBS of diff. map types are loaded only when needed
* NEW: Shortcodegenerator at backend extented to get the chosen maptype
* FIX: Customfield marker error at IE
= 0.9.3 =
* NEW: added Google Maps: Sattelite, Street, Hybrid, Physical
= 0.9.2 =
* NEW: added osm_l tag for map with linked marker to tagged posts.
* FIX: correct offset for pin-icons and non-osm-icons
* FIX: style correction for some WP-themes
= 0.9.1 =
* NEW: popup marker with link for the map displaying all posts/pages of the blog
* FIX: licenselink is not displayed if an external map is loaded
* FIX: some WP-thems showed grids/lines in the map
* FIX: bug if several maps were shown at the same time
= 0.9 =
* NEW: display several gpx files with diff. colours in one map
* NEW: template tags to be used in your theme to show maps at geotagged posts
* NEW: extend zoom level for mapnik to 18
= 0.8.7 =
* FIX: HTML-PopUp-Marker without Customfield-Text produced 'Array' (WP 2.9)
* FIX: size of bicycle icon
= 0.8.6 =
* NEW: performance improvement: needed libraries are loaded only if maps are displayed - improves the whole blog!
* NEW: external maps can be included instead of standard OSM-maps
* NEW: controls (scale, scaleline, mouseposition) can be included by tag
= 0.8.5 =
* NEW: HTML marker for PopUps
= 0.8.4 =
* FIX: plugin folder changed
* FIX: some internal stuff
= 0.8.3 =
* FIX: correct offset for indiv. marker
= 0.8.1  = 
* FIX: check whether gcstats is activated or not
= 0.8.0  = 
* NEW: separate file for option and import; gcstats support; add marker in option page
= 0.7.0  = 
* NEW: shortcode generator in option page added
= 0.6.0  = 
* NEW: options got prefix "osm_", therefore settings have to be made again at upgrade
= 0.5.0  = 
* NEW:added type at shortcode (Mapnik, Osmarender, CycleMap, All) ; overviewmap in shortcode
= 0.4.0  = 
* NEW: added KML support and colour interface for tracks
= 0.3.0  = 
* NEW: added "marker_all_posts" at shortcode to set a marker for all posts
= 0.2.0  = 
* NEW: loading GPX files with shortcode
