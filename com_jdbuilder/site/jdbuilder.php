<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jdbuilder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 Hitesh Aggarwal
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Jdbuilder', JPATH_COMPONENT);
JLoader::register('JdbuilderController', JPATH_COMPONENT . '/controller.php');


// Execute the task.
$controller = JControllerLegacy::getInstance('Jdbuilder');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
