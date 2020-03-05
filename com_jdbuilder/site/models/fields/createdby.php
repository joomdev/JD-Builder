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
class JFormFieldCreatedby extends JFormField {

   /**
    * The form field type.
    *
    * @var        string
    * @since    1.6
    */
   protected $type = 'createdby';

   /**
    * Method to get the field input markup.
    *
    * @return   string  The field input markup.
    *
    * @since    1.6
    */
   protected function getInput() {
      // Initialize variables.
      $html = array();

      // Load user
      $user_id = $this->value;

      if ($user_id) {
         $user = JFactory::getUser($user_id);
      } else {
         $user = JFactory::getUser();
         $html[] = '<input type="hidden" name="' . $this->name . '" value="' . $user->id . '" />';
      }

      if (!$this->hidden) {
         $html[] = "<div>" . $user->name . " (" . $user->username . ")</div>";
      }

      return implode($html);
   }

}
