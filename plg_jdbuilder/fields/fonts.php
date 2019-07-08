<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('groupedlist');

class JFormFieldFonts extends JFormFieldGroupedList {

   protected $type = 'fonts';

   public function getGroups() {

      $default = new \stdClass();
      $default->text = \JText::_('JDEFAULT');
      $default->value = '';
      $groups[] = [$default];

      $sfonts = [];
      foreach (JDPageBuilder\Constants::SYSTEM_FONTS as $value => $label) {
         $font = new \stdClass();
         $font->text = $label;
         $font->value = "s~" . $value;
         $sfonts[] = $font;
      }
      $groups[\JText::_("JDBUILDER_SYSTEM_FONTS_TITLE")] = $sfonts;

      $gfonts = [];
      $googlefonts = \json_decode(file_get_contents(JPATH_SITE . '/media/jdbuilder/data/googlefonts.json'), true);
      foreach ($googlefonts as $googlefont) {
         $font = new \stdClass();
         $font->text = $googlefont['label'];
         $font->value = $googlefont['value'];
         $gfonts[] = $font;
      }
      $groups[\JText::_("JDBUILDER_GOOGLE_FONTS_TITLE")] = $gfonts;

      return $groups;
   }

}
