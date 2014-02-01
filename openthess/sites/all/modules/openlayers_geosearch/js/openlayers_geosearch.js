(function($) {
  Drupal.openlayers.openlayers_behavior_geosearch = {};
  Drupal.openlayers_geosearch = {};

  //Drupal.behaviors.openlayers_geosearch = function (context) {
  //Drupal.openlayers.addBehavior('openlayers_behavior_geosearch', function (data, options) {
  Drupal.behaviors.openlayers_behavior_geosearch = {
    'attach': function(context, settings) {
//      var map = data.openlayers;

      /*
       * This only happens upon loading of the original block for the results
       */
//      $('#openlayersgeosearchresults', context).not('.openlayersgeosearchresults-processed').each(function() {
//        $(this).addClass('openlayersgeosearchresults-processed');
      $('#openlayersgeosearchresults').once('openlayersgeosearchresults', function() {
        // Take all the maps on the page
        Drupal.openlayers_geosearch.data = $('.openlayers-map').data('openlayers');
        if (!Drupal.openlayers_geosearch.data.map.displayProjection) {
          Drupal.openlayers_geosearch.data.map.displayProjection = 4326;
        }
/*
        if (Drupal.openlayers_geosearch.data.map.behaviors['openlayers_behavior_zoomtolayer']) {  
          Drupal.openlayers_geosearch.data.map.behaviors['openlayers_behavior_zoomtolayer'].point_zoom_level = 10;
        }
*/
        Drupal.openlayers_geosearch.displayProjection = new OpenLayers.Projection(Drupal.openlayers_geosearch.data.map.displayProjection);
        Drupal.openlayers_geosearch.projection = new OpenLayers.Projection(Drupal.openlayers_geosearch.data.map.projection);
        var searchLayers = Drupal.openlayers_geosearch.data.openlayers.getLayersBy('drupalID', "openlayers_searchresult_layer");
        // If the searchlayer is not selected, we just create one on the fly
        if (searchLayers.length == 0) {
          var searchLayer = new OpenLayers.Layer.Vector(
            Drupal.t("Search Layer"),
            {
              projection: new OpenLayers.Projection('EPSG:4326'),
              drupalID: 'openlayers_searchresult_layer'
            }
          );
          // We add the default styles to the layer, so we can use them when the table is clicked
          var styleMap = Drupal.openlayers.getStyleMap(Drupal.openlayers_geosearch.data.map, 'openlayers_searchresult_layer');
          searchLayer.StyleMap = styleMap;
          Drupal.openlayers_geosearch.data.openlayers.addLayer(searchLayer);
          searchLayers.push(searchLayer);
        }
        Drupal.openlayers_geosearch.vectorLayer = Drupal.openlayers_geosearch.data.openlayers.getLayersBy('drupalID', "openlayers_searchresult_layer");

        /* 
         *  Create an Openlayers Control that keeps the selection in the table and the map in sync
         */
        var popupSelect = new OpenLayers.Control.SelectFeature(searchLayers[0],
          {
            clickout: true, toggle: true,
            multiple: false, hover: false,
            onSelect: function (feature) {
              /*
               * The popup code is copied (oh horror) from the popup_behaviour
               */    	        	
              // Create FramedCloud popup.
              popup = new OpenLayers.Popup.FramedCloud(
                'popup',
                feature.geometry.getBounds().getCenterLonLat(),
                null,
                Drupal.theme('openlayers_geosearchPopup', feature),
                null,
                true,
                function (evt) {
                	$("#popup").remove();
                	var that = $('#' + feature.id.replace(/\./g, '-') + '-list');
                    $(that[0]).removeClass("openlayers-geosearch-selected")
                }
              );
              // Redraw the feature as being selected.

              var styleMap = Drupal.openlayers.getStyleMap(Drupal.openlayers_geosearch.data.map, 'openlayers_searchresult_layer');
              feature.style = styleMap.styles['select'].defaultStyle;
              var vectorLayer = Drupal.openlayers_geosearch.data.openlayers.getLayersBy('drupalID', "openlayers_searchresult_layer");
              vectorLayer[0].drawFeature(feature, styleMap.styles['select'].defaultStyle);

              /*
               * Add a selected class to the html element with the corresponding id as this feature
               */
              id = feature.id;
              id = id.replace(/\./g, '-');
              var that = $('#' + id + '-list');
              $(that[0]).addClass("openlayers-geosearch-selected");
              // Assign popup to feature and map.
              feature.popup = popup;
              feature.layer.map.addPopup(popup);
              //Drupal.openlayers.popup.selectedFeature = feature;
            },
            onUnselect: function (feature) {
              // redraw the feature as default
              var styleMap = Drupal.openlayers.getStyleMap(Drupal.openlayers_geosearch.data.map, 'openlayers_searchresult_layer');
              feature.style = styleMap.styles['default'].defaultStyle;
              var vectorLayer = Drupal.openlayers_geosearch.data.openlayers.getLayersBy('drupalID', "openlayers_searchresult_layer");
              vectorLayer[0].drawFeature(feature, styleMap.styles['default'].defaultStyle);

              var that = $('#' + feature.id.replace(/\./g, '-') + '-list');
              $(that[0]).removeClass("openlayers-geosearch-selected");

              // Remove popup if feature is unselected.
              feature.layer.map.removePopup(feature.popup);
              feature.popup.destroy();
              feature.popup = null;
            }
          }
        );

        Drupal.openlayers_geosearch.popupSelect = popupSelect;
        Drupal.openlayers_geosearch.data.openlayers.addControl(popupSelect);
        popupSelect.activate();
      });

      /*
       *  This event is only when the results are reset, after a fresh search
       */
//      $("#openlayersgeosearchtabs", context).not('.openlayersgeosearchtabs-processed').each(function() {
//        $(this).tabs().addClass('.openlayersgeosearchtabs-processed');
      $("#openlayersgeosearchtabs").once('openlayersgeosearchtabs', function() {
        // here we unselect any dot and remove all dots from the map (only before adding the first result
        Drupal.openlayers_geosearch.popupSelect.unselectAll();
        Drupal.openlayers_geosearch.vectorLayer[0].removeAllFeatures();
      });

      $("#openlayersgeosearchtabs").tabs();
      
      /*
       * This only happens upon (re)loading the full set of results
       */
      // var i = 0;
//      $('.openlayers-geosearch-result-table', context).not('.openlayers-geosearch-result-table-processed').each(function() {
//        $(this).addClass('openlayers-geosearch-result-table-processed');
      $('.openlayers-geosearch-result-table').once('openlayers-geosearch-result-table', function() {
        /*
         * Now we loop through all the links within the table, the links hold the lat & lon for each point to be plotted
         */
//        $('.openlayers-geosearch-result-table a', context).not('.openlayers-geosearch-result-a-processed').each(function() {
//          $(this).addClass('openlayers-geosearch-result-a-processed');
      $('.openlayers-geosearch-result-table a').once('openlayers-geosearch-result-a', function() {
          // and here we add the dot to the map
          var point = Drupal.openlayers_geosearch.getpoint($(this)[0].href);
          var geometry = new OpenLayers.Geometry.Point(point.lat, point.lon).transform(Drupal.openlayers_geosearch.displayProjection, Drupal.openlayers_geosearch.projection);
          // var bounds = new OpenLayers.Bounds(point.minx, point.miny, point.maxx, point.maxy).transform(Drupal.openlayers_geosearch.displayProjection, Drupal.openlayers_geosearch.projection);

          var pointfeature = new OpenLayers.Feature.Vector(geometry);
          // lets get the styles of this layer
          var styleMap = Drupal.openlayers.getStyleMap(Drupal.openlayers_geosearch.data.map, 'openlayers_searchresult_layer');
          pointfeature.style = styleMap.styles['default'].defaultStyle;
          // we store the id of the feature in our <a id="id"> tag, so we can do things when we click the link
          var id = pointfeature.id + ".list";
          id = id.replace(/\./g, '-'); // the . does not go well with css 
          $(this)[0].id = id;
          pointfeature.attributes.name = $(this)[0].innerHTML;
          var popupid = pointfeature.id + ".popup";
          popupid = popupid.replace(/\./g, '-'); // the . does not go well with css 
          pointfeature.attributes.description = "";

          Drupal.openlayers_geosearch.vectorLayer[0].addFeatures([pointfeature], styleMap.styles['default'].defaultStyle);
          $(this).click(Drupal.openlayers_geosearch.blockclick);
        });
        Drupal.openlayers_geosearch.zoomtoresults();
      });
    }
  };

  /**
   * Performs a search on the a links in the Results Block
   */
  Drupal.openlayers_geosearch.blockclick = function () {
    // the id is passed as OpenLayers.Features.id.list (so we remove the .list from the string to get the id)
    var id = this.id.substring(0, this.id.length -5 );
    // find the results layer
    var vectorLayer = Drupal.openlayers_geosearch.data.openlayers.getLayersBy('drupalID', "openlayers_searchresult_layer");
    // css does not like dots
    id = id.replace(/-/g, '.');
    // find the feature
    var feature = vectorLayer[0].getFeatureById(id);
    //OpenLayers.Control.SelectFeature.select(feature);
    Drupal.openlayers_geosearch.popupSelect.clickFeature(feature);
    return false;
  };

  /*
   *  Returns a Point from the href we have crafted
   */
  Drupal.openlayers_geosearch.getpoint = function(href) {
    var mainparts = href.split("?");
    var parts = mainparts[1].split("&");
    var point = {};
    for (var i in parts) {
      part = parts[i].split("=");
      point[part[0]] = part[1];
    }
    return point;  
  };

  Drupal.openlayers_geosearch.zoomtoresults = function() {
    var layerextent = Drupal.openlayers_geosearch.vectorLayer[0].getDataExtent();

    // Check for valid layer extent
    if (layerextent != null) {
      Drupal.openlayers_geosearch.data.openlayers.zoomToExtent(layerextent);
    
      // If unable to find width due to single point,
      // zoom in with point_zoom_level option.
      // Lets try to change this to the Bounding box of the Point.
      if (layerextent.getWidth() == 0.0) {
        if (Drupal.openlayers_geosearch.data.map.behaviors['openlayers_behavior_zoomtolayer'] != undefined) {
          Drupal.openlayers_geosearch.data.openlayers.zoomTo(Drupal.openlayers_geosearch.data.map.behaviors['openlayers_behavior_zoomtolayer'].point_zoom_level);
        }
      }
    }
  };

  /**
   * Javascript Drupal Theming function for inside of Popups
   *
   * To override
   *
   * @param feature
   *  OpenLayers feature object
   * @return
   *  Formatted HTML
   */
  Drupal.theme.prototype.openlayers_geosearchPopup = function(feature) {
    var output = '';
    if (typeof Drupal.theme.prototype.openlayers_geosearchPopupCustom != 'function') {
      output =
        '<div class="openlayers-geosearch-popup openlayers-geosearch-popup-name">' +
          feature.attributes.name +
        '</div>' +
        '<div class="openlayers-geosearch-popup openlayers-geosearch-popup-description">' +
          feature.attributes.description +
        '</div>';
    } else {
      output = Drupal.theme.prototype.openlayers_geosearchPopupCustom(feature);
    }
    return output;
  };
  
})(jQuery);