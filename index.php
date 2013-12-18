 <!DOCTYPE html> 
<html> 
<head> 
  <meta name="viewport" content="user-scalable=no,width=device-width" />
  <link rel="stylesheet" href="//code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
  <script src="//code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="//code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>

  <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
  <script type="text/javascript" src="js/jquery.ui.map.js"></script>

  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.css" />
 <!--[if lte IE 8]>
     <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.ie.css" />
 <![endif]-->

  <script src="http://cdn.leafletjs.com/leaflet-0.7.1/leaflet.js"></script>

  <style type="text/css">
    #map { 
      height: 180px; 
    }
  </style>
</head> 
<body> 
<div data-role="page" id="home">
  <div data-role="header">
    <h1 style="">OpenThessaloniki</h1>
  </div>
  <div data-role="content">
    <span>Latitude:</span> <span id="lat"></span> <br>
    <span>Longitude:</span> <span id="lng"></span> <br>
  </div>
  <!-- <div data-role="content" id="map_canvas" style="height: 300px;">
    
  </div> -->
  <div id="map"></div>
</div>

<script type="text/javascript">
 
</script>
</body>
</html>