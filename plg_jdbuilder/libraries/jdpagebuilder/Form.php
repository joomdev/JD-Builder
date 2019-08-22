<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

class Form {

   public $fieldsets = [];
   public $xmlfile;
   public static $fields_without_name = ["header_tag", "spacer", "comingsoon", "elementinfo", "div", "alert"];
   public static $fields_without_value = ["spacer", "comingsoon", "elementinfo", "div", "alert"];
   public $type;

   public function __construct($type) {
      $this->type = $type;
   }

   public function load($xmlfile) {
      $this->xmlfile = $xmlfile;
      $xml = simplexml_load_file($xmlfile);
      foreach ($xml->form->fields->fieldset as $fieldset) {
         $fname = (string) $fieldset->attributes()->name;
         if (empty($fname)) {
            continue;
         }
         if (isset($this->fieldsets[$fname])) {
            $this->fieldsets[$fname]->merge($fieldset);
         } else {
            $this->fieldsets[$fname] = new Fieldset($fieldset, $this->type);
         }
      }
   }

   public function get() {
      $return = ['tabs' => []];
      $first = true;
      foreach ($this->fieldsets as $fieldset) {
         $tab = $fieldset->get();
         $tab['active'] = $first;
         $return['tabs'][] = $tab;
         $first = false;
      }
      usort($return['tabs'], '\JDPageBuilder\FormHelper::sortByOrdering');
      foreach ($return['tabs'] as $tab) {
         unset($tab['ordering']);
      }
      return $return;
   }

}

?>
