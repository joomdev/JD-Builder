<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load tooltip behavior
JHtml::_('behavior.tooltip');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$function = JFactory::getApplication()->input->getCmd('function', 'jSelectBook');
?>
<div class="container-popup">
<form action="<?php echo JRoute::_('index.php?option=com_jdbuilder&view=pages&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm">

   <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

   <div class="clearfix"></div>
   <table class="adminlist table table-striped table-hover">
   <thead>
					<tr>
						<th width="1%" class="center nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
                  <th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                  </th>
             
						<th width="15%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
						</th>
						<!-- <th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
						</th> -->
						<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
      <tfoot>
         <tr>
            <td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
         </tr>
      </tfoot>
      <tbody>
         <?php foreach ($this->items as $i => $item) : ?>
         <?php if ($item->language && JLanguageMultilang::isEnabled())
					{
						$tag = strlen($item->language);
						if ($tag == 5)
						{
							$lang = substr($item->language, 0, 2);
						}
						elseif ($tag == 6)
						{
							$lang = substr($item->language, 0, 3);
						}
						else {
							$lang = '';
						}
					}
					elseif (!JLanguageMultilang::isEnabled())
					{
						$lang = '';
					}
					?>
            <tr class="row<?php echo $i % 2; ?>">
               <td align="center"><?php echo JHtml::_('jgrid.published', $item->state, $i, 'pages.'); ?></td>
               <td>
                  <a style="cursor: pointer;" class="pointer" onclick="if (window.parent)
									window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>');"><?php echo $this->escape($item->title); ?></a>
							<div class="small">
								<?php echo JText::_('JDB_CATEGORY') . ': ' . $this->escape($item->category_id); ?>
							</div>
               </td>
               <td align="center"><?php echo $item->access; ?></td>
               <td align="center"><?php echo $item->language; ?></td>
               <!-- <td class="nowrap small hidden-phone">
							<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
						</td> -->
               <td><?php echo $item->id; ?></td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
   <div>
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
      <?php echo JHtml::_('form.token'); ?>
   </div>
</form>
</div>