<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Class JdbuilderController
 *
 * @since  1.6
 */
class JdbuilderController extends JControllerLegacy {

   /**
    * Method to display a view.
    *
    * @param   boolean $cachable  If true, the view output will be cached
    * @param   mixed   $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
    *
    * @return  JController   This object to support chaining.
    *
    * @since    1.5
    */
   public function display($cachable = false, $urlparams = false) {
      $app = JFactory::getApplication();
      $view = $app->input->getCmd('view', 'pages');
      $app->input->set('view', $view);

      parent::display($cachable, $urlparams);

      return $this;
   }

}
