/**
 * @file finder_ui_reload.js
 */

(function ($) {

  /**
   * The reload behavior for the Finder UI.
   */
  Drupal.behaviors.finder_ui_reload = {
    attach: function(context) {
      jQuery(".finder-ui-needs-reload", context)
        .each(function () {
          location.reload();
        });
    }
  };

})(jQuery);