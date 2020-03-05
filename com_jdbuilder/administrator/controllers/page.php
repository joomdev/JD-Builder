<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Page controller class.
 *
 * @since  1.6
 */
class JdbuilderControllerPage extends JControllerForm {

   /**
    * Constructor
    *
    * @throws Exception
    */
   public function __construct() {
      $this->view_list = 'pages';
      parent::__construct();
   }

}
