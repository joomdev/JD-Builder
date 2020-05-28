<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Helpers;

// No direct access
defined('_JEXEC') or die('Restricted access');

abstract class ModalHelper
{
    protected static $selectArticle = null;
    protected static $editArticle = null;
    protected static $newArticle = null;

    public static function selectArticleModal()
    {
        if (self::$selectArticle === null) {
            \JFactory::getDocument()->addScriptDeclaration("function JDBOnSelectArticle(id, title) { var event = new CustomEvent('onJDBArticleSelect', { detail: { id: id, title: title } }); parent.dispatchEvent(event); }");
            $selectArticleUrl = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;' . \JSession::getFormToken() . '=1&amp;function=JDBOnSelectArticle';
            self::$selectArticle = \JHtml::_(
                'bootstrap.renderModal',
                'JDBSelectArticleModal',
                array(
                    'title'       => 'Select or Change article',
                    'url'         => $selectArticleUrl,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => '70',
                    'modalWidth'  => '80',
                    'footer'      => '<button type="button" class="btn" data-dismiss="modal">Close</button>',
                )
            );
        }
        return self::$selectArticle;
    }
}
