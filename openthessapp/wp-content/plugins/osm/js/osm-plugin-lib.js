/*
  OSM OpenLayers for OSM wordpress plugin
  plugin: http://wp-osm-plugin.HanBlog.net
  blog:   http://www.HanBlog.net
*/
function osm_MarkerPopUpClick(a_evt)
{
    if (this.popup == null){
        this.popup = this.createPopup(this.closeBox);
        map.addPopup(this.popup);
        this.popup.show();
    }
    else{// Close all pop-ups
        for (var i = 0; i < map.popups.length; i++){
            map.popups[i].hide();
        }
        this.popup.toggle();
    }
    OpenLayers.Event.stop(a_evt);
}


// Display Disc / Circles
function osm_getFeatureDiscCenter(a_discLayer, a_lon, a_lat, a_radius, a_centeropac, a_centercol, a_strw, a_strcol, a_stropac, a_fillcol, a_fillopac) 
{
   var lonLat = new OpenLayers.LonLat(a_lon, a_lat).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());

    var discStyle    = { strokeColor: a_strcol,
                         strokeOpacity: a_stropac,
                         strokeWidth: a_strw,
                         fillColor: a_fillcol,
                         fillOpacity: a_fillopac
                       };
    var centerStyle  = { strokeColor: a_centercol,
                         strokeOpacity: a_centeropac,
                         strokeWidth: a_strw,
                         fillColor: a_centercol,
                         fillOpacity: a_centeropac
                       };

    var disc = OpenLayers.Geometry.Polygon.createRegularPolygon(
                                             new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat),
                                             a_radius,
                                             200); // nombre de faces
                 
    var center = OpenLayers.Geometry.Polygon.createRegularPolygon(
                                             new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat),
                                             1,   // taille dans lunite de la carte
                                             5);  // nombre de faces
                 

    var featureDisc   = new OpenLayers.Feature.Vector(disc,null,discStyle);
    var featureCenter = new OpenLayers.Feature.Vector(center,null,centerStyle);
    a_discLayer.addFeatures([featureDisc,featureCenter]);
}

// Draw line
function osm_setLinePoints(a_lineLayer, a_strw, a_strcol, a_stropac, a_Points)
{
  var Points = new Array();

  for (var i = 0; i < a_Points.length; i++) {
    var lonLat = new OpenLayers.LonLat(a_Points[i]["lon"], a_Points[i]["lat"]).transform(new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());
    Points[i] = new OpenLayers.Geometry.Point(lonLat.lon, lonLat.lat);
  }
  var line = new OpenLayers.Geometry.LineString(Points);
  var style = { 
    strokeColor: a_strcol, 
    strokeOpacity: a_stropac, 
    strokeWidth: a_strw 
   };

  var lineFeature = new OpenLayers.Feature.Vector(line, null, style);
  a_lineLayer.addFeatures([lineFeature]);
}

// Functions for KML files
function osm_onPopupClose(evt) {
  select.unselectAll();
}

function osm_onFeatureSelect(event) {
  var feature = event.feature;
  var content = "<b>"+feature.attributes.name + "</b> <br>" + feature.attributes.description;

  if (content.search("<script") != -1) {
     content = "Content contained Javascript! Escaped content below.<br>" + content.replace(/</g, "&lt;");
  }
  
  popup = new OpenLayers.Popup.FramedCloud("OSM Plugin",
    feature.geometry.getBounds().getCenterLonLat(),
      new OpenLayers.Size(100,100),
      content,
      null, true, osm_onPopupClose);
  feature.popup = popup;
  map.addPopup(popup);
 }

function osm_onFeatureUnselect(event) {
  var feature = event.feature;
  if(feature.popup) {
    map.removePopup(feature.popup);
    feature.popup.destroy();
    delete feature.popup;
  }   
}

// Clickhandler / Shorcode generator

function osm_getRadioValue(a_Form){
  if (a_Form == "Markerform"){
    for (var i=0; i < document.Markerform.Art.length; i++){
      if (document.Markerform.Art[i].checked){
        var rad_val = document.Markerform.Art[i].value;
        return rad_val;
      }
    }
    return "undefined";
  }
  else if (a_Form == "GPXcolourform"){
    for (var i=0; i < document.GPXcolourform.Gpx_colour.length; i++){
      if (document.GPXcolourform.Gpx_colour[i].checked){
        var rad_val = document.GPXcolourform.Gpx_colour[i].value;
        return rad_val;
      }
    }
    return "undefined";
  }
  else if (a_Form == "Bordercolourform"){
    for (var i=0; i < document.Bordercolourform.Border_colour.length; i++){
      if (document.Bordercolourform.Border_colour[i].checked){
        var rad_val = document.Bordercolourform.Border_colour[i].value;
        return rad_val;
      }
    }
    return "undefined";
  }
  else if (a_Form == "Naviform"){
    for (var i=0; i < document.Naviform.Navi_Link.length; i++){
      if (document.Naviform.Navi_Link[i].checked){
        var rad_val = document.Naviform.Navi_Link[i].value;
        return rad_val;
      }
    }
    return "undefined";
  }
  else if (a_Form == "ControlStyleform"){
    for (var i=0; i < document.ControlStyleform.Cntrl_style.length; i++){
      if (document.ControlStyleform.Cntrl_style[i].checked){
        var rad_val = document.ControlStyleform.Cntrl_style[i].value;
        return rad_val;
      }
    }
    return "undefined";
  }
  return "not implemented";
}

