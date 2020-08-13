<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

class JFormFieldJdbeditor extends JFormField
{

   protected $type = 'jdbeditor';

   public function getLabel()
   {
      return false;
   }

   public function getInput()
   {
      $document = JFactory::getDocument();
      if (JDB_JOOMLA_VERSION == 3) {
         $style = '#general .span9 .control-label{display: none}#general .controls{margin: 0}';
      } else {
         $style = '#general .col-lg-9 .card-body .control-label{display: none}#general .controls{margin: 0} #subhead{ z-index: 9999 !important;}';
      }
      $document->addStyleSheet(\JURI::root() . 'media/com_jdbuilder/css/style.min.css', ['version' => JDB_MEDIA_VERSION]);
      $document->addStyleDeclaration($style);

      return '<input name="' . $this->name . '" type="hidden" id="' . $this->id . '" value="' . $this->value . '" />
      <script>
         var _JDB = {};
         _JDB.ITEM = {
            "id": ' . $this->value . ',
            "layout_id": ' . $this->value . ',
            "type": "module"
         };
      </script>
      <div>' . \JDPageBuilder\Builder::builderArea(true, 'module', $this->value) . '</div>';
   }
}
