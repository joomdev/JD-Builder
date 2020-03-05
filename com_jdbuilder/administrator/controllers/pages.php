<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;

/**
 * Pages list controller class.
 *
 * @since  1.6
 */
class JdbuilderControllerPages extends JControllerAdmin {

   /**
    * Method to clone existing Pages
    *
    * @return void
    */
   public function duplicate() {
      // Check for request forgeries
      Jsession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

      // Get id(s)
      $pks = $this->input->post->get('cid', array(), 'array');

      try {
         if (empty($pks)) {
            throw new Exception(JText::_('COM_JDBUILDER_NO_ELEMENT_SELECTED'));
         }

         ArrayHelper::toInteger($pks);
         $model = $this->getModel();
         $model->duplicate($pks);
         $this->setMessage(Jtext::_('COM_JDBUILDER_ITEMS_SUCCESS_DUPLICATED'));
      } catch (Exception $e) {
         JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
      }

      $this->setRedirect('index.php?option=com_jdbuilder&view=pages');
   }

   /**
    * Proxy for getModel.
    *
    * @param   string  $name    Optional. Model name
    * @param   string  $prefix  Optional. Class prefix
    * @param   array   $config  Optional. Configuration array for model
    *
    * @return  object	The Model
    *
    * @since    1.6
    */
   public function getModel($name = 'page', $prefix = 'JdbuilderModel', $config = array()) {
      $model = parent::getModel($name, $prefix, array('ignore_request' => true));

      return $model;
   }

   /**
    * Method to save the submitted ordering values for records via AJAX.
    *
    * @return  void
    *
    * @since   3.0
    */
   public function saveOrderAjax() {
      // Get the input
      $input = JFactory::getApplication()->input;
      $pks = $input->post->get('cid', array(), 'array');
      $order = $input->post->get('order', array(), 'array');

      // Sanitize the input
      ArrayHelper::toInteger($pks);
      ArrayHelper::toInteger($order);

      // Get the model
      $model = $this->getModel();

      // Save the ordering
      $return = $model->saveorder($pks, $order);

      if ($return) {
         echo "1";
      }

      // Close the application
      JFactory::getApplication()->close();
   }

}
