<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Pages list controller class.
 *
 * @since  1.6
 */
class JdbuilderControllerIntegrations extends JControllerAdmin
{

    /**
     * Method to clone existing Pages
     *
     * @return void
     */
    public function articleToggle()
    {
        // Jsession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        if (defined('JDB_PRO') && !JDB_PRO) {
            JFactory::getApplication()->enqueueMessage(\JText::_('COM_JDBUILDER_PRO_FEATURE_WARNING'), 'error');
            $this->setRedirect('index.php?option=com_jdbuilder&view=integrations');
            return;
        }
        try {
            $params = JComponentHelper::getParams('com_jdbuilder');
            $article_integration = $params->get('article_integration',  1);

            $article_integration = $article_integration ? 0 : 1;
            $params->set('article_integration', $article_integration);

            // Save the parameters
            $componentid = JComponentHelper::getComponent('com_jdbuilder')->id;
            $table = JTable::getInstance('extension');
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
            JFactory::getApplication()->enqueueMessage(\JText::_($msg), 'success');
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_jdbuilder&view=integrations');
    }
    
    public function faToggle()
    {
        // Jsession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        if (defined('JDB_PRO') && !JDB_PRO) {
            JFactory::getApplication()->enqueueMessage(\JText::_('COM_JDBUILDER_PRO_FEATURE_WARNING'), 'error');
            $this->setRedirect('index.php?option=com_jdbuilder&view=integrations');
            return;
        }
        try {
            $params = JComponentHelper::getParams('com_jdbuilder');
            $fontawesomepro_integration = 0;

            $fontawesomepro_integration = $fontawesomepro_integration ? 0 : 1;
            $params->set('fontawesomepro_integration', $fontawesomepro_integration);

            // Save the parameters
            $componentid = JComponentHelper::getComponent('com_jdbuilder')->id;
            $table = JTable::getInstance('extension');
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
            JFactory::getApplication()->enqueueMessage(\JText::_($msg), 'success');
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_jdbuilder&view=integrations');
    }
}
