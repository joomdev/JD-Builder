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
      $style = '#general .span9 .control-label{display: none}#general .controls{margin: 0}';
      $document->addStyleDeclaration($style);

      return '<input name="' . $this->name . '" type="hidden" id="' . $this->id . '" value="' . $this->value . '" />
      <script>
         _JDB.ITEM = {
            "id": ' . $this->value . ',
            "layout_id": ' . $this->value . ',
            "type": "module"
         };
      </script>
      <div>' . \JDPageBuilder\Builder::builderArea(true, 'module', $this->value) . '</div>';
   }
}
