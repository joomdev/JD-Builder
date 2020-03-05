<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
//JHtml::_('behavior.tooltip');
//JHtml::_('behavior.formvalidation');
//JHtml::_('formbehavior.chosen', 'select');
//JHtml::_('behavior.keepalive');
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_jdbuilder/css/form.css');
?>
<script type="text/javascript">
   Joomla.submitbutton = function(task) {
      if (task == 'page.cancel') {
         Joomla.submitform(task, document.getElementById('page-form'));
      } else {
         if (document.getElementById('jform_title').value == "") {
            return false;
         }
         Joomla.submitform(task, document.getElementById('page-form'));
      }
   }
</script>
<script id="jdb-form-page-permissions-template" type="text/x-lodash-template">
   <?php if (JFactory::getUser()->authorise('core.admin', 'jdbuilder')) : ?>
   <?php echo $this->form->getInput('rules'); ?>
<?php endif; ?>
</script>
<script>
   <?php
   $item = ["id" => $this->item->id, "title" => empty($this->item->title) ? '' : $this->item->title, "layout_id" => empty($this->item->layout_id) ? 0 : $this->item->layout_id, "type" => "page", "params" => $this->item->params];
   ?>
   _JDB.ITEM = <?php echo \json_encode($item); ?>;
</script>
<script>
   _JDB.ACCESS = <?php echo \json_encode($this->accessibility); ?>;
</script>
<form action="<?php echo JRoute::_('index.php?option=com_jdbuilder&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="page-form">
   <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
   <input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
   <input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
   <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
   <input type="hidden" name="jform[layout_id]" value="<?php echo $this->item->layout_id; ?>" />
   <?php echo $this->form->renderField('created_by'); ?>
   <?php echo $this->form->renderField('modified_by'); ?>

   <div class="form-horizontal hidden">
      <div class="row-fluid">
         <div class="span10 form-horizontal">
            <fieldset class="adminform">



               <?php //echo $this->form->renderField('title');    
               ?>
               <?php //echo $this->form->renderField('category_id');      
               ?>

               <?php
               foreach ((array) $this->item->category_id as $value) :
                  if (!is_array($value)) :
                     echo '<input type="hidden" class="category_id" name="jform[category_idhidden][' . $value . ']" value="' . $value . '" />';
                  endif;
               endforeach;
               ?>



               <?php if ($this->state->params->get('save_history', 1)) : ?>
                  <div class="control-group">
                     <div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
                     <div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
                  </div>
               <?php endif; ?>
            </fieldset>
         </div>
      </div>
      <input type="hidden" name="task" value="" />
   </div>
   <div>
      {jdbuilder}
   </div>
</form>