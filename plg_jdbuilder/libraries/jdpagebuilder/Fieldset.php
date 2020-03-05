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

class Fieldset
{

   protected $xml;
   public $type;
   protected $title;
   protected $groups = [];
   public $ordering = 1;
   private $siblings = [];

   public function __construct($xml, $type, $siblings)
   {
      $this->xml = $xml;
      $this->type = $type;
      $this->siblings = $siblings;
      $this->title = (string) $this->xml->attributes()->name;
      $label = (string) $this->xml->attributes()->label;
      $ordering = (string) $this->xml->attributes()->ordering;
      $this->ordering = empty($ordering) ? $this->ordering : (int) $ordering;
      if (!empty($label)) {
         $this->title = \JText::_($label);
      }
      $this->groups['_default'] = new FieldGroup('', 'JDB_GENERAL_TITLE');
      foreach ($this->xml->field as $field) {
         $type = (string) $field->attributes()->type;
         if ($type == "group") {
            $gname = (string) $field->attributes()->name;
            if (!isset($this->groups[$gname])) {
               $this->groups[$gname] = new FieldGroup($field);
            }
         }
      }

      foreach ($this->xml->field as $field) {
         $type = (string) $field->attributes()->type;
         if ($type != "group") {
            $gname = "_default";
            $group = (string) $field->attributes()->group;
            if (!empty($group) && isset($this->groups[$group])) {
               $gname = $group;
            }
            if ($type == "fieldsgroup") {
               $name = (string) $field->attributes()->name;
               $filename = (string) $field->attributes()->filename;
               $showon = (string) $field->attributes()->showon;
               $showon = empty($showon) ? null : $showon;
               if (empty($name)) {
                  continue;
               }
               if (empty($filename)) {
                  $filename = $name;
               }
               $sfields = Helper::getFieldsGroup($filename, $this->type);
               $defaults = [];
               $invisibles = [];
               foreach ($field->property as $property) {
                  $pName =  (string) $property->attributes()->name;
                  $pDefault =  (string) $property->attributes()->default;
                  $defaults[$name . ucfirst($pName)] = $pDefault;

                  $invisible =  (string) $property->attributes()->invisible;
                  $invisible = ($invisible === 'true') ? true : false;
                  if ($invisible) {
                     $invisibles[] = $pName;
                  }
               }
               foreach ($sfields as $sfield) {
                  $type = (string) $sfield->attributes()->type;
                  if ($type == "group" && $type == "fieldsgroup") {
                     continue;
                  }
                  $sfName =  (string) $sfield->attributes()->name;
                  $this->groups[$gname]->addField($sfield, $name, $defaults, $showon, in_array($sfName, $invisibles));
               }
            } else {
               $this->groups[$gname]->addField($field);
            }
         }
      }
   }

   public function merge($xml)
   {
      foreach ($xml->field as $field) {
         $type = (string) $field->attributes()->type;
         if ($type == "group") {
            $gname = (string) $field->attributes()->name;
            $replace = (string) $field->attributes()->replace;
            $from = (string) $field->attributes()->from;
            if ($replace === 'true' && !empty($from) && isset($this->siblings[$from]) && isset($this->siblings[$from]->groups[$gname]) && !isset($this->groups[$gname])) {

               $group = new FieldGroup($field);

               $this->groups[$gname] = $this->siblings[$from]->groups[$gname];
               $this->groups[$gname]->title = $group->title;
               $this->groups[$gname]->ordering = $group->ordering;


               unset($this->siblings[$from]->groups[$gname]);
            } else if (!isset($this->groups[$gname])) {
               $this->groups[$gname] = new FieldGroup($field);
            }
         }
      }

      foreach ($xml->field as $field) {
         $type = (string) $field->attributes()->type;
         if ($type != "group") {
            $gname = "_default";
            $group = (string) $field->attributes()->group;
            if (!empty($group) && isset($this->groups[$group])) {
               $gname = $group;
            }
            if ($type == "fieldsgroup") {
               $name = (string) $field->attributes()->name;
               $filename = (string) $field->attributes()->filename;
               if (empty($name)) {
                  continue;
               }
               if (empty($filename)) {
                  $filename = $name;
               }
               $showon = (string) $field->attributes()->showon;
               $showon = empty($showon) ? null : $showon;
               $sfields = Helper::getFieldsGroup($filename, $this->type);
               $defaults = [];
               $invisibles = [];
               foreach ($field->property as $property) {
                  $pName =  (string) $property->attributes()->name;
                  $pDefault =  (string) $property->attributes()->default;
                  $defaults[$name . ucfirst($pName)] = $pDefault;

                  $invisible =  (string) $property->attributes()->invisible;
                  $invisible = ($invisible === 'true') ? true : false;
                  if ($invisible) {
                     $invisibles[] = $pName;
                  }
               }
               foreach ($sfields as $sfield) {
                  $type = (string) $sfield->attributes()->type;
                  if ($type == "group" && $type == "fieldsgroup") {
                     continue;
                  }
                  $sfName =  (string) $sfield->attributes()->name;
                  $this->groups[$gname]->addField($sfield, $name, $defaults, $showon, in_array($sfName, $invisibles));
               }
            } else {
               $this->groups[$gname]->addField($field);
            }
         }
      }
   }

   public function get()
   {
      $return = ['title' => $this->title, 'ordering' => $this->ordering, 'groups' => []];
      foreach ($this->groups as $group) {
         $item = $group->get();
         if (count($item['fields'])) {
            $return['groups'][] = $item;
         }
      }
      usort($return['groups'], '\JDPageBuilder\FormHelper::sortByOrdering');
      return $return;
   }
}
