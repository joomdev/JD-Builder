<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

// No direct access
defined('_JEXEC') or die('Restricted access');

class ElementStyle
{

    protected $styles = ['desktop' => [], 'mobile' => [], 'tablet' => []];
    protected $css = ['desktop' => [], 'mobile' => [], 'tablet' => []];
    public $selector;

    public function __construct($selector)
    {
        $this->selector = $selector;
    }

    public function addCss($property, $value, $device = "desktop")
    {
        if ($value === null || $value === "") {
            return;
        }
        if (is_string($value)) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $this->styles[$device][$property] = $value;
        } else if (is_numeric($value)) {
            $this->styles[$device][$property] = $value;
        }
    }

    public function addStyle($css, $device = "desktop")
    {
        if (empty($css)) {
            return;
        }
        $this->css[$device][] = $css;
    }

    public function render($output = false)
    {
        $scss = [];

        foreach (\JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            $scss[$device] = "";
        }

        foreach ($this->styles as $device => $styles) {
            if (!empty($styles)) {
                foreach ($styles as $property => $value) {
                    /* if ($device !== 'desktop' && isset($this->styles['desktop'][$property]) && $this->styles['desktop'][$property] == $value) {
                  continue;
               }
               if ($device == 'mobile' && isset($this->styles['tablet'][$property]) && $this->styles['tablet'][$property] == $value) {
                  continue;
               } */
                    $scss[$device] .= "{$property}:{$value};";
                }
            }
        }

        foreach ($this->css as $device => $cssScripts) {
            if (!empty($cssScripts)) {
                $cssscript = "";
                foreach ($cssScripts as $css) {
                    if (is_string($css)) {
                        $cssscript .= $css;
                        if (substr($cssscript, -1) != ";") {
                            $cssscript .= ';';
                        }
                    }
                }
                if (!empty($cssscript)) {
                    $scss[$device] .= $cssscript;
                }
            }
        }

        $inlineScss = [];
        foreach ($scss as $device => $script) {
            if ($script != '') {
                $inlineScss[$device] = $this->selector . ' {' . $script . '}';
            }
        }

        \JDPageBuilder\Builder::addStyle($inlineScss);

        /*
      foreach ($scss as $device => $cssscript) {
         if (!empty($cssscript)) {
            if ($device != "desktop") {
               if ($device == "tablet") {
                  $inlineScss .= '@media (min-width: 768px) and (max-width: 991.98px) {';
               } else {
                  $inlineScss .= '@media (max-width: 767.98px) {';
               }
            }
            $inlineScss .= $cssscript;
            if ($device != "desktop") {
               $inlineScss .= '}';
            }
         }
      }

      if (empty($inlineScss)) {
         return '';
      }

      $scss = $this->selector . " {" . $inlineScss . "}";
      if (!$output) {
         \JDPageBuilder\Builder::addStyle($scss);
      } else {
         return $scss;
      }
      */
    }
}
