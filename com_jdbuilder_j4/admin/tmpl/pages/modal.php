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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$app = Factory::getApplication();

if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(JUri::root() . 'administrator/components/com_jdbuilder/assets/css/jdbuilder.css');
$document->addStyleSheet(JUri::root() . 'media/com_jdbuilder/css/list.css');

$user = Factory::getUser();
$userId = $user->get('id');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$function  = $app->input->getCmd('function', 'jSelectPage');
$editor    = $app->input->getCmd('editor', '');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);

if (!empty($editor)) {
    // This view is used also in com_menus. Load the xtd script only if the editor is set!
    $this->document->addScriptOptions('xtd-pages', array('editor' => $editor));
    $onclick = "jSelectPage";
}
?>
<div class="container-popup">
    <form action="<?php echo Route::_('index.php?option=com_jdbuilder&view=pages' . '&function=' . $function . '&' . Session::getFormToken() . '=1&editor=' . $editor); ?>" method="post" name="adminForm" id="adminForm">
        <div id="j-main-container" class="j-main-container">
            <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
            <table class="table" id="pageList">
                <thead>
                    <tr>
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
                    $iconStates = array(
                        -2 => 'icon-trash',
                        0  => 'icon-unpublish',
                        1  => 'icon-publish',
                    );
                    ?>
                    <?php
                    foreach ($this->items as $i => $item) :
                        $canEdit = $user->authorise('core.edit', 'com_jdbuilder');
                        $canCheckin = $user->authorise('core.manage', 'com_jdbuilder');
                        $canChange = $user->authorise('core.edit.state', 'com_jdbuilder');
                    ?>
                        <?php if ($item->language && Multilanguage::isEnabled()) {
                            $tag = strlen($item->language);
                            if ($tag == 5) {
                                $lang = substr($item->language, 0, 2);
                            } elseif ($tag == 6) {
                                $lang = substr($item->language, 0, 3);
                            } else {
                                $lang = '';
                            }
                        } elseif (!Multilanguage::isEnabled()) {
                            $lang = '';
                        }
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center tbody-icon">
                                <span class="<?php echo $iconStates[$this->escape($item->state)]; ?>" aria-hidden="true"></span>
                            </td>
                            <td>
                                <a onclick="if (window.parent)
									window.parent.<?php echo $this->escape($onclick); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>');" href="javascript:void(0)">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
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
        </div>
    </form>
</div>