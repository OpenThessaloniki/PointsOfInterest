This module aims to rebuild the search functionality similar to www.openstreetmap.org. It includes:

# A search block
# A block with results of the search
# A block with a map where each searchresult is plotted on (but any openlayers map on 
    the page will react to a search.

The module comes with 3 standard plugins:

# Google. Depends on Geocode Module. http://drupal.org/project/geocode
# Geonames. Depends on Geonames Module. http://drupal.org/project/geonames
# WFS. For which you need access to a WFS server, eg. Geoserver

The module itself requires the Openlayers Module. http://drupal.org/project/openlayers

Usage

# Install the module like you would do with any module
# Enable at least 1 of the submodules
# Go to http://example.com/admin/build/openlayers/geosearch to change settings (the Google module has no settings)
# As long as 'Enable Test Page' is ticked, you can access a demo at http://example.com/admin/build/openlayers/geosearch 


This module is sponsored by Unicef Uganda for the Devtrac Project. http://www.devtrac.ug