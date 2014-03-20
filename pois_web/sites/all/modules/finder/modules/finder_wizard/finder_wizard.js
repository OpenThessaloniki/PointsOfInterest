/**
 * @file finder_wizard.js
 */

(function ($) {

  /**
   * The behavior for the Finder UI.
   */
  Drupal.behaviors.finder_wizard = {
    attach: function(context) {

      for (i in Drupal.settings.finder_wizard.finder_wizard_next) {
        var build_id = Drupal.settings.finder_wizard.finder_wizard_next[i];
        if (jQuery("#" + build_id + " > form", context).length > 0) {
          jQuery("#" + build_id + " > form input[name=next]", context).click();
        }
      }

      for (i in Drupal.settings.finder_wizard.finder_wizard_back) {
        var build_id = Drupal.settings.finder_wizard.finder_wizard_back[i];
        if (jQuery("#" + build_id + " > form", context).length > 0) {
          jQuery("#" + build_id + " > form input[name=back]", context).click();
        }
      }

    }
  };

})(jQuery);