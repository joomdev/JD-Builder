<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

class FieldGroup {

   protected $xml;
   protected $title;
   public $ordering = 0;
   protected $fields = [];

   public function __construct($xml = '', $title = '') {
      $this->xml = $xml;
      if (!empty($xml)) {
         $this->title = (string) $this->xml->attributes()->name;
         $label = (string) $this->xml->attributes()->label;
         if (!empty($label)) {
            $this->title = \JText::_($label);
         }
         $ordering = (string) $this->xml->attributes()->ordering;
         $this->ordering = empty($ordering) ? 0 : (int) $ordering;


         $showon = (string) $this->xml->attributes()->showon;
         if (!empty($showon)) {
            $this->showon = $showon;
         }
      }
      if (!empty($title)) {
         $this->title = \JText::_($title);
         $this->ordering = 1;
      }
   }

   public function addField($xml, $prefix = '') {
      $type = (string) $xml->attributes()->type;
      if ($type != "group") {
         $name = (string) $xml->attributes()->name;
         $type = (string) $xml->attributes()->type;
         if (!empty($name) && !empty($type)) {
            $this->fields[] = new Field($xml, $prefix);
         } else if (in_array($type, Form::$fields_without_name)) {
            $this->fields[] = new Field($xml, $prefix);
         }
      }
   }

   public function get() {
      $return = ['title' => $this->title, 'ordering' => $this->ordering, 'fields' => []];
      if (isset($this->showon)) {
         $return['showon'] = FormHelper::displayExpression($this->showon);
      }
      foreach ($this->fields as $field) {
         $return['fields'][] = $field->get();
      }
      //usort($return['fields'], '\JDPageBuilder\FormHelper::sortByOrdering');
      return $return;
   }

}
