<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

use FontLib\Font;

// No direct access
defined('_JEXEC') or die('Restricted access');

class Field
{
   protected $xml;
   public $name;
   public $type;
   public $label;
   public $value;
   public $prefix;
   public $responsive;
   public $ordering = 0;
   public $description;
   public $required;
   protected static $fonts = null;

   public function __construct($xml, $prefix = '', $defaults = [], $parentShowon = null, $invisible = false)
   {
      $this->xml = $xml;
      $this->prefix = $prefix;
      $name = (string) $this->xml->attributes()->name;
      if (!empty($prefix)) {
         $name = $prefix . '-' . $name;
      }
      if (strpos($name, '-') !== false) {
         $this->name = Helper::camelize($name);
      } else {
         $this->name = $name;
      }
      $this->type = (string) $this->xml->attributes()->type;
      if ($this->type == "icon") {
         $this->type = "jdicon";
      }
      $label = (string) $this->xml->attributes()->label;
      if (strtolower($label) == "false") {
         $this->label = FALSE;
      } else if ($label != '') {
         $this->label = \JText::_($label);
      } else {
         $this->label = $this->name;
      }
      $description = (string) $this->xml->attributes()->description;
      if ($description == 'JDB_PHP_MAX_UPLOAD_SIZE') {
         $this->description = \JText::sprintf('JDB_PHP_MAX_UPLOAD_SIZE', '<strong>' . \JFilesystemHelper::fileUploadMaxSize() . '</strong>');
      } else {
         $this->description = \JText::_($description);
      }

      $required = (string) $this->xml->attributes()->required;
      $this->required = $required == 'true' ? true : false;

      $ordering = (string) $this->xml->attributes()->ordering;
      $this->ordering = empty($ordering) ? 1 : (int) $ordering;

      $showon = (string) $this->xml->attributes()->showon;
      if (!empty($showon)) {
         if (!empty($prefix)) {
            $showon = str_replace('_fg', Helper::camelize($prefix), $showon);
         }
         if ($parentShowon !== null) {
            $this->showon = '(' . $parentShowon . ')[AND]' . $showon;
         } else {
            $this->showon = $showon;
         }
      } else if ($parentShowon !== null) {
         $this->showon = $parentShowon;
      }

      $responsive = (string) $this->xml->attributes()->responsive;
      $this->responsive = strtolower($responsive) == "true" ? true : false;
      $this->invisible = $invisible;

      $showInfo = (string) $this->xml->attributes()->showInfo;
      $this->showInfo = ($showInfo == 'true' ? true : false);

      $taggable = (string) $this->xml->attributes()->taggable;
      $this->taggable = ($taggable == 'true' ? true : false);

      $this->setValue($defaults);
   }

   public static function customFonts()
   {
      if (!static::$fonts) {
         static::$fonts = self::getCustomFonts();
      }
      return static::$fonts;
   }

   public static function getFieldByType($type, $name = "", $value = "")
   {
      $xml = new \SimpleXMLElement("<field></field>");
      $xml->addAttribute("type", $type);
      $xml->addAttribute("name", $name);
      $xml->addAttribute("default", $value);
      return $xml;
   }

   public function setValue($defaults = [])
   {
      if (!empty($defaults) && isset($defaults[$this->name])) {
         $default = $defaults[$this->name];
      } else {
         $default = (string) $this->xml->attributes()->default;
         $default = \JText::_($default);
      }
      switch ($this->type) {
         case 'typography':
         case 'spacing':
         case 'subform':
         case 'slider':
         case 'time':
            if (empty($default)) {
               $default = "{}";
            }
            if (Helper::isValidJSON($default)) {
               $this->value = Helper::jsonDecode($default);
            } else {
               $this->value = \json_decode('{}');
            }
            break;
         case 'repeatable':
         case 'jcategory':
         case 'checkbox':
            if ($default == "") {
               $default = "[]";
            }
            if (Helper::isValidJSON($default)) {
               $this->value = Helper::jsonDecode($default);
            } else {
               $this->value = [];
            }
            break;
         case 'list':
            $multiple = (string) $this->xml->attributes()->multiple;
            $multiple = strtolower($multiple) == 'true' ? true : false;
            if ($multiple) {
               if ($default == "") {
                  $default = "[]";
               }
               if (Helper::isValidJSON($default)) {
                  $this->value = Helper::jsonDecode($default);
               } else {
                  $this->value = [];
               }
            } else {
               $this->value = $default;
            }
            break;
         default:
            $this->value = $default;
            break;
      }
   }

   public function get()
   {
      $return = [];
      if (!in_array($this->type, Form::$fields_without_name)) {
         $return['name'] = $this->name;
      }

      $return['type'] = $this->type;
      $return['label'] = $this->label;
      $return['description'] = $this->description;
      $return['required'] = $this->required;
      $return['responsive'] = $this->responsive;
      $return['ordering'] = $this->ordering;

      if (isset($this->showon)) {
         $return['showon'] = FormHelper::displayExpression($this->showon);
      }

      $width = (string) $this->xml->attributes()->width;
      if (!empty($width)) {
         $return['width'] = $width;
      }

      $class = (string) $this->xml->attributes()->class;
      if (!empty($class)) {
         $return['class'] = $class;
      }

      switch ($this->type) {
         case 'switch':
            $this->value = strtolower($this->value) == "true" ? true : false;
            $onColor = (string) $this->xml->attributes()->{'active-color'};
            $offColor = (string) $this->xml->attributes()->{'inactive-color'};
            if (!empty($onColor)) {
               $return['on'] = $onColor;
            }
            if (!empty($offColor)) {
               $return['off'] = $offColor;
            }
            break;
         case 'spacing':
            $corners = (string) $this->xml->attributes()->corners;
            $return['corners'] = strtolower($corners) == "true" ? true : false;
            break;
         case 'radio':
            $radiotype = (string) $this->xml->attributes()->radiotype;
            $return['radiotype'] = in_array($radiotype, ['default', 'image', 'svg', 'icon', 'buttons']) ? $radiotype : 'default';
            $this->radiotype = $return['radiotype'];

            $icononly = (string) $this->xml->attributes()->icononly;
            $return['icononly'] = strtolower($icononly) == "true" ? true : false;

            $return['options'] = $this->getOptions();
            break;
         case 'text':
            $placeholder = (string) $this->xml->attributes()->placeholder;
            $return['placeholder'] = \JText::_($placeholder);

            $alphanumeric = (string) $this->xml->attributes()->alphanumeric;
            $return['alphanumeric'] = ($alphanumeric === 'true' ? true : false);
            
            $slug = isset($this->xml->attributes()->slug) ? (string) $this->xml->attributes()->slug : false;
            $return['slug'] = ($slug === false ? false : $slug);
            break;
         case 'jcategory':
            $extension = (string) $this->xml->attributes()->{'extension'};
            $extension = empty($extension) ? 'com_content' : $extension;
            $return['extension'] = $extension;
            break;
         case 'date':
            $placeholder = (string) $this->xml->attributes()->placeholder;
            $return['placeholder'] = \JText::_($placeholder);

            $min = (string) $this->xml->attributes()->min;
            $return['min'] = empty($min) ? '' : $min;

            $max = (string) $this->xml->attributes()->max;
            $return['max'] = empty($max) ? '' : $max;
            break;
         case 'number':
            $placeholder = (string) $this->xml->attributes()->placeholder;
            $return['placeholder'] = \JText::_($placeholder);

            $min = (string) $this->xml->attributes()->min;
            $return['min'] = empty($min) ? 0 : ($min + 0);

            $max = (string) $this->xml->attributes()->max;
            $return['max'] = empty($max) ? 10000 : ($max + 0);

            $step = (string) $this->xml->attributes()->step;
            $return['step'] = empty($step) ? 1 : ($step + 0);
            break;
         case 'textarea':
            $placeholder = (string) $this->xml->attributes()->placeholder;
            $return['placeholder'] = \JText::_($placeholder);

            $rows = (string) $this->xml->attributes()->rows;
            $return['rows'] = $rows == "" ? 6 : (int) $rows;

            break;
         case 'checkbox':
            $return['options'] = $this->getOptions();
            break;
         case 'header_tag':
            $this->value = (!in_array(strtolower($this->value), ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'p', 'small', 'mark', 'abbr', 'blockquote', 'code', 'pre']) ? 'h3' : strtolower($this->value));
            break;
         case 'div':
            $id = (string) $this->xml->attributes()->id;
            $class = (string) $this->xml->attributes()->class;
            $return['id'] = $id;
            $return['class'] = $class;
            break;
         case 'repeatable':
            $return['fields'] = $this->getRepeatableFields();
            $itemtitle = (string) $this->xml->attributes()->{'item-title'};
            if (!empty($itemtitle)) {
               $return['itemtitle'] = \JText::_($itemtitle);
            }
            if ($this->taggable) {
               $tagkey = (string) $this->xml->attributes()->{'tag-key'};
               $tagExclude = (string) $this->xml->attributes()->{'tag-exclude'};
               if (!empty($tagkey)) {
                  $return['tagkey'] = $tagkey;
                  $return['tagExclude'] = empty($tagExclude) ? [] : \json_decode($tagExclude);
               } else {
                  $this->taggable = false;
               }
            }
            $itemicon = (string) $this->xml->attributes()->{'item-icon'};
            if (!empty($itemicon)) {
               $return['itemicon'] = \JText::_($itemicon);
            }
            $itemTitleField = (string) $this->xml->attributes()->{'item-title-field'};
            if (!empty($itemTitleField)) {
               $return['titlefield'] = $itemTitleField;
            }
            $itemIconField = (string) $this->xml->attributes()->{'item-icon-field'};
            if (!empty($itemIconField)) {
               $return['iconfield'] = $itemIconField;
            }
            break;
         case 'subform':
            $return['fields'] = $this->getRepeatableFields();
            break;
         case 'slider':
            $slider = (string) $this->xml->attributes()->slider;
            $return['slider'] = strtolower($slider) == 'false' ? false : true;

            $changeOnSlide = (string) $this->xml->attributes()->changeOnSlide;
            $return['changeOnSlide'] = strtolower($changeOnSlide) == 'false' ? false : true;

            $min = (string) $this->xml->attributes()->min;
            $return['min'] = empty($min) ? 0 : ($min + 0);

            $max = (string) $this->xml->attributes()->max;
            $return['max'] = empty($max) ? 100 : ($max + 0);

            $step = (string) $this->xml->attributes()->step;
            $return['step'] = empty($step) ? 1 : ($step + 0);

            $unit = (string) $this->xml->attributes()->unit;
            $return['unit'] = $unit;

            $units = (string) $this->xml->attributes()->units;
            if (empty($units)) {

               if (is_array($this->value)) {
                  $default = (string) $this->xml->attributes()->default;
                  $this->value = $default;
               }

               if (empty($this->value) && (is_array($this->value) || is_object($this->value))) {
                  $this->value = null;
               }

               $value = new \stdClass();
               $value->value = isset($this->value->value) ? $this->value->value : $this->value;
               $value->unit = $unit;
               $this->value = $value;
            }
            $return['units'] = empty($units) ? (empty($unit) ? [] : [$unit]) : explode(',', $units);
            break;
         case 'color':
            $small = (string) $this->xml->attributes()->small;
            $alpha = (string) $this->xml->attributes()->alpha;
            $rgba = (string) $this->xml->attributes()->rgba;
            $return['small'] = strtolower($small) == 'true' ? true : false;
            $return['alpha'] = strtolower($alpha) == 'false' ? false : true;
            $return['rgba'] = strtolower($rgba) == 'false' ? false : true;
            break;
         case 'code-editor':
            $language = (string) $this->xml->attributes()->language;
            $language = empty($language) ? 'html' : $language;
            $return['language'] = strtolower($language);

            $mini = (string) $this->xml->attributes()->mini;
            $mini = empty($mini) ? 'false' : $mini;
            $return['mini'] = strtolower($mini);
            break;
         case 'list':
            $multiple = (string) $this->xml->attributes()->multiple;
            $search = (string) $this->xml->attributes()->search;
            $return['multiple'] = strtolower($multiple) == 'true' ? true : false;
            $return['search'] = strtolower($search) == 'false' ? false : true;
            $return['options'] = $this->getOptions();
            $return['groups'] = $this->getOptionGroup();
            break;
         case 'tab':
            $return['options'] = $this->getOptions();
            $tab = (string) $this->xml->attributes()->tab;
            $return['tab'] = $tab;
            break;
         case 'jposition':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $return['search'] = true;
            $return['options'] = $this->getModulePositions();
            $return['groups'] = $this->getOptionGroup();
            break;
         case 'jmodule':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $return['search'] = true;
            $return['options'] = $this->getModules();
            $return['groups'] = [];
            break;
         case 'shapedividers':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $return['search'] = true;
            $return['options'] = $this->getShapeDividers();
            $return['groups'] = [];
            break;
         case 'particlespresets':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $return['search'] = true;
            $return['options'] = $this->getParticlesPresets();
            $return['groups'] = [];
            break;
         case 'accesslevel':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $this->value = (empty($this->value) || !is_numeric($this->value)) ? '' : $this->value;
            $return['search'] = false;
            $return['options'] = $this->getAccessLevels();
            $return['groups'] = [];
            break;
         case 'ajax':
            $return['type'] = 'ajax';
            $multiple = (string) $this->xml->attributes()->multiple;
            $return['multiple'] = strtolower($multiple) == 'true' ? true : false;
            $this->value = (empty($this->value) || !is_numeric($this->value)) ? '' : $this->value;
            $return['search'] = true;
            $return['options'] = [];
            $return['groups'] = [];
            break;
         case 'category':
            $return['type'] = 'list';
            $multiple = (string) $this->xml->attributes()->multiple;
            $return['multiple'] = strtolower($multiple) == 'true' ? true : false;
            if ($return['multiple']) {
               $this->value = empty($this->value) ? [] : Helper::jsonDecode($this->value);
            }
            $return['search'] = true;
            $return['options'] = $this->getCategoryOptions();
            $return['groups'] = [];
            break;
         case 'language':
            $return['type'] = 'list';
            $multiple = (string) $this->xml->attributes()->multiple;
            $return['multiple'] = strtolower($multiple) == 'true' ? true : false;
            if ($return['multiple']) {
               $this->value = empty($this->value) ? [] : Helper::jsonDecode($this->value);
            }
            $return['search'] = true;
            $return['options'] = $this->getLanguageOptions();
            $return['groups'] = [];
            break;
         case 'contentlanguage':
            $return['type'] = 'list';
            $multiple = (string) $this->xml->attributes()->multiple;
            $return['multiple'] = strtolower($multiple) == 'true' ? true : false;
            if ($return['multiple']) {
               $this->value = empty($this->value) ? [] : Helper::jsonDecode($this->value);
            }
            $return['search'] = true;
            $return['options'] = $this->getContentLanguageOptions();
            $return['groups'] = [];
            break;
         case 'typetag':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $return['search'] = false;
            $return['options'] = $this->getTypeTags();
            $return['groups'] = [];
            break;
         case 'hover-animations':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $return['search'] = true;
            $return['options'] = $this->getHoverAnimations();
            $return['groups'] = [];
            break;
         case 'icon-animations':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $return['search'] = true;
            $return['options'] = $this->getIconAnimations();
            $return['groups'] = [];
            break;
         case 'font-family':
            $return['type'] = 'list';
            $return['fonts'] = true;
            $return['multiple'] = false;
            $return['search'] = true;
            $return['options'] = [];
            $return['groups'] = [];
            break;
         case 'menuitems':
            $return['type'] = 'list';
            $return['menuitems'] = true;
            $return['multiple'] = false;
            $return['search'] = true;
            $return['options'] = [];
            $return['groups'] = [];
            break;
         case 'chromestyle':
            $return['type'] = 'list';
            $return['multiple'] = false;
            $return['search'] = false;
            $return['options'] = [];
            $return['groups'] = $this->getChromeGroups();
            break;
         case 'boxshadow':
            $return['textshadow'] = false;
            break;
         case 'textshadow':
            $return['type'] = 'boxshadow';
            $return['textshadow'] = true;
            break;
      }

      if (!in_array($this->type, Form::$fields_without_value)) {
         if ($this->responsive) {
            $value = [];

            if (isset($this->value->md) || isset($this->value->sm) || isset($this->value->xs)) {
               foreach (Helper::$devices as $deviceKey => $device) {
                  if (isset($this->value->{$deviceKey})) {
                     $value[$deviceKey] = $this->value->{$deviceKey};
                  } else {
                     foreach (Helper::$devices as $dk => $d) {
                        if (($dk != $deviceKey) && isset($this->value->{$dk})) {
                           $value[$deviceKey] = $this->value->{$dk};
                           break;
                        }
                     }
                  }
               }
            } else {
               foreach (Helper::$devices as $deviceKey => $device) {
                  $value[$deviceKey] = $this->value;
               }
            }
            $return['value'] = $value;
         } else {
            $return['value'] = $this->value;
         }
         // $return['default'] = $this->value;
      }
      $return['invisible'] = $this->invisible;
      if ($this->showInfo) {
         $return['showInfo'] = true;
      }
      if ($this->taggable) {
         $return['taggable'] = true;
      }
      return $return;
   }

   public function getOptions()
   {
      $options = [];
      foreach ($this->xml->option as $option) {
         $label = (string) $option;
         $value = (string) $option->attributes()->value;
         $item = [];
         $item['label'] = \JText::_($label);
         $item['value'] = $value;
         if ($this->type == "radio") {
            if (in_array($this->radiotype, ['image', 'svg', 'icon', 'buttons'])) {
               $radiotype = $this->radiotype;
               if ($radiotype == 'buttons') {
                  $radiotype = 'icon';
                  $icon = (string) $option->attributes()->icon;
                  if (empty($icon)) {
                     $svg = (string) $option->attributes()->svg;
                     if (!empty($svg)) {
                        $radiotype = 'svg';
                     }
                  }
               }
               $radiotypevalue = (string) $option->attributes()->{$radiotype};
               if (!empty($radiotypevalue)) {
                  if ($radiotype == 'svg' || $radiotype == 'image') {
                     $radiotypevalue = \JURI::root() . $radiotypevalue;
                  }
                  $item[$radiotype] = $radiotypevalue;
               }
            }
         }
         $options[] = $item;
      }
      return $options;
   }

   public function getModulePositions()
   {

      \JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php');

      $options = [];
      $options[] = ['label' => \JText::_('JDB_NONE'), 'value' => ''];
      $positions = \ModulesHelper::getPositions(0);
      foreach ($positions as $option) {
         $item = [];
         $item['label'] = \JText::_($option->text);
         $item['value'] = $option->value;
         $options[] = $item;
      }

      return $options;
   }

   public function getModules()
   {
      $options = [];

      $db = \JFactory::getDbo();
      $query = "SELECT `#__modules`.*,`#__usergroups`.`title` as `access_title` FROM `#__modules` JOIN `#__usergroups` ON `#__usergroups`.`id`=`#__modules`.`access` WHERE `#__modules`.`client_id`=0";
      $db->setQuery($query);
      $results = $db->loadObjectList();

      $options[] = ['label' => \JText::_('JDB_NONE'), 'value' => ''];

      foreach ($results as $result) {
         $item = [];


         $label = [];
         if (!$result->published) {
            $label[] = '[' . \JText::_('JUNPUBLISHED') . ']';
         }
         $label[] = '[' . $result->id . ']';
         if (!empty($result->position)) {
            $label[] = '[' . $result->position . ']';
         }

         $item['label'] = \JText::_($result->title) . ' ' . implode(' ', $label);
         $item['value'] = $result->id;
         $options[] = $item;
      }

      return $options;
   }

   public static function getShapeDividers()
   {
      $options = [];
      $options[] = ['label' => \JText::_('JDB_NONE'), 'value' => ''];

      $path = JDBPATH_MEDIA . '/data/shape-dividers';
      $dividers = glob($path . "/*.svg");
      foreach ($dividers as $divider) {
         $name = basename($divider);
         $value = str_replace('.svg', '', $name);
         $label = 'JDB_SHAPEDIVIDER_' . str_replace('-', '_', strtoupper($value));
         $item = [];
         $item['label'] = \JText::_($label);
         $item['value'] = $value;
         $options[] = $item;
      }

      return $options;
   }

   public static function getParticlesPresets()
   {
      $options = [];
      
      $path = JDBPATH_MEDIA . '/data/particles-presets';
      $presets = glob($path . "/*.json");
      foreach ($presets as $preset) {
         $name = basename($preset);
         $value = str_replace('.json', '', $name);
         $label = 'JDB_PARTICLESPRESETS_' . str_replace('-', '_', strtoupper($value));
         $item = [];
         $item['label'] = \JText::_($label);
         $item['value'] = $value;
         if($value == 'default'){
            array_unshift($options, $item);
         }else{
            $options[] = $item;
         }
      }

      return $options;
   }

   public function getAccessLevels()
   {
      $db = \JFactory::getDbo();
      $query = $db->getQuery(true);

      $query->select('a.id AS value, a.title AS label');
      $query->from('#__viewlevels AS a');
      $query->group('a.id, a.title, a.ordering');
      $query->order('a.ordering ASC');
      $query->order($query->qn('title') . ' ASC');

      // Get the options.
      $db->setQuery($query);
      $options = $db->loadObjectList();
      $default = [['label' => \JText::_('JDB_DEFAULT'), 'value' => '']];
      return (array) $options;
   }

   public function getOptionGroup()
   {
      $groups = [];
      foreach ($this->xml->optgroup as $optgroup) {
         $grouptitle = (string) $optgroup->attributes()->label;
         $group = ['label' => \JText::_($grouptitle), 'options' => []];
         foreach ($optgroup->option as $option) {
            $label = (string) $option;
            $value = (string) $option->attributes()->value;
            $item = [];
            $item['label'] = \JText::_($label);
            $item['value'] = $value;
            $group['options'][] = $item;
         }
         $groups[] = $group;
      }
      return $groups;
   }

   public function getRepeatableFields()
   {
      $formgroup = new FieldGroup();
      foreach ($this->xml->form->field as $field) {
         $type = (string) $field->attributes()->type;
         if ($type != "group") {
            $formgroup->addField($field);
         }
      }
      $group = $formgroup->get();
      return $group['fields'];
   }

   public function getHoverAnimations()
   {
      $animations = Constants::HOVER_ANIMATIONS;
      $options = [];
      $options[] = ['label' => \JText::_('JDB_NONE'), 'value' => ''];
      foreach ($animations as $value => $animation) {
         $item = [];
         $item['label'] = \JText::_($animation);
         $item['value'] = $value;
         $options[] = $item;
      }
      return $options;
   }

   public function getIconAnimations()
   {
      $animations = Constants::ICON_HOVER_ANIMATIONS;
      $options = [];
      $options[] = ['label' => \JText::_('JDB_NONE'), 'value' => ''];
      foreach ($animations as $animation) {
         $item = [];
         $item['label'] = Helper::titlecase($animation);
         $item['value'] = $animation;
         $options[] = $item;
      }
      return $options;
   }

   public function getFonts()
   {

      $options = [];
      $custom_fonts = [];
      $custom_fonts['label'] = \JText::_('JDB_CUSTOM_FONTS_TITLE');
      $custom_fonts['type'] = "custom";
      $custom_fonts['options'] = [];
      $customFonts = Field::customFonts();
      $coptions = [];
      if (!empty($customFonts)) {
         foreach ($customFonts as $id => $customFont) {
            $coptions[] = ['label' => $customFont['name'], 'value' => "c~" . $id];
         }
         $custom_fonts['options'] = $coptions;
      }
      $options[] = $custom_fonts;

      $system_fonts = [];
      $system_fonts['label'] = \JText::_('JDB_SYSTEM_FONTS_TITLE');
      $system_fonts['type'] = "system";
      $system_fonts['options'] = $this->getSystemFonts();
      $options[] = $system_fonts;


      $google_fonts = [];
      $google_fonts['label'] = \JText::_('JDB_GOOGLE_FONTS_TITLE');
      $google_fonts['type'] = "google";
      $google_fonts['options'] = [];
      $options[] = $google_fonts;
      return $options;
   }

   public static function getSystemFonts()
   {
      $options = [];
      foreach (Constants::SYSTEM_FONTS as $value => $label) {
         $options[] = ['label' => $label, 'value' => "s~" . $value];
      }
      return $options;
   }

   public static function getCustomFonts()
   {
      $fonts_path = JPATH_PLUGINS . '/system/jdbuilder/fonts';
      if (!file_exists($fonts_path)) {
         return [];
      }
      $fonts = [];
      $font_extensions = ['otf', 'ttf', 'woff'];
      foreach (scandir($fonts_path) as $font_path) {
         if (is_file($fonts_path . '/' . $font_path)) {
            $pathinfo = pathinfo($fonts_path . '/' . $font_path);
            if (in_array($pathinfo['extension'], $font_extensions)) {
               $font = \FontLib\Font::load($fonts_path . '/' . $font_path);
               $font->parse();
               $fontname = $font->getFontFullName();
               $fontid = 'library-font-' . \JFilterOutput::stringURLSafe($fontname);
               if (!isset($fonts[$fontid])) {
                  $fonts[$fontid] = [];
                  $fonts[$fontid]['id'] = $fontid;
                  $fonts[$fontid]['name'] = $fontname;
                  $fonts[$fontid]['files'] = [];
               }
               $fonts[$fontid]['files'][] = \JURI::root() . 'plugins/system/jdbuilder/fonts/' . $font_path;
            }
         }
      }
      file_put_contents($fonts_path . '/fonts.json', \json_encode($fonts));
      return $fonts;
   }

   public static function getGoogleFonts()
   {
      $options = [];
      foreach (Constants::SYSTEM_FONTS as $value => $label) {
         $options[] = ['label' => $label, 'value' => "s~" . $value];
      }
      return $options;
   }

   public function getCategoryOptions()
   {

      $return = [['label' => \JText::_('JDB_NONE'), 'value' => '0']];
      $extension = (string) $this->xml->attributes()->extension;
      $scope = (string) $this->xml->attributes()->scope;

      $options = array();

      $published = (string) $this->xml->attributes()->published;
      $language = (string) $this->xml->attributes()->language;

      // Load the category options for a given extension.
      if (!empty($extension)) {
         // Filter over published state or not depending upon if it is present.
         $filters = array();
         if ($published) {
            $filters['filter.published'] = explode(',', $published);
         }

         // Filter over language depending upon if it is present.
         if ($language) {
            $filters['filter.language'] = explode(',', $language);
         }

         if ($filters === array()) {
            $options = \JHtml::_('category.options', $extension);
         } else {
            $options = \JHtml::_('category.options', $extension, $filters);
         }

         // Verify permissions.  If the action attribute is set, then we scan the options.
         if ((string) $this->xml->attributes()->action) {
            // Get the current user object.
            $user = \JFactory::getUser();

            foreach ($options as $i => $option) {
               /*
                * To take save or create in a category you need to have create rights for that category
                * unless the item is already in that category.
                * Unset the option if the user isn't authorised for it. In this field assets are always categories.
                */
               if ($user->authorise('core.create', $extension . '.category.' . $option->value) === false) {
                  unset($options[$i]);
               }
            }
         }

         $show_root = (string) $this->xml->attributes()->show_root;

         if (!empty($show_root)) {
            array_unshift($options, \JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
         }

         foreach ($options as $option) {
            $return[] = ['label' => $option->text, 'value' => $option->value];
         }
      }
      return $return;
   }

   public function getLanguageOptions()
   {
      // Initialize some field attributes.
      $client = 'site';

      // Make sure the languages are sorted base on locale instead of random sorting
      $languages = \JLanguageHelper::createLanguageList($this->value, constant('JPATH_' . strtoupper($client)), true, true);
      if (count($languages) > 1) {
         usort($languages, function ($a, $b) {
            return strcmp($a['value'], $b['value']);
         });
      }

      // Merge any additional options in the XML definition.
      // Set the default value active language

      if ($langParams = \JComponentHelper::getParams('com_languages')) {
         switch ((string) $this->value) {
            case 'site':
            case 'frontend':
            case '0':
               $this->value = $langParams->get('site', 'en-GB');
               break;
            case 'admin':
            case 'administrator':
            case 'backend':
            case '1':
               $this->value = $langParams->get('administrator', 'en-GB');
               break;
            case 'active':
            case 'auto':
               $lang = \JFactory::getLanguage();
               $this->value = $lang->getTag();
               break;
            default:
               break;
         }
      }
      $options = [['label' => 'JALL', 'value' => '*']];
      foreach ($languages as $language) {
         $options[] = ['label' => $language['text'], 'value' => $language['value']];
      }
      return $options;
   }

   public function getContentLanguageOptions()
   {
      $options = [['label' => \JText::_('JALL'), 'value' => '*']];

      $languages = \JHtml::_('contentlanguage.existing');

      foreach ($languages as $language) {
         $options[] = ['label' => $language->title_native, 'value' => $language->value];
      }

      return $options;
   }

   public function getChromeGroups()
   {
      $groups = array();

      $tmp = '---' . \JText::_('JLIB_FORM_VALUE_FROM_TEMPLATE') . '---';
      $groups[$tmp][] = \JHtml::_('select.option', '0', \JText::_('JLIB_FORM_VALUE_INHERITED'));

      $templateStyles = $this->getTemplateModuleStyles();

      // Create one new option object for each available style, grouped by templates
      foreach ($templateStyles as $template => $styles) {
         $template = ucfirst($template);
         $groups[$template] = array();

         foreach ($styles as $style) {
            $tmp = \JHtml::_('select.option', $template . '-' . $style, $style);
            $groups[$template][] = $tmp;
         }
      }

      reset($groups);


      $return = [];
      foreach ($groups as $key => $group) {
         $g = ['label' => $key, 'options' => []];
         foreach ($group as $option) {
            $g['options'][] = ['label' => $option->text, 'value' => $option->value];
         }
         $return[] = $g;
      }

      return $return;
   }

   public function getTemplateModuleStyles()
   {
      $moduleStyles = array();

      $templates = array($this->getSystemTemplate());
      $templates = array_merge($templates, $this->getTemplates());

      foreach ($templates as $template) {
         $modulesFilePath = JPATH_SITE . '/templates/' . $template->element . '/html/modules.php';
         if (file_exists($modulesFilePath)) {
            $modulesFileData = file_get_contents($modulesFilePath);

            preg_match_all('/function[\s\t]*modChrome\_([a-z0-9\-\_]*)[\s\t]*\(/i', $modulesFileData, $styles);

            if (!array_key_exists($template->element, $moduleStyles)) {
               $moduleStyles[$template->element] = array();
            }

            $moduleStyles[$template->element] = $styles[1];
         }
      }

      return $moduleStyles;
   }

   public function getSystemTemplate()
   {
      $template = new \stdClass();
      $template->element = 'system';
      $template->name = 'system';

      return $template;
   }

   public function getTemplates()
   {
      $db = \JFactory::getDbo();

      // Get the database object and a new query object.
      $query = $db->getQuery(true);

      // Build the query.
      $query->select('element, name')
         ->from('#__extensions')
         ->where('client_id = 0')
         ->where('type = ' . $db->quote('template'))
         ->where('enabled = 1');

      // Set the query and load the templates.
      $db->setQuery($query);

      return $db->loadObjectList('element');
   }

   public function getTypeTags()
   {
      $options = [['label' => \JText::_('JDB_DEFAULT'), 'value' => '']];
      foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span', 'small'] as $tag) {
         $options[] = ['label' => \JText::_('JDB_' . ucfirst($tag)), 'value' => $tag];
      }
      return $options;
   }
}
