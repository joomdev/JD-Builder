<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

// No direct access
defined('_JEXEC') or die('Restricted access');

class ElementHelper
{
    public $params, $id, $type;

    public function __construct()
    {
        $this->params = new \JRegistry();

        $app = \JFactory::getApplication();
        $ajaxID = $app->input->get('ajaxID', '', 'RAW');
        $element = Helper::getElementByAjaxID($ajaxID);
        $this->id = $element->id;
        $this->type = $element->type;
        $this->params = $element->params;
        Helper::loadLanguage($this->type, JDBPATH_ELEMENTS . '/' . $this->type);
    }
}
