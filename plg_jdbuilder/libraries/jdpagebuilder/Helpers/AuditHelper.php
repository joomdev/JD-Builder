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

class AuditHelper
{
    public static function getJDBModules()
    {
        $db = \JFactory::getDbo();
        $query = "SELECT `id`, `params` FROM `#__modules` WHERE `module`='mod_jdbuilder'";
        $db->setQuery($query);
        $modules = $db->loadObjectList();
        if (empty($modules)) {
            return [];
        }
        $modulesIds = array_column($modules, 'id');

        $query = "SELECT `status`, `itemid` FROM `#__jdbuilder_audit` WHERE `itemid` IN (" . implode(',', $modulesIds) . ") AND `itemtype`='module'";
        $db->setQuery($query);
        $statuses = $db->loadAssocList('itemid', 'status');

        foreach ($modules as &$module) {
            $module->audit_status = isset($statuses[$module->id]) ? $statuses[$module->id] : 0;
        }

        return $modules;
    }

    public static function auditModules()
    {
        $db = \JFactory::getDbo();
        $JDBModules = self::getJDBModules();
        $layouts = [];
        $modules = [];
        foreach ($JDBModules as $module) {
            $modules[$module->id] = $module;
            $moduleParams = \json_decode($module->params, true);
            $layoutId = @$moduleParams['jdbuilder_layout'];
            if (!empty($layoutId)) {
                if (!isset($layouts[$layoutId])) {
                    $layouts[$layoutId] = [];
                }
                $layouts[$layoutId][] = $module->id;
            }
        }

        $checkedModules = [];
        foreach ($layouts as $layoutId => $modulesID) {
            if (count($modulesID) > 1) {
                sort($modulesID);
                foreach ($modulesID as $index => $moduleID) {
                    if ($index !== 0 && empty($modules[$moduleID]->audit_status)) {
                        self::fixModule($moduleID, $layoutId, $modules[$moduleID]->params);
                    } else if (empty($modules[$moduleID]->audit_status)) {
                        $checkedModules[] = $moduleID;
                    }
                }
            } else {
                if (empty($modules[$modulesID[0]]->audit_status)) {
                    $checkedModules[] = $modulesID[0];
                }
            }
        }

        $values = [];
        $time = time();
        foreach ($checkedModules as $mid) {
            $values[] = "(null, {$mid}, 'module', 1, '{}', {$time}, {$time})";
        }
        if (!empty($values)) {
            $values = implode(', ', $values);
            $query = "INSERT INTO `#__jdbuilder_audit` (id, itemid, itemtype, status, data, created, updated) VALUES {$values}";
            $db->setQuery($query);
            $db->query();
        }
    }

    public static function fixModule($moduleId, $layoutId = 0, $moduleParams = null)
    {
        $params = new \JRegistry();
        if (!empty($moduleParams)) {
            $params->loadObject(\json_decode($moduleParams));
        }

        $db = \JFactory::getDbo();
        $query = "SELECT `layout` FROM `#__jdbuilder_layouts` WHERE `id`='{$layoutId}'";
        $db->setQuery($query);
        $result = $db->loadObject();

        $layout = \json_decode($result->layout, true);
        $layout['type'] = 'layout';
        LayoutHelper::removeID($layout);
        LayoutHelper::refreshObjectID($layout, 'layout', 38);

        $object = new \stdClass();
        $object->id = NULL;
        $object->layout = \json_encode($layout);
        $object->created = time();
        $object->updated = time();
        $db->insertObject('#__jdbuilder_layouts', $object);
        $lid = $db->insertid();
        $params->set('jdbuilder_layout', $lid);

        $object = new \stdClass();
        $object->id = $moduleId;
        $object->params = \json_encode($params->toObject());
        $db->updateObject('#__modules', $object, 'id');

        return true;
    }

    public static function getJDBArticles($onlyActive = false)
    {
        $db = \JFactory::getDbo();
        if ($onlyActive) {
            $string = '"jdbuilder_layout_enabled":"1"';
        } else {
            $string = '"jdbuilder_layout_enabled"';
        }
        $db->setQuery("SELECT `id` FROM `#__content` WHERE `attribs` LIKE '%{$string}%'");
        return $db->loadObjectList();
    }

    public static function getJDBArticlesForAudit($onlyActive = false)
    {
        $db = \JFactory::getDbo();
        $string = '"jdbuilder_layout_enabled"';
        $query = "SELECT `id`, `attribs` FROM `#__content` WHERE `attribs` LIKE '%{$string}%'";
        $db->setQuery($query);
        $articles = $db->loadObjectList();
        if (empty($articles)) {
            return [];
        }
        $articlesIds = array_column($articles, 'id');

        $query = "SELECT `status`, `itemid` FROM `#__jdbuilder_audit` WHERE `itemid` IN (" . implode(',', $articlesIds) . ") AND `itemtype`='article'";
        $db->setQuery($query);
        $statuses = $db->loadAssocList('itemid', 'status');

        foreach ($articles as &$article) {
            $article->audit_status = isset($statuses[$article->id]) ? $statuses[$article->id] : 0;
        }

        return $articles;
    }

    public static function auditArticles()
    {
        $db = \JFactory::getDbo();
        $JDBArticles = self::getJDBArticlesForAudit();

        $layouts = [];
        $articles = [];
        foreach ($JDBArticles as $article) {
            $articles[$article->id] = $article;
            $articleParams = \json_decode($article->attribs, true);
            $layoutId = @$articleParams['jdbuilder_layout_id'];
            if (!empty($layoutId)) {
                if (!isset($layouts[$layoutId])) {
                    $layouts[$layoutId] = [];
                }
                $layouts[$layoutId][] = $article->id;
            }
        }

        $checkedArticles = [];
        foreach ($layouts as $layoutId => $articlesID) {
            if (count($articlesID) > 1) {
                sort($articlesID);
                foreach ($articlesID as $index => $articleID) {
                    if ($index !== 0 && empty($articles[$articleID]->audit_status)) {
                        self::fixArticle($articleID, $layoutId, $articles[$articleID]->attribs);
                    } else if (empty($articles[$articleID]->audit_status)) {
                        $checkedArticles[] = $articleID;
                    }
                }
            } else {
                if (empty($articles[$articlesID[0]]->audit_status)) {
                    $checkedArticles[] = $articlesID[0];
                }
            }
        }

        $values = [];
        $time = time();
        foreach ($checkedArticles as $mid) {
            $values[] = "(null, {$mid}, 'article', 1, '{}', {$time}, {$time})";
        }
        if (!empty($values)) {
            $values = implode(', ', $values);
            $query = "INSERT INTO `#__jdbuilder_audit` (id, itemid, itemtype, status, data, created, updated) VALUES {$values}";
            $db->setQuery($query);
            $db->query();
        }
    }

    public static function fixArticle($articleID, $layoutId = 0, $articleParams = null)
    {
        $params = new \JRegistry();
        if (!empty($articleParams)) {
            $params->loadObject(\json_decode($articleParams));
        }

        $db = \JFactory::getDbo();
        $query = "SELECT `layout` FROM `#__jdbuilder_layouts` WHERE `id`='{$layoutId}'";
        $db->setQuery($query);
        $result = $db->loadObject();

        $layout = \json_decode($result->layout, true);
        $layout['type'] = 'layout';
        LayoutHelper::removeID($layout);
        LayoutHelper::refreshObjectID($layout, 'layout', 38);

        $object = new \stdClass();
        $object->id = NULL;
        $object->layout = \json_encode($layout);
        $object->created = time();
        $object->updated = time();
        $db->insertObject('#__jdbuilder_layouts', $object);
        $lid = $db->insertid();
        $params->set('jdbuilder_layout_id', $lid);

        $object = new \stdClass();
        $object->id = $articleID;
        $object->attribs = \json_encode($params->toObject());
        $db->updateObject('#__content', $object, 'id');

        return true;
    }
}
