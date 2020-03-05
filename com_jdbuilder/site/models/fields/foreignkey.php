<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

jimport('joomla.form.formfield');

/**
 * Supports a value from an external table
 *
 * @since  1.6
 */
class JFormFieldForeignKey extends FormField {

   /**
    * The form field type.
    *
    * @var        string
    * @since    1.6
    */
   protected $type = 'foreignkey';
   private $input_type;
   private $table;
   private $key_field;
   private $value_field;

   /**
    * Method to get the field input markup.
    *
    * @return   string  The field input markup.
    *
    * @since    1.6
    */
   protected function getInput() {
      // Type of input the field shows
      $this->input_type = $this->getAttribute('input_type');

      // Database Table
      $this->table = $this->getAttribute('table');

      // The field that the field will save on the database
      $this->key_field = (string) $this->getAttribute('key_field');

      // The column that the field shows in the input
      $this->value_field = (string) $this->getAttribute('value_field');

      // Flag to identify if the fk_value is multiple
      $this->value_multiple = (int) $this->getAttribute('value_multiple', 0);

      // Flag to identify if the fk_value hides the trashed items
      $this->hideTrashed = (int) $this->getAttribute('hide_trashed', 0);

      // Flag to identify if the fk has default order
      $this->ordering = (int) $this->getAttribute('ordering', 0);

      // Initialize variables.
      $html = '';
      $fk_value = '';

      // Load all the field options
      $db = Factory::getDbo();
      $query = $db->getQuery(true);

      // Support for multiple fields on fk_values
      if ($this->value_multiple == 1) {
         // Get the fields for multiple value
         $this->value_fields = (string) $this->getAttribute('value_field_multiple');
         $this->value_fields = explode(',', $this->value_fields);
         $this->separator = (string) $this->getAttribute('separator');

         $fk_value = ' CONCAT(';

         foreach ($this->value_fields as $field) {
            $fk_value .= $db->quoteName($field) . ', \'' . $this->separator . '\', ';
         }

         $fk_value = substr($fk_value, 0, -(strlen($this->separator) + 6));
         $fk_value .= ') AS ' . $db->quoteName($this->value_field);
      } else {
         $fk_value = $db->quoteName($this->value_field);
      }

      $query
              ->select(
                      array(
                          $db->quoteName($this->key_field),
                          $fk_value
                      )
              )
              ->from($this->table);

      if ($this->hideTrashed) {
         $query->where($db->quoteName('state') . ' != -2');
      }

      if ($this->ordering) {
         $query->order('ordering ASC');
      }

      $db->setQuery($query);
      $results = $db->loadObjectList();

      $input_options = 'class="' . $this->getAttribute('class') . '"';

      // Depends of the type of input, the field will show a type or another
      switch ($this->input_type) {
         case 'list':
         default:
            $options = array();

            // Iterate through all the results
            foreach ($results as $result) {
               $options[] = JHtml::_('select.option', $result->{$this->key_field}, $result->{$this->value_field});
            }

            $value = $this->value;

            // If the value is a string -> Only one result
            if (is_string($value)) {
               $value = array($value);
            } elseif (is_object($value)) {
               // If the value is an object, let's get its properties.
               $value = get_object_vars($value);
            }

            // If the select is multiple
            if ($this->multiple) {
               $input_options .= 'multiple="multiple"';
            } else {
               array_unshift($options, JHtml::_('select.option', '', ''));
            }

            $html = JHtml::_('select.genericlist', $options, $this->name, $input_options, 'value', 'text', $value, $this->id);
            break;
      }

      return $html;
   }

   /**
    * Wrapper method for getting attributes from the form element
    *
    * @param   string  $attr_name  Attribute name
    * @param   mixed   $default    Optional value to return if attribute not found
    *
    * @return mixed The value of the attribute if it exists, null otherwise
    */
   public function getAttribute($attr_name, $default = null) {
      if (!empty($this->element[$attr_name])) {
         return $this->element[$attr_name];
      } else {
         return $default;
      }
   }

}
