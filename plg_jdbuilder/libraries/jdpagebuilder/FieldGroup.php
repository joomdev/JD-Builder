<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

// No direct access
defined('_JEXEC') or die('Restricted access');

class FieldGroup
{

   protected $xml;
   public $title;
   public $ordering = 0;
   protected $fields = [];

   public function __construct($xml = '', $title = '')
   {
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

   public function addField($xml, $prefix = '', $defaults = [], $showon = null, $invisible = false)
   {
      $type = (string) $xml->attributes()->type;
      if ($type != "group") {
         $name = (string) $xml->attributes()->name;
         $type = (string) $xml->attributes()->type;
         if (!empty($name) && !empty($type)) {
            $this->fields[] = new Field($xml, $prefix, $defaults, $showon, $invisible);
         } else if (in_array($type, Form::$fields_without_name)) {
            $this->fields[] = new Field($xml, $prefix, $defaults, $showon, $invisible);
         }
      }
   }

   public function get()
   {
      $return = ['title' => $this->title, 'ordering' => $this->ordering, 'fields' => []];
      if (isset($this->showon)) {
         $return['showon'] = FormHelper::displayExpression($this->showon);
      }
      $added = [];
      $index = 0;
      foreach ($this->fields as $field) {
         $f = $field->get();
         if (!isset($f['name'])) {
            $return['fields'][$index] = $f;
            $index++;
         } else if (!\in_array($f['name'], $added)) {
            $return['fields'][$index] = $f;
            $added[$index] = $f['name'];
            $index++;
         } else {
            $i = array_search($f['name'], $added);
            $return['fields'][$i] = $f;
         }
      }
      //usort($return['fields'], '\JDPageBuilder\FormHelper::sortByOrdering');
      return $return;
   }
}
