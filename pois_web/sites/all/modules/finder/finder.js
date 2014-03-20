/**
 * @file finder.js
 */

(function ($) {

  /**
   * The behavior for the Finder UI.
   */
  Drupal.behaviors.finder = {
    attach: function(context) {

      for (finder_name in Drupal.settings.finder) {
        if (jQuery(".finder-" + finder_name, context).length > 0) {

          for (i in Drupal.settings.finder[finder_name].autosubmit) {
            var build_id = Drupal.settings.finder[finder_name].autosubmit[i];
            jQuery("#" + build_id + " > form", context).find("input, select, textarea")
              .change(function() {
                jQuery(this, context).parents('form').first().submit();
              });
          }

          for (i in Drupal.settings.finder[finder_name].results_load) {
            var build_id = Drupal.settings.finder[finder_name].results_load[i];
            var values = $("#" + build_id + " > form", context).first().serialize();
            $.post(Drupal.settings.basePath + "?q=finder_results_ajax/" + finder_name, values, function(data) {
              jQuery("#" + build_id + " > .finder-results-" + finder_name, context)
                .replaceWith(data.output);
            });
          }

          for (i in Drupal.settings.finder[finder_name].results_update) {
            var build_id = Drupal.settings.finder[finder_name].results_update[i];
            jQuery("#" + build_id + " > form", context).find("input, select, textarea")
             .bind('click change blur keyup', function() {
               var values = $(this).parents('form').first().serialize();
               $.post(Drupal.settings.basePath + "?q=finder_results_ajax/" + finder_name, values, function(data) {
                 jQuery("#" + build_id + " > .finder-results-" + finder_name, context)
                   .replaceWith(data.output);
               });
             });
          }

          for (j in Drupal.settings.finder[finder_name]['load']) {
            var element = Drupal.settings.finder[finder_name]['load'][j];
            var element_class = '.finder-form-element-' + element;
            if (jQuery(element_class, context).length > 0) {
              var values = $(this).parents('form').first().serialize();
              $.post(Drupal.settings.basePath + "?q=finder_element_ajax/" + finder_name + "/" + element, values, function(data) {
                jQuery(".finder-" + data.finder + " .finder-form-element-" + data.element, context)
                  .replaceWith(data.output);
              });

            }
          }

          for (reduce_element in Drupal.settings.finder[finder_name]['reduce']) {
            var reduce_element_class = '.finder-element-' + reduce_element;
            if (jQuery(reduce_element_class, context).length > 0) {
              for (i in Drupal.settings.finder[finder_name]['reduce'][reduce_element]) {
                var element = Drupal.settings.finder[finder_name]['reduce'][reduce_element][i];
                var element_class = '.finder-element-' + element;
                if (jQuery(element_class, context).length > 0) {
                  jQuery(element_class, context)
                    .change(function() {
                      var values = $(this).parents('form').first().serialize();
                      $.post(Drupal.settings.basePath + "?q=finder_element_ajax/" + finder_name + "/" + reduce_element, values, function(data) {
                        jQuery(".finder-" + data.finder + " .finder-form-element-" + data.element, context)
                          .replaceWith(data.output);
                      });
                    });
                }
              }
            }
          }
        }
      }
    }
  };

})(jQuery);