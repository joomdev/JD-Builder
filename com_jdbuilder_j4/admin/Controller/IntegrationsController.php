<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomdev\Component\JDBuilder\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class IntegrationsController extends AdminController
{
    /**
     * Method to clone existing Pages
     *
     * @return void
     */
    public function articleToggle()
    {
        if (defined('JDB_PRO') && !JDB_PRO) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JDBUILDER_PRO_FEATURE_WARNING'), 'error');
            $this->setRedirect('index.php?option=com_jdbuilder&view=integrations');
            return;
        }
        try {
            $params = ComponentHelper::getParams('com_jdbuilder');
            $article_integration = $params->get('article_integration',  1);

            $article_integration = $article_integration ? 0 : 1;
            $params->set('article_integration', $article_integration);

            // Save the parameters
            $componentid = ComponentHelper::getComponent('com_jdbuilder')->id;
            $table = Table::getInstance('extension');
            $table->load($componentid);
            $table->bind(array('params' => $params->toString()));

            // check for error
            if (!$table->check()) {
                throw new \Exception($table->getError());
            }
            // Save to database
            if (!$table->store()) {
                throw new \Exception($table->getError());
            }

            $msg = 'COM_JDBUILDER_ARTICLE_INTEGRATION_' . ($article_integration ? 'ENABLED' : 'DISABLED');
            Factory::getApplication()->enqueueMessage(Text::_($msg), 'success');
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_jdbuilder&view=integrations');
    }

    public function faToggle()
    {
        if (defined('JDB_PRO') && !JDB_PRO) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_JDBUILDER_PRO_FEATURE_WARNING'), 'error');
            $this->setRedirect('index.php?option=com_jdbuilder&view=integrations');
            return;
        }
        try {
            $params = ComponentHelper::getParams('com_jdbuilder');
            $fontawesomepro_integration = $params->get('fontawesomepro_integration',  1);

            $fontawesomepro_integration = $fontawesomepro_integration ? 0 : 1;
            $params->set('fontawesomepro_integration', $fontawesomepro_integration);

            // Save the parameters
            $componentid = ComponentHelper::getComponent('com_jdbuilder')->id;
            $table = Table::getInstance('extension');
            $table->load($componentid);
            $table->bind(array('params' => $params->toString()));

            // check for error
            if (!$table->check()) {
                throw new \Exception($table->getError());
            }
            // Save to database
            if (!$table->store()) {
                throw new \Exception($table->getError());
            }

            $msg = 'COM_JDBUILDER_FA_INTEGRATION_' . ($fontawesomepro_integration ? 'ENABLED' : 'DISABLED');
            Factory::getApplication()->enqueueMessage(Text::_($msg), 'success');
        } catch (Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_jdbuilder&view=integrations');
    }
}
