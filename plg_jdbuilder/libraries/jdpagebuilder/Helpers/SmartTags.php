<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Helpers;

use JDPageBuilder\Helper;
use Jenssegers\Agent\Agent;

// No direct access
defined('_JEXEC') or die('Restricted access');

class SmartTags
{
    protected static $values = [];
    public static function apply($string = '', $values = [])
    {
        if (!is_string($string)) {
            return $string;
        }
        self::$values = $values;
        $string = preg_replace_callback('/{(.*?)}/', function ($matches) {
            $html = self::render($matches[1], $matches[0]);
            if (!is_string($html)) {
                $html = \json_encode($html);
            }
            return $html;
        }, $string);
        return $string;
    }

    private static function render($tag = '', $default = '')
    {
        $tag = explode('.', $tag);
        $type = isset($tag[0]) && !empty($tag[0]) ? $tag[0] : '';
        $subtype = isset($tag[1]) && !empty($tag[1]) ? $tag[1] : '';
        $func = strtolower($type) . 'Tags';
        if (method_exists(self::class, $func)) {
            $output = self::$func(strtolower($subtype));
            if ($output !== null) {
                return $output;
            }
        } else {
            $output = self::otherTags(strtolower($type), $subtype, $tag);
            if ($output !== null) {
                return $output;
            }
        }
        return $default;
    }

    protected static function pageTags($type)
    {
        $return = null;
        $document = \JFactory::getDocument();
        $uri = \JUri::getInstance();

        switch ($type) {
            /* case 'title':
                $return = $document->getTitle(); // to be fix
                break; */
            case 'url':
                $return = $uri->toString();
                break;
            case 'path':
                $return = $uri->getPath();
                break;
            case 'lang':
                $return = $document->getLanguage();
                break;
        }
        return $return;
    }

    protected static function systemTags($type)
    {
        $return = null;
        switch ($type) {
            case 'date':
                $return = date('Y-m-d');
                break;
            case 'time':
                $return = date('H:i:s');
                break;
            case 'timestamp':
                $return = date('Y-m-d H:i:s');
                break;
        }
        return $return;
    }

    protected static function siteTags($type)
    {
        $return = null;
        $config = \JFactory::getConfig();
        $uri = \JUri::getInstance();
        switch ($type) {
            case 'name':
                $return = $config->get('sitename', '');
                break;
            case 'url':
                $return = $uri->base();
                break;
            case 'host':
                $return = $uri->getHost();
                break;
            case 'email':
                $return = $config->get('mailfrom', '');
                break;
        }
        return $return;
    }

    protected static function clientTags($type)
    {
        $return = null;
        $agent = new Agent();
        $uri = \JUri::getInstance();
        switch ($type) {
            /* case 'device':
                $return = $agent->device(); // to be check
                break; */
            case 'ip':
                $return = Helper::getClientIp();
                break;
            case 'browser':
                $return = $agent->browser();
                break;
            case 'browser_version':
                $return = $agent->version($agent->browser());
                break;
            case 'os':
                $return = $agent->platform();
                break;
            case 'os_version':
                $return = $agent->version($agent->platform());
                break;
            case 'useragent':
                $return = $_SERVER['HTTP_USER_AGENT'];
                break;
        }
        return $return;
    }

    protected static function userTags($type)
    {
        $return = null;
        $user = \JFactory::getUser();
        switch ($type) {
            case 'id':
                $return = $user->id;
                break;
            case 'username':
                if ($user->id) {
                    $return = $user->username;
                } else {
                    $return = '';
                }
                break;
            case 'email':
                if ($user->id) {
                    $return = $user->email;
                } else {
                    $return = '';
                }
                break;
            case 'name':
                if ($user->id) {
                    $return = $user->name;
                } else {
                    $return = '';
                }
                break;
            case 'groups':
                $return = self::getUserGroups($user->groups);
                break;
        }
        return $return;
    }

    public static function getUserGroups($groupsIds = [])
    {
        if (empty($groupsIds)) {
            return 'Guest';
        }
        $db = \JFactory::getDbo();
        $db->setQuery("SELECT `title` FROM `#__usergroups` WHERE `id` IN (" . implode(",", array_values($groupsIds)) . ")");
        return implode(", ", array_map(function ($o) {
            return $o->title;
        }, $db->loadObjectList()));
    }

    public static function otherTags($type, $subtype = null, $types = [])
    {
        $return = null;
        $app = \JFactory::getApplication()->input->get;
        switch ($type) {
            case 'referrer':
                $return = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                break;
            case 'randomid':
                $return = uniqid();
                break;
            case 'querystring':
                $uri = \JUri::getInstance($_SERVER['HTTP_REFERER']);
                $query = $uri->getQuery(true);
                $return = @$query[$subtype];
                break;
            default:
                if (isset(self::$values[$type])) {
                    if ($subtype !== null && isset(self::$values[$type][$subtype])) {
                        $return = self::$values[$type][$subtype];
                    } else {
                        $return = self::$values[$type];
                    }
                }
                break;
        }

        return $return;
    }
}
