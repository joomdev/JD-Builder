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

class DataHelper
{
    public static function icons()
    {
        $data = file_get_contents(JDBPATH_MEDIA . '/data/icons.json');
        return \json_decode($data);
    }

    public static function updateIcons()
    {
        $icon_libs = ['fontawesome', 'typicon', 'foundation'];
        $json = [];
        self::updateFontAwesome();
        foreach ($icon_libs as $icon_lib) {
            $data = file_get_contents(JDBPATH_MEDIA . '/data/icons/' . $icon_lib . '.json');
            $json[] = \json_decode($data);
        }
        file_put_contents(JDBPATH_MEDIA . '/data/icons.json', \json_encode($json));
        // update icons in repository
        return "Updated";
    }

    private static function updateFontAwesome()
    {
        $icons = \json_decode(file_get_contents(JDBURL_MEDIA . '/vendor/fontawesome/metadata/icons.json'), true);

        $faicons = [];
        foreach ($icons as $class => $icon) {
            if (isset($icon['free']) && !empty($icon['free'])) {
                foreach ($icon['free'] as $cat) {
                    $faicons[] = ['label' => $icon['label'], 'class' => 'fa' . substr($cat, 0, 1) . ' fa-' . $class];
                }
            }
        }

        $json = ['title' => 'Font Awesome', 'version' => \JDPageBuilder\Constants::FONTAWESOME_VERSION, 'total' => count($faicons), 'icons' => $faicons];

        file_put_contents(JDBPATH_MEDIA . '/data/icons/fontawesome.json', \json_encode($json));
    }

    public static function webfonts()
    {
        $data = file_get_contents(JDBPATH_MEDIA . '/data/webfonts.json');
        return \json_decode($data);
    }
}
