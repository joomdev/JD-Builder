<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

// No direct access
defined('_JEXEC') or die('Restricted access');

class ElementHelper
{
    public $params;

    public function __construct()
    {
        $this->params = new \JRegistry();

        $app = \JFactory::getApplication();
        $elementId = $app->input->get('eid', '', 'RAW');
        $this->setParams($elementId);
    }

    public function setParams($elementId)
    {
        if (!empty($elementId)) {
            $params = Helper::getElementParams($elementId);
            if ($params !== null) {
                $this->params = $params;
            }
        }
    }
}
