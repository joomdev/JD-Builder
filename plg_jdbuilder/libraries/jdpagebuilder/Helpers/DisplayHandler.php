<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Helpers;

use DateTime;
use JDPageBuilder\Helper;
use Jenssegers\Agent\Agent;

// No direct access
defined('_JEXEC') or die('Restricted access');

class DisplayHandler
{
    protected $element = null;

    public function __construct(&$element)
    {
        $this->element  = $element;
    }

    public function check()
    {
        if (!$this->element->visibility) return;


        $params = $this->element->params;
        $match_conditions = $params->get('match_conditions', 'all');

        $statuses = [];

        $statuses[] = $this->checkMenuItems(); // menu items
        $statuses[] = $this->checkUrls(); // urls
        $statuses[] = $this->checkDate(); // date
        $statuses[] = $this->checkTime(); // time
        $statuses[] = $this->checkJoomlaUsers(); // usergroups
        $statuses[] = $this->checkPageview(); // pageviews
        $statuses[] = $this->checkStartTime(); // starttime
        $statuses[] = $this->checkDevices(); // devices
        $statuses[] = $this->checkBrowser(); // browser
        $statuses[] = $this->checkOS(); // operating systems
        $statuses[] = $this->checkReferrer(); // referrer
        $statuses[] = $this->checkIp(); // ip


        foreach ($statuses as $index => $status) {
            if ($status === null) unset($statuses[$index]);
        }

        if ($match_conditions == 'all' && in_array(false, $statuses)) {
            $this->element->visibility = false;
        }

        if ($match_conditions == 'any' && !in_array(true, $statuses)) {
            $this->element->visibility = false;
        }
    }

    public function checkMenuItems()
    {
        $params = $this->element->params;
        $menuitems_condition = $params->get('menuitems_condition', false);
        if ($menuitems_condition == 'ignore') return; // ignored

        $menuitems = $params->get('menuitems', []);

        $app = \JFactory::getApplication();
        $menuitem = $app->getMenu()->getActive()->id;

        if (empty($menuitems)) return true; // show if menuitems empty
        if ($menuitems_condition == 'include' && !in_array($menuitem, $menuitems)) return false; // hide if not in the list
        if ($menuitems_condition == 'exclude' && in_array($menuitem, $menuitems)) return false; // hide if in the list
        return true; // show default
    }

    public function checkUrls()
    {
        $params = $this->element->params;
        $urls_condition = $params->get('urls_condition', false);
        if ($urls_condition == 'ignore') return; // ignored

        $urls = $params->get('urls', '');
        $urls = explode("\n", $urls);
        $uri = \JUri::getInstance();
        $url = $uri->toString();

        if (empty($urls)) return true; // show if empty
        if ($urls_condition == 'include' && !in_array($url, $urls)) return false; // hide if not in the list
        if ($urls_condition == 'exclude' && in_array($url, $urls)) return false; // hide if in the list
        return true; // show default
    }

    public function checkDate()
    {
        $params = $this->element->params;
        $date_condition = $params->get('date_condition', false);
        if ($date_condition == 'ignore') return; // ignored

        $startdate = $params->get('startdate', '');
        $enddate = $params->get('enddate', '');

        if (empty($startdate) || empty($enddate)) return true; // show if empty

        $inrange = Helper::checkDateInRange($startdate, $enddate, date('Y-m-d'));
        if ($date_condition == 'include' && !$inrange) return false; // hide if not in range
        if ($date_condition == 'exclude' && $inrange) return false; // hide if in range

        return true; // show default
    }

    public function checkTime()
    {
        $params = $this->element->params;
        $time_condition = $params->get('time_condition', false);
        if ($time_condition == 'ignore') return; // ignored

        $starttime = $params->get('starttime', \json_decode('{}'));
        $endtime = $params->get('endtime', \json_decode('{}'));

        if (empty($starttime) || empty($endtime)) return true; // show if empty

        $timezone = \JFactory::getConfig()->get('offset');
        $date = date_create(null, new \DateTimeZone($timezone));


        $starttime = date('Y-m-d') . ' ' . str_pad($starttime->hours, 2,  "0", STR_PAD_LEFT) . ':' . str_pad($starttime->minutes, 2, "0", STR_PAD_LEFT) . ':00';
        $endtime = date('Y-m-d') . ' ' . str_pad($endtime->hours, 2,  "0", STR_PAD_LEFT) . ':' . str_pad($endtime->minutes, 2, "0", STR_PAD_LEFT) . ':00';
        $currenttime = date('Y-m-d') . ' ' . str_pad(date_format($date, 'H'), 2,  "0", STR_PAD_LEFT) . ':' . str_pad(date_format($date, 'i'), 2, "0", STR_PAD_LEFT) . ':00';

        $inrange = Helper::checkDateInRange($starttime, $endtime, $currenttime);
        if ($time_condition == 'include' && !$inrange) return false; // hide if not in range
        if ($time_condition == 'exclude' && $inrange) return false; // hide if in range

        return true; // show default
    }

    public function checkJoomlaUsers()
    {
        $params = $this->element->params;
        $access_condition = $params->get('access_condition', false); // show if ignored
        if ($access_condition == 'ignore') return; //ignored

        $usergroups = $params->get('usergroups', []);
        if (empty($usergroups)) return true; // show if empty

        $user = \JFactory::getUser();

        $groups = isset($user->groups) ? $user->groups : [];

        $inArray = false;
        foreach ($groups as $group) {
            if (in_array($group, $usergroups)) {
                $inArray = true;
                break;
            }
        }

        if ($access_condition == 'include' && !$inArray) return false; // hide if not in groups
        if ($access_condition == 'exclude' && $inArray) return false; // hide if in groups
        return true; // show default
    }

    public function checkPageview()
    {
        $params = $this->element->params;
        $pageview_condition = $params->get('pageview_condition', false); // show if ignored
        if ($pageview_condition == 'ignore') return; // ignored

        $pageview_count = $params->get('pageview_count', 0);
        if (empty($pageview_count)) return true; // show if empty

        $app = \JFactory::getApplication();
        $count = $app->input->cookie->get('jdb-page-view', 0);

        $pageview_match = $params->get('pageview_match', 'exact');
        $match = false;
        if ($pageview_match == 'exact' && $pageview_count == $count) {
            $match = true;
        } elseif ($pageview_match == 'fewer' && $pageview_count >= $count) {
            $match = true;
        } elseif ($pageview_match == 'more' && $pageview_count <= $count) {
            $match = true;
        }

        if ($pageview_condition == 'include' && !$match) return false; // hide if not match
        if ($pageview_condition == 'exclude' && $match) return false; // hide if match
        return true; // show default
    }

    public function checkStartTime()
    {
        $params = $this->element->params;
        $timeonsite_condition = $params->get('timeonsite_condition', false); // show if ignored
        if ($timeonsite_condition == 'ignore') return; // ignored

        $timeonsite = $params->get('timeonsite', 0);
        if (empty($timeonsite)) return true; // show if empty

        $session = \JFactory::getApplication()->getSession();
        $sessionStartTime = $session->get('jdstarttime');
        if (!$sessionStartTime) return false; // hide if no session

        $jdstarttime = $session->get('jdstarttime');
        $inrange = Helper::checkDateInRange($jdstarttime, date('Y-m-d H:i:s', strtotime($jdstarttime . ' +1 year')), date('Y-m-d H:i:s'));

        if ($timeonsite_condition == 'include' && !$inrange) return false; // hide if not in range
        if ($timeonsite_condition == 'exclude' && $inrange) return false; // hide if in range
        return true; // show default
    }

    public function checkDevices()
    {
        $params = $this->element->params;
        $target_condition = $params->get('target_condition', false);
        if ($target_condition == 'ignore') return; // ignored

        $devices = $params->get('targetdevices', []);
        if (empty($devices)) return true; // show if empty
        $agent = new Agent();
        $device = $agent->isDesktop() ? 'desktop' : ($agent->isTablet() ? 'tablet' : 'mobile');

        if ($target_condition == 'include' && !in_array($device, $devices)) return false; // hide if not in devices
        if ($target_condition == 'exclude' && in_array($device, $devices)) return false; // hide if in devices
        return true; // show default
    }

    public function checkBrowser()
    {
        $params = $this->element->params;
        $browser_condition = $params->get('browser_condition', false);
        if ($browser_condition == 'ignore') return; // ignored

        $browsers = $params->get('targetbrowsers', []);
        if (empty($browsers)) return true; // show if empty
        $agent = new Agent();
        $browser = strtolower($agent->browser());

        if ($browser_condition == 'include' && !in_array($browser, $browsers)) return false; // hide if not in selected browser
        if ($browser_condition == 'exclude' && in_array($browser, $browsers)) return false; // hide if in selected browser
        return true; // show default
    }

    public function checkOS()
    {
        $params = $this->element->params;
        $os_condition = $params->get('os_condition', false);
        if ($os_condition == 'ignore') return;  // ignored

        $operating_systems = $params->get('targetos', []);
        if (empty($operating_systems)) return true; // show if empty
        $agent = new Agent();
        $os = strtolower($agent->platform());

        if ($os_condition == 'include' && !in_array($os, $operating_systems)) return false; // hide if not in selected os
        if ($os_condition == 'exclude' && in_array($os, $operating_systems)) return false; // hide if in selected os
        return true; // show default
    }

    public function checkReferrer()
    {
        $params = $this->element->params;
        $referrer_condition = $params->get('referrer_condition', false);
        if ($referrer_condition == 'ignore') return; // ignored

        $referrers = $params->get('referrers', '');
        $referrers = explode("\n", $referrers);
        if (empty($referrers)) return true; // show if empty


        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (empty($referrer)) return true; // show if no referrer

        if ($referrer_condition == 'include' && !in_array($referrer, $referrers)) return false; // hide if not in the list
        if ($referrer_condition == 'exclude' && in_array($referrer, $referrers)) return false; // hide if in the list
        return true; // show default
    }

    public function checkIp()
    {
        $params = $this->element->params;
        $ip_condition = $params->get('ip_condition', false);
        if ($ip_condition == 'ignore') return; // ignored

        $ipList = $params->get('ips', '');
        $ipList = explode("\n", $ipList);
        if (empty($ipList)) return true; // show if empty

        $ip = Helper::getClientIp();

        if (empty($ip)) return true; // show if empty

        if ($ip_condition == 'include' && !in_array($ip, $ipList)) return false; // hide if not in the list
        if ($ip_condition == 'exclude' && in_array($ip, $ipList)) return false; // hide if in the list
        return true; // show default
    }
}
