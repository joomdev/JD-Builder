<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomdev\Component\JDBuilder\Administrator\Helper\JdbuilderHelper;
use Joomla\CMS\Button\PublishedButton;

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(JUri::root() . 'administrator/components/com_jdbuilder/assets/css/jdbuilder.css');
$document->addStyleSheet(JUri::root() . 'media/com_jdbuilder/css/list.css');

$user = Factory::getUser();
$userId = $user->get('id');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo Route::_('index.php?option=com_jdbuilder&view=pages'); ?>" method="post" name="adminForm" id="adminForm">
   <div id="j-main-container" class="j-main-container">
      <?php
      echo JdbuilderHelper::versionMessage();
      ?>
      <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
      <table class="table" id="pageList">
         <thead>
            <tr>
               <td style="width:1%" class="text-center">
                  <?php echo HTMLHelper::_('grid.checkall'); ?>
               </td>
               <th scope="col" style="width:1%; min-width:85px" class="text-center">
                  <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
               </th>
               <th scope="col">
                  <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
               </th>
               <th scope="col">
                  <?php echo HTMLHelper::_('searchtools.sort', 'COM_JDBUILDER_PAGES_CATEGORY_ID', 'a.category_id', $listDirn, $listOrder); ?>
               </th>
               <th scope="col">
                  <?php echo HTMLHelper::_('searchtools.sort', 'COM_JDBUILDER_PAGES_ACCESS', 'a.access', $listDirn, $listOrder); ?>
               </th>
               <th scope="col">
                  <?php echo HTMLHelper::_('searchtools.sort', 'COM_JDBUILDER_PAGES_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
               </th>
               <th scope="col">
                  <?php echo HTMLHelper::_('searchtools.sort', 'COM_JDBUILDER_PAGES_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
               </th>
               <th scope="col">
                  <?php echo HTMLHelper::_('searchtools.sort', 'COM_JDBUILDER_PAGES_ID', 'a.id', $listDirn, $listOrder); ?>
               </th>
            </tr>
         </thead>
         <tbody>
            <?php
            foreach ($this->items as $i => $item) :
               $canEdit = $user->authorise('core.edit', 'com_jdbuilder');
               $canCheckin = $user->authorise('core.manage', 'com_jdbuilder');
               $canChange = $user->authorise('core.edit.state', 'com_jdbuilder');
            ?>
               <tr class="row<?php echo $i % 2; ?>">
                  <td class="text-center">
                     <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                  </td>
                  <td class="center">
                     <?php
                     $options = [
                        'task_prefix' => 'pages.',
                        'disabled' => !$canChange
                     ];

                     echo (new PublishedButton)->render((int) $item->state, $i, $options);
                     ?>
                  </td>
                  <td>
                     <?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'pages.', $canCheckin); ?>
                     <?php endif; ?>
                     <?php
                     $itemid = JDPageBuilder\Helper::getPageItemIdByLink('index.php?option=com_jdbuilder&view=page&id=' . $item->id);
                     $url = !empty($itemid) ? 'index.php?Itemid=' . $itemid : 'index.php?option=com_jdbuilder&view=page&id=' . $item->id;
                     ?>
                     <a target="_blank" href="<?php echo JDPageBuilder\Helper::JRouteLink('site', $url); ?>" title="Preview" target="_blank"><span class="fa fa-eye"></span></a>
                     <?php if ($canEdit) : ?>
                        <a class="ml-2" href="<?php echo Route::_('index.php?option=com_jdbuilder&task=page.edit&id=' . (int) $item->id); ?>">
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
                  <td class="small">
                     <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
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

      <?php echo $this->pagination->getListFooter(); ?>

      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <?php echo HTMLHelper::_('form.token'); ?>
      <div class="component-version-container">
         <span class="component-version"><?php echo Text::_('COM_JDBUILDER'); ?> <span>v<?php echo JDB_VERSION; ?></span><?php echo Text::_('JDBUILDER_VERSION_LABEL'); ?>| Developed with <span style="color: red">&hearts;</span> by <a href="https://www.joomdev.com" target="_blank">JoomDev</a></span>
         <div class="support-link">
            <a href="https://docs.joomdev.com/category/jd-builder/" target="_blank">Documentation</a> <span>|</span> <a href="https://www.joomdev.com/jd-builder/changelog" target="_blank">Changelog</a> <span>|</span> <a href="https://www.joomdev.com/forum/jd-builder" target="_blank">Forum</a> <span>|</span> <a href="https://www.youtube.com/playlist?list=PLv9TlpLcSZTAnfiT0x10HO5GGaTJhUB1K" target="_blank">Video Tutorials</a> <span>|</span> <a href="https://extensions.joomla.org/extension/jd-builder" target="_blank"><span class="icon-joomla"></span> Rate Us</a>
         </div>
      </div>
   </div>
</form>