<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

// No direct access
defined('_JEXEC') or die('Restricted access');

class Layout extends BaseElement
{

   public $sections = [];

   public function __construct($object, $type = 'page', $id = 0, $indexMode = false)
   {
      parent::__construct($object);
      $this->id = 'jdb-layout-' . $this->id;
      $this->indexMode = $indexMode;
      $GLOBALS['jdlid'] = $this->id;
      $this->itemType = $type;
      $this->itemID = $id;
      $layout = \json_decode($object->layout, FALSE);
      if (isset($layout->sections)) {
         foreach ($layout->sections as $section) {
            $this->sections[] = new Section($section, $this);
         }
      }
      $this->addClass($this->id);
      $this->addClass('jdbuilder');
      //$this->addAttribute('jdb-layout');
   }

   public function getContent()
   {
      $content = [];
      foreach ($this->sections as $section) {
         $content[] = $section->render();
      }
      return implode("", $content);
   }
}
