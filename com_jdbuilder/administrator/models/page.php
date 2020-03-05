<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use JDPageBuilder\Helper;

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Jdbuilder model.
 *
 * @since  1.6
 */
class JdbuilderModelPage extends JModelAdmin
{

   /**
    * @var      string    The prefix to use with controller messages.
    * @since    1.6
    */
   protected $text_prefix = 'COM_JDBUILDER';

   /**
    * @var   	string  	Alias to manage history control
    * @since   3.2
    */
   public $typeAlias = 'com_jdbuilder.page';

   /**
    * @var null  Item data
    * @since  1.6
    */
   protected $item = null;

   /**
    * Returns a reference to the a Table object, always creating it.
    *
    * @param   string  $type    The table type to instantiate
    * @param   string  $prefix  A prefix for the table class name. Optional.
    * @param   array   $config  Configuration array for model. Optional.
    *
    * @return    JTable    A database object
    *
    * @since    1.6
    */
   public function getTable($type = 'Page', $prefix = 'JdbuilderTable', $config = array())
   {
      return JTable::getInstance($type, $prefix, $config);
   }

   /**
    * Method to get the record form.
    *
    * @param   array    $data      An optional array of data for the form to interogate.
    * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
    *
    * @return  JForm  A JForm object on success, false on failure
    *
    * @since    1.6
    */
   public function getForm($data = array(), $loadData = true)
   {
      // Initialise variables.
      $app = JFactory::getApplication();

      // Get the form.
      $form = $this->loadForm(
         'com_jdbuilder.page',
         'page',
         array(
            'control' => 'jform',
            'load_data' => $loadData
         )
      );



      if (empty($form)) {
         return false;
      }

      return $form;
   }

   /**
    * Method to get the data that should be injected in the form.
    *
    * @return   mixed  The data for the form.
    *
    * @since    1.6
    */
   protected function loadFormData()
   {
      // Check the session for previously entered form data.
      $data = JFactory::getApplication()->getUserState('com_jdbuilder.edit.page.data', array());

      if (empty($data)) {
         if ($this->item === null) {
            $this->item = $this->getItem();
         }

         $data = $this->item;


         // Support for multiple or not foreign key field: category_id
         $array = array();

         foreach ((array) $data->category_id as $value) {
            if (!is_array($value)) {
               $array[] = $value;
            }
         }
         if (!empty($array)) {

            $data->category_id = $array;
         }
      }

      return $data;
   }

   /**
    * Method to get a single record.
    *
    * @param   integer  $pk  The id of the primary key.
    *
    * @return  mixed    Object on success, false on failure.
    *
    * @since    1.6
    */
   public function getItem($pk = null)
   {

      if ($item = parent::getItem($pk)) {
         // Do any procesing on fields here if needed
      }

      return $item;
   }

   /**
    * Method to duplicate an Page
    *
    * @param   array  &$pks  An array of primary key IDs.
    *
    * @return  boolean  True if successful.
    *
    * @throws  Exception
    */
   public function duplicate(&$pks)
   {
      $user = JFactory::getUser();

      // Access checks.
      if (!$user->authorise('core.create', 'com_jdbuilder')) {
         throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
      }

      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
      }

      $dispatcher = JEventDispatcher::getInstance();
      $context = $this->option . '.' . $this->name;

      // Include the plugins for the save events.
      JPluginHelper::importPlugin($this->events_map['save']);

      $table = $this->getTable();

      foreach ($pks as $pk) {

         if ($table->load($pk, true)) {
            // Reset the id to create a new record.
            $table->id = 0;
            $table->title = $table->title . ' (Copy)';

            if (!$table->check()) {
               throw new Exception($table->getError());
            }


            // Trigger the before save event.
            $result = $dispatcher->trigger($this->event_before_save, array($context, &$table, true));

            // Copy layout
            $layout = JDPageBuilder\Helper::getLayout($table->layout_id);
            $layout->id = null;
            $db = JFactory::getDbo();
            $db->insertObject('#__jdbuilder_layouts', $layout);
            $table->layout_id = $db->insertid();
            $table->state = 0;

            if (in_array(false, $result, true) || !$table->store()) {
               throw new Exception($table->getError());
            }

            // Trigger the after save event.
            $dispatcher->trigger($this->event_after_save, array($context, &$table, true));
         } else {
            throw new Exception($table->getError());
         }
      }

      // Clean cache
      $this->cleanCache();

      return true;
   }

   /**
    * Prepare and sanitise the table prior to saving.
    *
    * @param   JTable  $table  Table Object
    *
    * @return void
    *
    * @since    1.6
    */
   protected function prepareTable($table)
   {
      jimport('joomla.filter.output');

      if (empty($table->id)) {
         // Set ordering to the last item if not set
         if (@$table->ordering === '') {
            $db = JFactory::getDbo();
            $db->setQuery('SELECT MAX(ordering) FROM #__jdbuilder_pages');
            $max = $db->loadResult();
            $table->ordering = $max + 1;
         }
      }
   }

   public function save($data)
   {
      if (Helper::isBuilderDemo()) {
         throw new \Exception(\JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
      }

      $input = JFactory::getApplication()->input;

      // Alter the title for save as copy
      if ($input->get('task') == 'save2copy') {
         $origTable = clone $this->getTable();
         $origTable->load($input->getInt('id'));

         if ($data['title'] == $origTable->title) {
            //list($title, $alias) = $this->generateNewTitle($data['category_id'], $data['alias'], $data['title']);
            $data['title'] = $data['title'] . '_copy';
         }

         $layout = JDPageBuilder\Helper::getLayout($data['layout_id']);
         $layout->id = null;
         $db = JFactory::getDbo();
         $db->insertObject('#__jdbuilder_layouts', $layout);
         $data['layout_id'] = $db->insertid();

         $data['state'] = 0;
      }

      return parent::save($data);
   }
}
