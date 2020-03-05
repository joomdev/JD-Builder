<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Page controller class.
 *
 * @since  1.6
 */
class JdbuilderControllerPage extends JControllerLegacy {

   /**
    * Method to check out an item for editing and redirect to the edit form.
    *
    * @return void
    *
    * @since    1.6
    */
   public function edit() {
      $app = JFactory::getApplication();

      // Get the previous edit id (if any) and the current edit id.
      $previousId = (int) $app->getUserState('com_jdbuilder.edit.page.id');
      $editId = $app->input->getInt('id', 0);

      // Set the user id for the user to edit in the session.
      $app->setUserState('com_jdbuilder.edit.page.id', $editId);

      // Get the model.
      $model = $this->getModel('Page', 'JdbuilderModel');

      // Check out the item
      if ($editId) {
         $model->checkout($editId);
      }

      // Check in the previous user.
      if ($previousId && $previousId !== $editId) {
         $model->checkin($previousId);
      }

      // Redirect to the edit screen.
      $this->setRedirect(JRoute::_('index.php?option=com_jdbuilder&view=pageform&layout=edit', false));
   }

   /**
    * Method to save a user's profile data.
    *
    * @return    void
    *
    * @throws Exception
    * @since    1.6
    */
   public function publish() {
      // Initialise variables.
      $app = JFactory::getApplication();

      // Checking if the user can remove object
      $user = JFactory::getUser();

      if ($user->authorise('core.edit', 'com_jdbuilder') || $user->authorise('core.edit.state', 'com_jdbuilder')) {
         $model = $this->getModel('Page', 'JdbuilderModel');

         // Get the user data.
         $id = $app->input->getInt('id');
         $state = $app->input->getInt('state');

         // Attempt to save the data.
         $return = $model->publish($id, $state);

         // Check for errors.
         if ($return === false) {
            $this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
         }

         // Clear the profile id from the session.
         $app->setUserState('com_jdbuilder.edit.page.id', null);

         // Flush the data from the session.
         $app->setUserState('com_jdbuilder.edit.page.data', null);

         // Redirect to the list screen.
         $this->setMessage(JText::_('COM_JDBUILDER_ITEM_SAVED_SUCCESSFULLY'));
         $menu = JFactory::getApplication()->getMenu();
         $item = $menu->getActive();

         if (!$item) {
            // If there isn't any menu item active, redirect to list view
            $this->setRedirect(JRoute::_('index.php?option=com_jdbuilder&view=pages', false));
         } else {
            $this->setRedirect(JRoute::_('index.php?Itemid=' . $item->id, false));
         }
      } else {
         throw new Exception(500);
      }
   }

   /**
    * Remove data
    *
    * @return void
    *
    * @throws Exception
    */
   public function remove() {
      // Initialise variables.
      $app = JFactory::getApplication();

      // Checking if the user can remove object
      $user = JFactory::getUser();

      if ($user->authorise('core.delete', 'com_jdbuilder')) {
         $model = $this->getModel('Page', 'JdbuilderModel');

         // Get the user data.
         $id = $app->input->getInt('id', 0);

         // Attempt to save the data.
         $return = $model->delete($id);

         // Check for errors.
         if ($return === false) {
            $this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
         } else {
            // Check in the profile.
            if ($return) {
               $model->checkin($return);
            }

            $app->setUserState('com_jdbuilder.edit.inventory.id', null);
            $app->setUserState('com_jdbuilder.edit.inventory.data', null);

            $app->enqueueMessage(JText::_('COM_JDBUILDER_ITEM_DELETED_SUCCESSFULLY'), 'success');
            $app->redirect(JRoute::_('index.php?option=com_jdbuilder&view=pages', false));
         }

         // Redirect to the list screen.
         $menu = JFactory::getApplication()->getMenu();
         $item = $menu->getActive();
         $this->setRedirect(JRoute::_($item->link, false));
      } else {
         throw new Exception(500);
      }
   }

}
