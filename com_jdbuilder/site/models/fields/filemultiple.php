<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldFileMultiple extends JFormField {

   /**
    * The form field type.
    *
    * @var        string
    * @since    1.6
    */
   protected $type = 'file';

   /**
    * Method to get the field input markup.
    *
    * @return    string    The field input markup.
    *
    * @since    1.6
    */
   protected function getInput() {
      // Initialize variables.
      $html = '<input type="file" name="' . $this->name . '[]" multiple >';

      return $html;
   }

}
