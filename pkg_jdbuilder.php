<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

class pkg_jdbuilderInstallerScript
{

   private $min_php_version    = '7.2';

   /**
    * 
    * Function to run before installing the component	 
    */
   public function preflight($type, $parent)
   {
      if (!$this->passMinimumPHPVersion()) {

         return false;
      }
   }

   /**
    *
    * Function to run when installing the component
    * @return void
    */
   public function install($parent)
   {
   }

   /**
    *
    * Function to run when un-installing the component
    * @return void
    */
   public function uninstall($parent)
   {
   }

   /**
    * 
    * Function to run when updating the component
    * @return void
    */
   function update($parent)
   {
   }

   /**
    * 
    * Function to run after installing the component	 
    */
   public function postflight($type, $parent)
   {
   }

   private function passMinimumPHPVersion()
   {

      if (version_compare(PHP_VERSION, $this->min_php_version, 'l')) {
         \JFactory::getApplication()->enqueueMessage(
            \JText::sprintf(
               'You current PHP version is %s. The minimum recommended PHP version for JD Builder is PHP %s.',
               '<strong>' . PHP_VERSION . '</strong>',
               '<strong>' . $this->min_php_version . '</strong>'
            ),
            'error'
         );

         return false;
      }

      return true;
   }
}
