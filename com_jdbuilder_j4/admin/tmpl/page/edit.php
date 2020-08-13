<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomdev\Component\JDBuilder\Administrator\Helper\JdbuilderHelper;

HTMLHelper::_('behavior.keepalive');

$document = Factory::getDocument();
$script = "Joomla.submitbutton = function(task, formSelector, validate) {
    if (task == 'page.cancel') {
       Joomla.submitform(task);
    } else {
       if (document.getElementById('jform_title').value == '') {
          document.getElementById('jform_title').focus();
          return false;
       }
       Joomla.submitform(task);
    }
}";

$document->addScriptDeclaration($script);
?>
<script id="jdb-form-page-permissions-template" type="text/x-lodash-template">
    <?php if (JFactory::getUser()->authorise('core.admin', 'jdbuilder')) : ?>
   <?php echo $this->form->getInput('rules'); ?>
<?php endif; ?>
</script>
<script>
    <?php
    $item = ["id" => $this->item->id, "title" => empty($this->item->title) ? '' : $this->item->title, "layout_id" => empty($this->item->layout_id) ? 0 : $this->item->layout_id, "type" => "page", "params" => $this->item->params];
    ?>
    var _JDB = {};
    _JDB.ITEM = <?php echo \json_encode($item); ?>;
    _JDB.FORM_ID = 'adminForm';
    _JDB.ACCESS = <?php echo \json_encode($this->accessibility); ?>;
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jdbuilder&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm">
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="jform[layout_id]" value="<?php echo $this->item->layout_id; ?>" />
    <?php echo $this->form->renderField('created_by'); ?>
    <?php echo $this->form->renderField('modified_by'); ?>
    <div>
        {jdbuilder}
    </div>
</form>