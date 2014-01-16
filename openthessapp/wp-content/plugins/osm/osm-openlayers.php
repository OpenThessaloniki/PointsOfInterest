<?php
/*
OpenLayers Abstraction for WordPress OSM Plugin
Plugin URI: http://wp-osm-plugin.HanBlog.net
*/
?>
<?php

class Osm_OpenLayers
{
  //support different types of GML Layers
  function addGmlLayer($a_LayerName, $a_FileName, $a_Colour, $a_Type)
  {
    Osm::traceText(DEBUG_INFO, "addGmlLayer(".$a_LayerName.",".$a_FileName.",".$a_Colour.",".$a_Type.")");
    
    // Add the Layer with the GPX Track
    $Layer .= '  var lgml = new OpenLayers.Layer.Vector("'.$a_LayerName.'",{';
    $Layer .= '   strategies: [new OpenLayers.Strategy.Fixed()],';
    $Layer .= '	  protocol: new OpenLayers.Protocol.HTTP({';
    $Layer .= '	   url: "'.$a_FileName.'",';


    if ($a_Type == 'GPX'){
    $Layer .= '	   format: new OpenLayers.Format.GPX()';
    $Layer .= '	  }),';
    
    $Layer .= '    style: {strokeColor: "'.$a_Colour.'", strokeWidth: 5, strokeOpacity: 0.5},';
    $Layer .= '    projection: new OpenLayers.Projection("EPSG:4326")';
    $Layer .= '  });';
    $Layer .= '  map.addLayer(lgml);';
    }
    else if ($a_Type == 'KML'){
    $Layer .= '	   format: new OpenLayers.Format.KML({';
    $Layer .= '	   extractStyles: true,';
    $Layer .= '	   extractAttributes: true,';
    $Layer .= '	   maxDepth: 2})';
    $Layer .= '	  }),';
    
    $Layer .= '    style: {strokeColor: "'.$a_Colour.'", strokeWidth: 5, strokeOpacity: 0.5},';
    $Layer .= '    projection: new OpenLayers.Projection("EPSG:4326")';
    $Layer .= '  });';
    $Layer .= '  map.addLayer(lgml);';

//+++
    $Layer .= '            select = new OpenLayers.Control.SelectFeature(lgml);';
            
    $Layer .= '            lgml.events.on({';
    $Layer .= '                "featureselected": osm_onFeatureSelect,';
    $Layer .= '                "featureunselected": osm_onFeatureUnselect';
    $Layer .= '            });';

    $Layer .= '            map.addControl(select);';
    $Layer .= '            select.activate();   ';
  //  $Layer .= '            map.zoomToExtent(new OpenLayers.Bounds(68.774414,11.381836,123.662109,34.628906));';
    }                 
    return $Layer;
  }

// support different types of GML Layers
  function addOsmLayer($a_LayerName, $a_Type, $a_OverviewMapZoom, $a_MapControl, $a_ExtType, $a_ExtName, $a_ExtAddress, $a_ExtInit, $a_theme)
  {
    Osm::traceText(DEBUG_INFO, "addOsmLayer(".$a_LayerName.",".$a_Type.",".$a_OverviewMapZoom.")");

    if ($a_theme == 'private'){
      $Layer .= ' OpenLayers.ImgPath = "'.OSM_OPENLAYERS_THEMES_URL.'";';
    }
    else {
      $Layer .= ' OpenLayers.ImgPath = "'.OSM_PLUGIN_THEMES_URL.$a_theme.'/";';
    }
    $Layer .= ' function getTileURL(bounds) {';
    $Layer .= ' var res = this.map.getResolution();';
    $Layer .= ' var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));';
    $Layer .= ' var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));';
    $Layer .= ' var z = this.map.getZoom();';
    $Layer .= ' var limit = Math.pow(2, z);';
    $Layer .= ' if (y < 0 || y >= limit) {';
    $Layer .= ' return null;';
    $Layer .= ' } else {';
    $Layer .= ' x = ((x % limit) + limit) % limit;';
    $Layer .= ' url = this.url;';
    $Layer .= ' path= z + "/" + x + "/" + y + "." + this.type;';
    $Layer .= ' if (url instanceof Array) {';
    $Layer .= ' url = this.selectUrl(path, url);';	
    $Layer .= ' }';
    $Layer .= ' return url+path;';
    $Layer .= ' }';
    $Layer .= ' }';
    $Layer .= ' map = new OpenLayers.Map ("'.$a_LayerName.'", {';
    $Layer .= '            controls:[';
    if (($a_MapControl[0] != 'off') && (strtolower($a_Type)!= 'ext')) {
      $Layer .= '              new OpenLayers.Control.Navigation(),';
      $Layer .= '              new OpenLayers.Control.PanZoom(),';
      $Layer .= '              new OpenLayers.Control.Attribution()';
    }
    else if (($a_MapControl[0] == 'off') && (strtolower($a_Type)!= 'ext')){
      $Layer .= '              new OpenLayers.Control.Attribution()';
    }
    else if (($a_MapControl[0] != 'off') && (strtolower($a_Type)== 'ext')){
      $Layer .= '              new OpenLayers.Control.Navigation(),';
      $Layer .= '              new OpenLayers.Control.PanZoom(),';
     $Layer .= '              new OpenLayers.Control.Attribution()';
    }
    else if (($a_MapControl[0] == 'off') && (strtolower($a_Type)== 'ext')){
      // there is nothing to do
    }
    else {
      Osm::traceText(DEBUG_ERROR, "addOsmLayer(".$a_MapControl[0].",".$a_Type.")");
    }
    $Layer .= '              ],';
    $Layer .= '          maxExtent: new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34),';
    $Layer .= '          maxResolution: 156543.0399,';
    $Layer .= '          numZoomLevels: 19,';
    $Layer .= '          units: "m",';
    $Layer .= '          projection: new OpenLayers.Projection("EPSG:900913"),';
    $Layer .= '           displayProjection: new OpenLayers.Projection("EPSG:4326")';
    $Layer .= '      } );';
    if ($a_Type == 'All'){
      $Layer .= 'var layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");';
      $Layer .= 'var layerCycle  = new OpenLayers.Layer.OSM.CycleMap("CycleMap");';
      $Layer .= 'var layerGooglePhysical   = new OpenLayers.Layer.Google("Google Physical", {type: google.maps.MapTypeId.TERRAIN} );';
      $Layer .= 'var layerGoogleStreet     = new OpenLayers.Layer.Google("Google Street", {type: google.maps.MapTypeId.ROADMAP} );';
      $Layer .= 'var layerGoogleHybrid     = new OpenLayers.Layer.Google("Google Hybrid", {type: google.maps.MapTypeId.HYBRID} );';
      $Layer .= 'var layerGoogleSatellite  = new OpenLayers.Layer.Google("Google Satellite", {type: google.maps.MapTypeId.SATELLITE} );';
      $Layer .= 'var layerOSM_Attr = new OpenLayers.Layer.Vector("OSM-plugin",{attribution:"<a href=\"http://wp-osm-plugin.hanblog.net\">OSM plugin</a>"});';
      $Layer .= 'map.addLayers([layerMapnik, layerCycle, layerGooglePhysical, layerGoogleStreet, layerGoogleHybrid, layerGoogleSatellite, layerOSM_Attr]);';
      $Layer .= 'map.addControl(new OpenLayers.Control.LayerSwitcher());';
    }
    else if ($a_Type == 'AllOsm'){
      $Layer .= 'var layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik", {wrapDateLine: true});';
//      $Layer .= 'var layerMapnik = new OpenLayers.Layer.OSM.Mapnik("Mapnik");';
      $Layer .= 'var layerCycle  = new OpenLayers.Layer.OSM.CycleMap("CycleMap");';
      $Layer .= 'var layerOSM_Attr = new OpenLayers.Layer.Vector("OSM-plugin",{attribution:"<a href=\"http://wp-osm-plugin.hanblog.net\">OSM plugin</a>"});';
      $Layer .= 'map.addLayers([layerMapnik, layerCycle, layerOSM_Attr]);';
      $Layer .= 'map.addControl(new OpenLayers.Control.LayerSwitcher());';
    }
    else if ($a_Type == 'OpenSeaMap'){
      $Layer .= 'var layerMapnik   = new OpenLayers.Layer.OSM.Mapnik("Mapnik");';
      $Layer .= 'var layerSeamark  = new OpenLayers.Layer.TMS("Seezeichen", "http://t1.openseamap.org/seamark/", { numZoomLevels: 18, type: "png", getURL: getTileURL, isBaseLayer: false, displayOutsideMaxExtent: true});';
      $Layer .= 'var layerPois = new OpenLayers.Layer.Vector("Haefen", { projection: new OpenLayers.Projection("EPSG:4326"), visibility: true, displayOutsideMaxExtent:true});';
      $Layer .= 'layerPois.setOpacity(0.8);';
      $Layer .= 'var layerOSM_Attr = new OpenLayers.Layer.Vector("OSM-plugin",{attribution:"<a href=\"http://wp-osm-plugin.hanblog.net\">OSM plugin</a>"});';
      $Layer .= 'map.addLayers([layerMapnik, layerSeamark, layerPois, layerOSM_Attr]);';
      $Layer .= 'map.addControl(new OpenLayers.Control.LayerSwitcher());';
    }
    else if ($a_Type == 'OpenWeatherMap'){
    	$Layer .= 'var layerMapnik   = new OpenLayers.Layer.OSM.Mapnik("Mapnik");';
    	$Layer .= '	var layerWeather = new OpenLayers.Layer.Vector.OWMWeather("Weather");';
    	$Layer .= 'var layerOSM_Attr = new OpenLayers.Layer.Vector("OSM-plugin",{attribution:"<a href=\"http://wp-osm-plugin.hanblog.net\">OSM plugin</a>"});';
    	$Layer .= 'map.addLayers([layerMapnik, layerWeather,layerOSM_Attr]);';
    	$Layer .= 'map.addControl(new OpenLayers.Control.LayerSwitcher());';
    }
    else if ($a_Type == 'AllGoogle'){
      $Layer .= 'var layerGooglePhysical   = new OpenLayers.Layer.Google("Google Physical", {type: google.maps.MapTypeId.TERRAIN} );';
      $Layer .= 'var layerGoogleStreet     = new OpenLayers.Layer.Google("Google Street", {type: google.maps.MapTypeId.ROADMAP} );';
      $Layer .= 'var layerGoogleHybrid     = new OpenLayers.Layer.Google("Google Hybrid", {type: google.maps.MapTypeId.HYBRID} );';
      $Layer .= 'var layerGoogleSatellite  = new OpenLayers.Layer.Google("Google Satellite", {type: google.maps.MapTypeId.SATELLITE} );';
      $Layer .= 'var layerOSM_Attr = new OpenLayers.Layer.Vector("OSM-plugin",{attribution:"<a href=\"http://wp-osm-plugin.hanblog.net\">OSM plugin</a>"});';
      $Layer .= 'map.addLayers([layerGooglePhysical, layerGoogleStreet, layerGoogleHybrid, layerGoogleSatellite, layerOSM_Attr]);';
      $Layer .= 'map.addControl(new OpenLayers.Control.LayerSwitcher());';
    }
    else{
      if ($a_Type == 'Mapnik'){
        $Layer .= 'var lmap = new OpenLayers.Layer.OSM.Mapnik("Mapnik");';
      } 
      else if ($a_Type == 'Osmarender'){
        $Layer .= 'var lmap = new OpenLayers.Layer.OSM.Osmarender("Osmarender");';
      } 
      else if ($a_Type == 'CycleMap'){
        $Layer .= 'var lmap = new OpenLayers.Layer.OSM.CycleMap("CycleMap");';
      }
      else if (($a_Type == 'GooglePhysical') || ($a_Type == 'Google Physical')){
        $Layer .= 'var lmap = new OpenLayers.Layer.Google("Google Physical", {type: google.maps.MapTypeId.TERRAIN} );';
      }
      else if (($a_Type == 'GoogleStreet') || ($a_Type == 'Google Street')){
        $Layer .= 'var lmap = new OpenLayers.Layer.Google("Google Street", {type: google.maps.MapTypeId.ROADMAP} );';
      }
      else if (($a_Type == 'GoogleHybrid') || ($a_Type == 'Google Hybrid')){
        $Layer .= 'var lmap = new OpenLayers.Layer.Google("Google Hybrid", {type: google.maps.MapTypeId.HYBRID} );';
      }
      else if (($a_Type == 'GoogleSatellite') || ($a_Type == 'Google Satellite')){
        $Layer .= 'var lmap = new OpenLayers.Layer.Google("Google Satellite", {type: google.maps.MapTypeId.SATELLITE} );';
      }
      else if (($a_Type == 'Ext') || ($a_Type == 'ext')) {
        $Layer .= 'var lmap = new OpenLayers.Layer.'.$a_ExtType.'("'.$a_ExtName.'","'.$a_ExtAddress.'",{'.$a_ExtInit.', attribution: "OpenLayers with"});';
      }
      $Layer .= 'var layerOSM_Attr = new OpenLayers.Layer.Vector("OSM-plugin",{attribution:"<a href=\"http://wp-osm-plugin.hanblog.net\">OSM plugin</a>"});';
      $Layer .= 'map.addLayers([lmap, layerOSM_Attr]);';
    }
	 
	if ($a_MapControl[0] != 'No'){
	  foreach ( $a_MapControl as $MapControl ){
	  	$MapControl = strtolower($MapControl);
	    if ( $MapControl == 'scaleline'){
	      $Layer .= 'map.addControl(new OpenLayers.Control.ScaleLine());';
	    }
	    elseif ($MapControl == 'scale'){
		  $Layer .= 'map.addControl(new OpenLayers.Control.Scale());';
		}
		elseif ($MapControl == 'mouseposition'){
		  $Layer .= 'map.addControl(new OpenLayers.Control.MousePosition({displayProjection: new OpenLayers.Projection("EPSG:4326")}));';
		}
		// add more if needed!
	  }
	}

    // add the overview map
    if ($a_OverviewMapZoom >= 0){  
      $Layer .= 'layer_ov = new OpenLayers.Layer.OSM.Mapnik("Mapnik");';
      if ($a_OverviewMapZoom > 0 && $a_OverviewMapZoom < 18 ){
        $Layer .= 'var options = {
                      layers: [layer_ov],
                      mapOptions: {numZoomLevels: '.$a_OverviewMapZoom.'}
                      };';
      }
      else{
        $Layer .= 'var options = {layers: [layer_ov]};';
      }
      $Layer .= 'map.addControl(new OpenLayers.Control.OverviewMap(options));';
    }
	
	// http://trac.openlayers.org/changeset/9023
    $Layer .= '    function osm_getTileURL(bounds) {';
    $Layer .= '        var res = this.map.getResolution();';
    $Layer .= '        var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));';
    $Layer .= '        var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));';
    $Layer .= '        var z = this.map.getZoom();';
    $Layer .= '        var limit = Math.pow(2, z);';

    $Layer .= '        if (y < 0 || y >= limit) {';
    $Layer .= '            return OpenLayers.Util.getImagesLocation() + "404.png";';
    $Layer .= '        } else {';
    $Layer .= '            x = ((x % limit) + limit) % limit;';
    $Layer .= '            return this.url + z + "/" + x + "/" + y + "." + this.type;';
    $Layer .= '        }';
    $Layer .= '    }';
	
    return $Layer;
  }

  function AddClickHandler($a_msgBox)
  {
    Osm::traceText(DEBUG_INFO, "AddClickHandler(".$a_msgBox.")");
    $a_msgBox = strtolower($a_msgBox);

//++ //++ set marker
    $Layer .= '  var markerslayer = new OpenLayers.Layer.Markers( "Markers" );';
   $Layer .= '	 var size = new OpenLayers.Size(21,25);';
    $Layer .= '  var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);';
    $Layer .= '  var click_icon = new OpenLayers.Icon("http://www.openlayers.org/dev/img/marker.png",size,offset); ';  
    $Layer .= '  map.addLayer(markerslayer);';
//--
    
    $Layer .= 'OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {';               
    $Layer .= ' 	                defaultHandlerOptions: {';
    $Layer .= ' 	                    "single": true,';
    $Layer .= ' 	                    "double": false,';
    $Layer .= ' 	                    "pixelTolerance": 0,';
    $Layer .= ' 	                    "stopSingle": false,';
    $Layer .= ' 	                    "stopDouble": false';
    $Layer .= ' 	                },';

    $Layer .= ' 	                initialize: function(options) {';
    $Layer .= ' 	                    this.handlerOptions = OpenLayers.Util.extend(';
    $Layer .= ' 	                        {}, this.defaultHandlerOptions';
    $Layer .= ' 	                    );';
    $Layer .= ' 	                    OpenLayers.Control.prototype.initialize.apply(';
    $Layer .= ' 	                        this, arguments';
    $Layer .= ' 	                    );';
    $Layer .= ' 	                    this.handler = new OpenLayers.Handler.Click(';
    $Layer .= ' 	                        this, {';
    $Layer .= ' 	                            "click": this.trigger';
    $Layer .= ' 	                        }, this.handlerOptions';
    $Layer .= ' 	                    );';
    $Layer .= ' 	                },';

    $Layer .= ' 	                trigger: function(e) {';
    $Layer .= '                     var LayerName =    map.baseLayer.name; ';  
    $Layer .= ' 	            var Centerlonlat = map.getCenter(e.xy).clone();';
    $Layer .= ' 	            var Clicklonlat = map.getLonLatFromViewPortPx(e.xy);';
    $Layer .= ' 	            var zoom = map.getZoom(e.xy);';
    $Layer .= '                     Centerlonlat.transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326"));';
    $Layer .= '                     Clicklonlat.transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326"));';
    $Layer .= '                     Centerlonlat.lat = Math.round( Centerlonlat.lat * 1000. ) / 1000.;'; // get the center of the map
    $Layer .= '                     Centerlonlat.lon = Math.round( Centerlonlat.lon * 1000. ) / 1000.;';
    $Layer .= '                     Clicklonlat.lat = Math.round( Clicklonlat.lat * 100000. ) / 100000.;';   // get the position for the marker
    $Layer .= '                     Clicklonlat.lon = Math.round( Clicklonlat.lon * 100000. ) / 100000.;';  

    if ($a_msgBox == 'sc_gen'){  
    $Layer .= ' div = document.getElementById("ShortCode_Div");';

    $Layer .= ' var MarkerName    = osm_getRadioValue("Markerform");';
    $Layer .= ' var GpxColour     = osm_getRadioValue("GPXcolourform");';
    $Layer .= ' var BorderColour  = osm_getRadioValue("Bordercolourform");';
    $Layer .= ' var NaviName      = osm_getRadioValue("Naviform");';
    $Layer .= ' var CntrStyle     = osm_getRadioValue("ControlStyleform");';

    $Layer .= ' MarkerField = "";';
    $Layer .= ' NaviField = "";';
    $Layer .= ' MarkerTextField_01 = "";';
    $Layer .= ' MarkerTextField_02 = "";';
    $Layer .= ' MarkerTextField_03 = "";';
    $Layer .= ' MarkerTextField_04 = "";';
    $Layer .= ' GpxFileField = "";';
    $Layer .= ' GpxColourField = "";';
    $Layer .= ' MarkerFileField = "";';
    $Layer .= ' MapControlField = "";';
    $Layer .= ' BorderColourField = "";';
    $Layer .= ' ControlStyleField = "";';
    $Layer .= ' ZIndexField = "";';

    $Layer .= ' if (MarkerName != "undefined"){';
    $Layer .= '   MarkerField = " marker=\""+Clicklonlat.lat+","+Clicklonlat.lon+
"\" marker_name=\"" + MarkerName + "\"";';  

    $Layer .= '   if (NaviName != "undefined"){';
    $Layer .= '     NaviField = " marker_routing=\""+ NaviName + "\"";';  
    $Layer .= '    }';

    $Layer .= '   if (document.Markertextform.MarkerText_01.value != "Max Mustermann"){';
    $Layer .= '     MarkerTextField_01 = " m_txt_01=\""+ document.Markertextform.MarkerText_01.value + "\"";';  
    $Layer .= '   }';    
    $Layer .= '   if (document.Markertextform.MarkerText_02.value != "Musterstr. 90"){';
    $Layer .= '    MarkerTextField_02 = " m_txt_02=\""+ document.Markertextform.MarkerText_02.value + "\"";';  
    $Layer .= '   }';
    $Layer .= '   if (document.Markertextform.MarkerText_03.value != "1020 Mustercity"){';
    $Layer .= '    MarkerTextField_03 = " m_txt_03=\""+ document.Markertextform.MarkerText_03.value + "\"";';  
    $Layer .= '   }';
    $Layer .= '   if (document.Markertextform.MarkerText_04.value != "MusterCountry"){';
    $Layer .= '    MarkerTextField_04 = " m_txt_04=\""+ document.Markertextform.MarkerText_04.value + "\"";';  
    $Layer .= '    }';

    $Layer .= '  }'; // if (MarkerName != "undefined")

    $Layer .= ' if (document.GPXfileform.GpxFile.value != "http://"){';
    $Layer .= '   GpxFileField = " gpx_file=\""+ document.GPXfileform.GpxFile.value + "\"";';  
    $Layer .= '  }';
    $Layer .= ' if (GpxColour != "undefined"){';
    $Layer .= '   GpxColourField = " gpx_colour=\""+ GpxColour + "\"";';  
    $Layer .= '  }';
    $Layer .= ' if (CntrStyle != "undefined"){';
    $Layer .= '   ControlStyleField = " theme=\""+ CntrStyle + "\"";';  
    $Layer .= '  }';
    $Layer .= ' if (document.ZIndexform.ZIndex.checked){';
    $Layer .= '   ZIndexField = " z_index=\""+ document.ZIndexform.ZIndex.value + "\"";';  
    $Layer .= '  }';    
    $Layer .= ' if (document.Markerfileform.MarkerFile.value != "http://"){';
    $Layer .= '   MarkerFileField = " marker_file=\""+ document.Markerfileform.MarkerFile.value + "\"";';  
    $Layer .= '  }';

    $Layer .= ' if (document.MapControlform.MapControl.checked){';
    $Layer .= '   MapControlField = " control=\""+ document.MapControlform.MapControl.value;';  
    $Layer .= '  }';
    $Layer .= ' if (document.MapControlform.Mouseposition.checked){';
    $Layer .= '   if (document.MapControlform.MapControl.checked){';
    $Layer .= '     MapControlField = MapControlField + "," + document.MapControlform.Mouseposition.value;';  
    $Layer .= '    }';
    $Layer .= '    else{';
    $Layer .= '      MapControlField = " control=\""+ document.MapControlform.Mouseposition.value;';  
    $Layer .= '    }';
    $Layer .= '  }';
    $Layer .= ' if ((document.MapControlform.Mouseposition.checked) || (document.MapControlform.MapControl.checked)) {';
    $Layer .= '  MapControlField = MapControlField + "\"";';
    $Layer .= '  }';

    $Layer .= ' if (BorderColour != "undefined"){';
    $Layer .= '   BorderColourField = " map_border=\"thin solid "+ BorderColour + "\"";';  
    $Layer .= '  }';

    $Layer .= ' div.innerHTML = "[osm_map lat=\"" + Centerlonlat.lat + "\" long=\"" + Centerlonlat.lon + "\" zoom=\"" + zoom + "\" width=\"600\" height=\"450\"" + MarkerField + GpxFileField + GpxColourField + BorderColourField + MarkerFileField + MapControlField + MarkerTextField_01 + MarkerTextField_02 + MarkerTextField_03 + MarkerTextField_04 + NaviField + ZIndexField + ControlStyleField + " type=\""+LayerName+"\"]";';

//++ set marker
    
    $Layer .= '  markerslayer.clearMarkers();';

   	$Layer .= '    var lonlat = new OpenLayers.LonLat(Centerlonlat.lon,Centerlonlat.lat); ';

//    $Layer .= '  var lonlat = new OpenLayers.LonLat(Centerlonlat.lon,Centerlonlat.lat).transform(map.displayProjection, map.projection);';
    $Layer .= '  markerslayer.addMarker(new OpenLayers.Marker(lonlat, click_icon.clone()));';

//--

    }
    else if( $a_msgBox == 'metabox_sc_gen'){
    $Layer .= ' MarkerField = "";';
    $Layer .= ' ThemeField = "";';
    $Layer .= ' if (document.post.osm_marker.value != "none"){';
    $Layer .= ' MarkerField = " marker=\""+Clicklonlat.lat+","+Clicklonlat.lon+
"\" marker_name=\"" + document.post.osm_marker.value + "\"";';  
    $Layer .= '  }';
    $Layer .= ' if (document.post.osm_theme.value == "dark"){';
    $Layer .= ' ThemeField = " control=\"mouseposition,scaleline\" map_border=\"thin solid grey\" theme=\"dark\"";';  
    $Layer .= '  }';
    $Layer .= ' if (document.post.osm_theme.value == "blue"){';
    $Layer .= ' ThemeField = " control=\"mouseposition,scaleline\" map_border=\"thin solid blue\" theme=\"ol\"";';  
    $Layer .= '  }';
    $Layer .= ' if (document.post.osm_theme.value == "orange"){';
    $Layer .= ' ThemeField = " control=\"mouseposition,scaleline\" map_border=\"thin solid orange\" theme=\"ol_orange\"";';  
    $Layer .= '  }';

    $Layer .= ' if (document.post.osm_mode.value == "sc_gen"){';
    $Layer .= ' GenTxt = "[osm_map lat=\"" + Centerlonlat.lat + "\" long=\"" + Centerlonlat.lon + "\" zoom=\"" + zoom + "\" width=\"600\" height=\"450\" " + ThemeField + MarkerField + "]";';  
    $Layer .= '  }';
    $Layer .= ' if (document.post.osm_mode.value == "geotagging"){';
    $Layer .= ' GenTxt = "For geotagging your post/page create a custom field. <br>Name: OSM_geo_data <br>Value: "+Clicklonlat.lat+","+Clicklonlat.lon;';  
    $Layer .= '  }';

      $Layer .= ' div = document.getElementById("ShortCode_Div");';
      $Layer .= ' div.innerHTML = GenTxt;';
    }
    else if( $a_msgBox == 'lat_long'){
      $Layer .= ' alert("Lat= " + Clicklonlat.lat + " Long= " + Clicklonlat.lon);';   
    }
    $Layer .= ' 	                }';
    $Layer .= ' 	';
    $Layer .= ' 	            });';
    $Layer .= 'var click = new OpenLayers.Control.Click();';
    $Layer .= 'map.addControl(click);';
    $Layer .= 'click.activate();';
    return $Layer;
  }

  function addMarkerListLayer($a_LayerName, $Icon ,$a_MarkerArray, $a_DoPopUp)
  {
    Osm::traceText(DEBUG_INFO, "addMarkerListLayer(".$a_LayerName.",".$Icon[name].",".$Icon[width].",".$Icon[height].",".$a_MarkerArray.",".$Icon[offset_width].",".$Icon[offset_height].",".$a_DoPopUp.")");

    $Layer .= 'var markers = new OpenLayers.Layer.Markers( "'.$a_LayerName.'" );';
    $Layer .= 'map.addLayer(markers);';
    
    $Layer .= 'var data = {};';
    $Layer .= 'var currentPopup;';
    $Layer .= 'data.icon = new OpenLayers.Icon("'.OSM_PLUGIN_ICONS_URL.$Icon[name].'",';
    $Layer .= '     new OpenLayers.Size('.$Icon[width].','.$Icon[height].'),';
    $Layer .= '     new OpenLayers.Pixel('.$Icon[offset_width].', '.$Icon[offset_height].'));';   
    
    $NumOfMarker = count($a_MarkerArray);
    for ($row = 0; $row < $NumOfMarker; $row++){
      $Layer .= 'var ll = new OpenLayers.LonLat('.$a_MarkerArray[$row][lon].','.$a_MarkerArray[$row][lat].').transform(map.displayProjection,  map.projection);';
      $Layer .= '     var feature = new OpenLayers.Feature(markers, ll, data);';
         
      $Layer .= 'feature.closeBox = true;';
      $Layer .= 'feature.popupClass = OpenLayers.Class(OpenLayers.Popup.FramedCloud, {"autoSize": true, minSize: new OpenLayers.Size('.$a_MarkerArray[$row][popup_width].','.$a_MarkerArray[$row][popup_height].'),"keepInMap": true } );';
      
      // add the the backslashes
      $OSM_HTML_TEXT = addslashes($a_MarkerArray[$row][text]);
      
      $Layer .= 'feature.data.popupContentHTML = "'.$OSM_HTML_TEXT.'";';
      $Layer .= 'feature.data.overflow = "hidden";';

      $Layer .= 'var marker = new OpenLayers.Marker(ll,data.icon.clone());';
      $Layer .= 'marker.feature = feature;';
      if ($a_DoPopUp == 'true'){
        $Layer .= 'marker.events.register("mousedown", feature, osm_MarkerPopUpClick);';
      }
      $Layer .= 'markers.addMarker(marker);';
      if ($a_DoPopUp == 'true'){
        $Layer .= 'map.addPopup(feature.createPopup(feature.closeBox));';   // maybe there is a better way to do 
        if ($NumOfMarker > 1){
          $Layer .= 'feature.popup.toggle();';                              // it than create and toggle!
        }
      }
    }
    return $Layer;
  }

//++
  function addLineLayer($a_LayerName, $a_MarkerArray)
  {
    Osm::traceText(DEBUG_INFO, "addLineLayer(".$a_LayerName);

    $Layer .= 'var LonList = new Array()  ';
    $Layer .= 'var LatList = new Array()  ';
    $NumOfMarker = count($a_MarkerArray);
    for ($ii = 0; $ii < $NumOfMarker; $ii++){
      $Layer .= 'LonList['.$ii.']='.$a_MarkerArray[$ii][lon];
      $Layer .= 'LatList['.$ii.']='.$a_MarkerArray[$ii][lat];
    }
    return $Layer;
  }
//--

    
  function addTextLayer($a_MarkerName, $a_marker_file)
  {
    Osm::traceText(DEBUG_INFO, "addTextLayer(".$a_marker_file.")");   
    $Layer .= 'var pois = new OpenLayers.Layer.Text( "'.$a_MarkerName.'",';
    $Layer .= '        { location:"'.$a_marker_file.'",';
    $Layer .= '          projection: map.displayProjection';
    $Layer .= '        });';
    $Layer .= 'map.addLayer(pois);';
    return $Layer; 
  } 

/// discs
   function addDiscs($centerListArray,$radiusListArray,$centerOpacityListArray,$centerColorListArray,
                     $borderWidthListArray,$borderColorListArray,$borderOpacityListArray,$fillColorListArray,$fillOpacityListArray) {

   
   $layer ='var discLayer = new OpenLayers.Layer.Vector("Disc Layer");';
   $layer.='var lineLayer = new OpenLayers.Layer.Vector("Line Layer");';

   for($i=0;$i<sizeof($centerListArray);$i++){
    // $centerListArray[$i] = lon lat -> lon,lat
    // only center and radius must be defined for each disc to be shown, else use first/default value (ie [0])
    $layer .= 'osm_getFeatureDiscCenter(discLayer,'.implode(",",explode( " ", trim($centerListArray[$i]) )).', '.$radiusListArray[$i].', '.
                      ((isset($centerOpacityListArray[$i]))? $centerOpacityListArray[$i] : $centerOpacityListArray[0]).', "'.
                      ((isset($centerColorListArray[$i]))?   $centerColorListArray[$i]   : $centerColorListArray[0]).'", '.
                      ((isset($borderWidthListArray[$i]))?   $borderWidthListArray[$i]   : $borderWidthListArray[0]).', "'.
                      ((isset($borderColorListArray[$i]))?   $borderColorListArray[$i]   : $borderColorListArray[0]).'", '.
                      ((isset($borderOpacityListArray[$i]))? $borderOpacityListArray[$i] : $borderOpacityListArray[0]).', "'.
                      ((isset($fillColorListArray[$i]))?     $fillColorListArray[$i]     : $fillColorListArray[0]).'", '.
                      ((isset($fillOpacityListArray[$i]))?   $fillOpacityListArray[$i]   : $fillOpacityListArray[0]).');';
    }
    $layer.=' map.addLayer(discLayer);';
    return $layer;
   }
 /// end discs 
// lines ++
   function addLines($PointListArray,$a_LineColor,$a_LineWidth)  
   {   
     $layer.='var lineLayer = new OpenLayers.Layer.Vector("Line Layer");';
     $layer.='var Points = new Array();';
     $layer.='var lineWidth = '.$a_LineWidth.';';
     $layer.='var lineColor = "'.$a_LineColor.'";';

     for($i=0;$i<sizeof($PointListArray);$i++){
       $layer.='Points['.$i.'] = new Object();';
       $layer.='Points['.$i.']["lon"] = '.$PointListArray[$i][lon].';';
       $layer.='Points['.$i.']["lat"] = '.$PointListArray[$i][lat].';';
     }
     $layer.=' osm_setLinePoints(lineLayer, lineWidth, lineColor, 0.7, Points);';
     $layer.=' map.addLayer(lineLayer);';

     return $layer;
   }
// //lines --

  function setMapCenterAndZoom($a_lat, $a_lon, $a_zoom)
  {
    Osm::traceText(DEBUG_INFO, "setMapCenterAndZoom(".$a_lat.",".$a_lon.",".$a_zoom.")");

    if (strtolower($a_zoom) == ('auto')){
      $a_zoom = 'null';
    }
    if ((strtolower($a_lat) == ('auto')) || (strtolower($a_lon) == ('auto'))) {
      $Layer .= 'var lonLat = null;';
    }
    else {
      $Layer .= 'var lonLat = new OpenLayers.LonLat('.$a_lon.','.$a_lat.').transform(map.displayProjection,  map.projection);';
    }
    
    $Layer .= 'map.setCenter (lonLat,'.$a_zoom.');'; // Zoomstufe einstellen
    return $Layer;
  } 

      
  // if you miss a MapType, just add it
  function checkMapType($a_type){
    // Osmarender ist not supported anymore
    if ($a_type == 'Osmarender'){
      return "Mapnik";
    }
    if ($a_type != 'Mapnik' && $a_type != 'Osmarender' && $a_type != 'CycleMap' && $a_type != 'OpenSeaMap' && $a_type != 'OpenWeatherMap' && $a_type != 'Google' && $a_type != 'All' && $a_type != 'AllGoogle' && $a_type != 'AllOsm' && $a_type != 'ext' && $a_type != 'GooglePhysical' && $a_type != 'GoogleStreet' && $a_type != 'GoogleHybrid' && $a_type != 'GoogleSatellite' && $a_type != 'Google Physical' && $a_type != 'Google Street' && $a_type != 'Google Hybrid' && $a_type != 'Google Satellite'&& $a_type != 'Ext'){
      return "All";
    }
    return $a_type;
  }

  // check the num of zoomlevels
  function checkOverviewMapZoomlevels($a_Zoomlevels){
    if ( $a_Zoomlevels > 17){
      Osm::traceText(DEBUG_ERROR, "Zoomlevel out of range!");
      return 0;
    }
    return $a_Zoomlevels;
  }     
  
  function checkControlType($a_MapControl){
    foreach ( $a_MapControl as $MapControl ){
	  Osm::traceText(DEBUG_INFO, "Checking the Map Control");
	  $MapControl = strtolower($MapControl);
	  if (( $MapControl != 'scaleline') && ($MapControl != 'scale') && ($MapControl != 'no') && ($MapControl != 'mouseposition') && ($MapControl != 'off')) {
	    Osm::traceText(DEBUG_ERROR, "e_invalid_control");
	    $a_MapControl[0]='No';
	  }
    }
    return $a_MapControl;
  }
  
}
?>
