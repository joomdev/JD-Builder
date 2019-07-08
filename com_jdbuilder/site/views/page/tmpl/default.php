<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Jdbuilder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 Hitesh Aggarwal
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$document = JFactory::getDocument();
JDPageBuilder\Builder::renderPage($this->item, 'page');
?>
