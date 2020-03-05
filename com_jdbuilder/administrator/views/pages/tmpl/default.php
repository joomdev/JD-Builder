<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '\/helpers/');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'administrator/components/com_jdbuilder/assets/css/jdbuilder.css');
$document->addStyleSheet(JUri::root() . 'media/com_jdbuilder/css/list.css');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_jdbuilder');
$saveOrder = $listOrder == 'a.`ordering`';

if ($saveOrder) {
   $saveOrderingUrl = 'index.php?option=com_jdbuilder&task=pages.saveOrderAjax&tmpl=component';
   JHtml::_('sortablelist.sortable', 'pageList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>

<form action="<?php echo JRoute::_('index.php?option=com_jdbuilder&view=pages'); ?>" method="post" name="adminForm" id="adminForm">
   <?php if (!empty($this->sidebar)) : ?>
      <div id="j-sidebar-container" class="span2">
         <?php echo $this->sidebar; ?>
      </div>
      <div id="j-main-container" class="span10">
      <?php
      echo JdbuilderHelper::versionMessage();
   else : ?>
         <div id="j-main-container">
         <?php
         echo JdbuilderHelper::versionMessage();
      endif; ?>

         <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

         <div class="clearfix"></div>
         <table class="table" id="pageList">
            <thead>
               <tr>
                  <?php if (isset($this->items[0]->ordering)) : ?>
                     <th width="1%" class="nowrap center hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', '', 'a.`ordering`', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                     </th>
                  <?php endif; ?>
                  <th width="1%" class="hidden-phone">
                     <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                  </th>
                  <?php if (isset($this->items[0]->state)) : ?>
                     <th width="1%" class="nowrap center">
                        <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.`state`', $listDirn, $listOrder); ?>
                     </th>
                  <?php endif; ?>


                  <th class='left'>
                     <?php echo JHtml::_('searchtools.sort', 'COM_JDBUILDER_PAGES_TITLE', 'a.`title`', $listDirn, $listOrder); ?>
                  </th>
                  <th class='left'>
                     <?php echo JHtml::_('searchtools.sort', 'COM_JDBUILDER_PAGES_CATEGORY_ID', 'a.`category_id`', $listDirn, $listOrder); ?>
                  </th>
                  <th class='left'>
                     <?php echo JHtml::_('searchtools.sort', 'COM_JDBUILDER_PAGES_ACCESS', 'a.`access`', $listDirn, $listOrder); ?>
                  </th>
                  <th class='left'>
                     <?php echo JHtml::_('searchtools.sort', 'COM_JDBUILDER_PAGES_LANGUAGE', 'a.`language`', $listDirn, $listOrder); ?>
                  </th>
                  <th class='left'>
                     <?php echo JHtml::_('searchtools.sort', 'COM_JDBUILDER_PAGES_CREATED_BY', 'a.`created_by`', $listDirn, $listOrder); ?>
                  </th>
                  <th class='left'>
                     <?php echo JHtml::_('searchtools.sort', 'COM_JDBUILDER_PAGES_ID', 'a.`id`', $listDirn, $listOrder); ?>
                  </th>

               </tr>
            </thead>
            <tfoot>
               <tr>
                  <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
                     <?php echo $this->pagination->getListFooter(); ?>
                  </td>
               </tr>
            </tfoot>
            <tbody>
               <?php
               foreach ($this->items as $i => $item) :
                  $ordering = ($listOrder == 'a.ordering');
                  $canCreate = $user->authorise('core.create', 'com_jdbuilder');
                  $canEdit = $user->authorise('core.edit', 'com_jdbuilder');
                  $canCheckin = $user->authorise('core.manage', 'com_jdbuilder');
                  $canChange = $user->authorise('core.edit.state', 'com_jdbuilder');
               ?>
                  <tr class="row<?php echo $i % 2; ?>">

                     <?php if (isset($this->items[0]->ordering)) : ?>
                        <td class="order nowrap center hidden-phone">
                           <?php
                           if ($canChange) :
                              $disableClassName = '';
                              $disabledLabel = '';

                              if (!$saveOrder) :
                                 $disabledLabel = JText::_('JORDERINGDISABLED');
                                 $disableClassName = 'inactive tip-top';
                              endif;
                           ?>
                              <span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
                                 <i class="icon-menu"></i>
                              </span>
                              <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                           <?php else : ?>
                              <span class="sortable-handler inactive">
                                 <i class="icon-menu"></i>
                              </span>
                           <?php endif; ?>
                        </td>
                     <?php endif; ?>
                     <td class="hidden-phone">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                     </td>
                     <td class="center">
                        <div class="btn-group">
                           <!-- We will be use the $item->publish_up, $item->publish_down .   -->
                           <?php echo JHtml::_('jgrid.published', $item->state, $i, 'pages.', $canChange, 'cb'); ?>

                           <?php
                           // Create dropdown items and render the dropdown list.
                           if ($canChange) {
                              //JHtml::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'pages');
                              JHtml::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'pages');
                              echo JHtml::_('actionsdropdown.render', $this->escape($item->title));
                           }
                           ?>
                        </div>
                     </td>

                     <td>
                        <?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
                           <?php echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'pages.', $canCheckin); ?>
                        <?php endif; ?>
                        <?php if ($canEdit) : ?>
                           <a href="<?php echo JRoute::_('index.php?option=com_jdbuilder&task=page.edit&id=' . (int) $item->id); ?>">
                              <?php echo $this->escape($item->title); ?></a>
                        <?php else : ?>
                           <?php echo $this->escape($item->title); ?>
                        <?php endif; ?>

                     </td>
                     <td>

                        <?php echo $item->category_id; ?>
                     </td>
                     <td>

                        <?php echo $item->access; ?>
                     </td>
                     <td>

                        <?php echo $item->language == '*' ? \JText::_('JALL') : $item->language; ?>
                     </td>
                     <td>

                        <?php echo $item->created_by; ?>
                     </td>
                     <td>

                        <?php echo $item->id; ?>
                     </td>
                  </tr>
               <?php endforeach; ?>
            </tbody>
         </table>

         <input type="hidden" name="task" value="" />
         <input type="hidden" name="boxchecked" value="0" />
         <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
         <?php echo JHtml::_('form.token'); ?>
         <div class="component-version-container">
            <span class="component-version"><?php echo \JText::_('COM_JDBUILDER'); ?> <span>v<?php echo JDB_VERSION; ?></span><?php echo \JText::_('JDBUILDER_VERSION_LABEL'); ?>| Developed with <span style="color: red">&hearts;</span> by <a href="https://www.joomdev.com" target="_blank">JoomDev</a></span>
            <div class="support-link">
               <a href="https://docs.joomdev.com/category/jd-builder/" target="_blank">Documentation</a> <span>|</span> <a href="https://www.joomdev.com/jd-builder/changelog" target="_blank">Changelog</a> <span>|</span> <a href="https://www.joomdev.com/forum/jd-builder" target="_blank">Forum</a> <span>|</span> <a href="https://www.youtube.com/playlist?list=PLv9TlpLcSZTAnfiT0x10HO5GGaTJhUB1K" target="_blank">Video Tutorials</a> <span>|</span> <a href="https://extensions.joomla.org/extension/jd-builder" target="_blank"><span class="icon-joomla"></span> Rate Us</a>
            </div>
         </div>
         </div>
</form>
<script>
   window.toggleField = function(id, task, field) {

      var f = document.adminForm,
         i = 0,
         cbx, cb = f[id];

      if (!cb)
         return false;

      while (true) {
         cbx = f['cb' + i];

         if (!cbx)
            break;

         cbx.checked = false;
         i++;
      }

      var inputField = document.createElement('input');

      inputField.type = 'hidden';
      inputField.name = 'field';
      inputField.value = field;
      f.appendChild(inputField);

      cb.checked = true;
      f.boxchecked.value = 1;
      window.submitform(task);

      return false;
   };
</script>