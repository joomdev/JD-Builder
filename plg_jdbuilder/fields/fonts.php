<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use JDPageBuilder\Field;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('groupedlist');

class JFormFieldFonts extends JFormFieldGroupedList
{

   protected $type = 'fonts';

   public function getGroups()
   {

      $default = new \stdClass();
      $default->text = \JText::_('JDB_DEFAULT');
      $default->value = '';
      $groups[] = [$default];

      $customFonts = Field::getCustomFonts();
      $cfonts = [];
      foreach ($customFonts as $f) {
         $font = new \stdClass();
         $font->text = $f['name'];
         $font->value = "c~" . $f['id'];
         $cfonts[] = $font;
      }
      $groups[\JText::_("JDB_SYSTEM_FONTS_TITLE")] = $cfonts;

      $sfonts = [];
      foreach (JDPageBuilder\Constants::SYSTEM_FONTS as $value => $label) {
         $font = new \stdClass();
         $font->text = $label;
         $font->value = "s~" . $value;
         $sfonts[] = $font;
      }
      $groups[\JText::_("JDB_GOOGLE_FONTS_TITLE")] = $sfonts;

      $gfonts = [];
      $googlefonts = \json_decode(file_get_contents(JPATH_SITE . '/media/jdbuilder/data/googlefonts.json'), true);
      foreach ($googlefonts as $googlefont) {
         $font = new \stdClass();
         $font->text = $googlefont['label'];
         $font->value = $googlefont['value'];
         $gfonts[] = $font;
      }
      $groups[\JText::_("JDB_GOOGLE_FONTS_TITLE")] = $gfonts;

      return $groups;
   }
}
