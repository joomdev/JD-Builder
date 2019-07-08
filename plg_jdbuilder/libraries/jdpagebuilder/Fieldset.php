<?php

namespace JDPageBuilder;

class Fieldset {

   protected $xml;
   public $type;
   protected $title;
   protected $groups = [];
   public $ordering = 1;

   public function __construct($xml, $type) {
      $this->xml = $xml;
      $this->type = $type;
      $this->title = (string) $this->xml->attributes()->name;
      $label = (string) $this->xml->attributes()->label;
      $ordering = (string) $this->xml->attributes()->ordering;
      $this->ordering = empty($ordering) ? $this->ordering : (int) $ordering;
      if (!empty($label)) {
         $this->title = \JText::_($label);
      }
      $this->groups['_default'] = new FieldGroup('', 'JDBUILDER_GENERAL_TITLE');
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
               if (empty($name)) {
                  continue;
               }
               if (empty($filename)) {
                  $filename = $name;
               }
               $sfields = Helper::getFieldsGroup($filename, $this->type);
               foreach ($sfields as $sfield) {
                  $type = (string) $sfield->attributes()->type;
                  if ($type == "group" && $type == "fieldsgroup") {
                     continue;
                  }
                  $this->groups[$gname]->addField($sfield, $name);
               }
            } else {
               $this->groups[$gname]->addField($field);
            }
         }
      }
   }

   public function merge($xml) {
      foreach ($xml->field as $field) {
         $type = (string) $field->attributes()->type;
         if ($type == "group") {
            $gname = (string) $field->attributes()->name;
            if (!isset($this->groups[$gname])) {
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
               $sfields = Helper::getFieldsGroup($filename, $this->type);
               foreach ($sfields as $sfield) {
                  $type = (string) $sfield->attributes()->type;
                  if ($type == "group" && $type == "fieldsgroup") {
                     continue;
                  }
                  $this->groups[$gname]->addField($sfield, $name);
               }
            } else {
               $this->groups[$gname]->addField($field);
            }
         }
      }
   }

   public function get() {
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
