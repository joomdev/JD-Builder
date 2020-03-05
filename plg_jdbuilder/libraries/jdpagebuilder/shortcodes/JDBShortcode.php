<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\JDBShortcode;

// No direct access
defined('_JEXEC') or die;

class JDBShortcode {

   public $tag = "";

   final public function hasShortcode($content) {
      if (false === strpos($content, '[')) {
         return false;
      }
      preg_match_all('/' . $this->getShortcodeRegex() . '/', $content, $matches, PREG_SET_ORDER);
      if (empty($matches)) {
         return false;
      }
      foreach ($matches as $shortcode) {
         if ($this->tag === $shortcode[2]) {
            return true;
         } elseif (!empty($shortcode[5]) && $this->hasShortcode($shortcode[5], $this->tag)) {
            return true;
         }
      }
      return false;
   }

   final public function getShortcodeRegex() {
      $tagregexp = join('|', array_map('preg_quote', [$this->tag]));
      return
              '\\['
              . '(\\[?)'
              . "($tagregexp)"
              . '(?![\\w-])'
              . '('
              . '[^\\]\\/]*'
              . '(?:'
              . '\\/(?!\\])'
              . '[^\\]\\/]*'
              . ')*?'
              . ')'
              . '(?:'
              . '(\\/)'
              . '\\]'
              . '|'
              . '\\]'
              . '(?:'
              . '('
              . '[^\\[]*+'
              . '(?:'
              . '\\[(?!\\/\\2\\])'
              . '[^\\[]*+'
              . ')*+'
              . ')'
              . '\\[\\/\\2\\]'
              . ')?'
              . ')'
              . '(\\]?)';
   }

   final public function render($content, $ignore_html = false) {
      if (!$this->hasShortcode($content)) {
         return $content;
      }

      $content = $this->applyShortcode($content);
      $pattern = $this->getShortcodeRegex();
      $content = preg_replace_callback("/$pattern/", [&$this, 'doShortcodeTag'], $content);
      return $content;
   }

   final public function applyShortcode($content, $ignore_html = false) {
      $trans = array(
          '&#91;' => '&#091;',
          '&#93;' => '&#093;',
      );
      $content = strtr($content, $trans);
      $trans = array(
          '[' => '&#91;',
          ']' => '&#93;',
      );
      $pattern = $this->getShortcodeRegex();
      $textarr = preg_split($this->getHtmlSplitRegex(), $content, -1, PREG_SPLIT_DELIM_CAPTURE);

      foreach ($textarr as &$element) {
         if ('' == $element || '<' !== $element[0]) {
            continue;
         }
         $noopen = false === strpos($element, '[');
         $noclose = false === strpos($element, ']');
         if ($noopen || $noclose) {
            if ($noopen xor $noclose) {
               $element = strtr($element, $trans);
            }
            continue;
         }
         if ($ignore_html || '<!--' === substr($element, 0, 4) || '<![CDATA[' === substr($element, 0, 9)) {
            $element = strtr($element, $trans);
            continue;
         }
         $attributes = $this->attrParse($element);

         if (false === $attributes) {
            // Some plugins are doing things like [name] <[email]>.
            if (1 === preg_match('%^<\s*\[\[?[^\[\]]+\]%', $element)) {
               $element = preg_replace_callback("/$pattern/", [&$this, 'doShortcodeTag'], $element);
            }
            $element = strtr($element, $trans);
            continue;
         }
         $front = array_shift($attributes);
         $back = array_pop($attributes);
         $matches = array();
         preg_match('%[a-zA-Z0-9]+%', $front, $matches);
         $elname = $matches[0];
         foreach ($attributes as &$attr) {
            $open = strpos($attr, '[');
            $close = strpos($attr, ']');
            if (false === $open || false === $close) {
               continue;
            }
            $double = strpos($attr, '"');
            $single = strpos($attr, "'");
            if (( false === $single || $open < $single ) && ( false === $double || $open < $double )) {
               $attr = preg_replace_callback("/$pattern/", [&$this, 'doShortcodeTag'], $attr);
            } else {
               $count = 0;
               $new_attr = preg_replace_callback("/$pattern/", [&$this, 'doShortcodeTag'], $attr, -1, $count);
               if ($count > 0) {
                  if ('' !== trim($new_attr)) {
                     $attr = $new_attr;
                  }
               }
            }
         }
         $element = $front . implode('', $attributes) . $back;
         $element = strtr($element, $trans);
      }
      $content = implode('', $textarr);
      return $content;
   }

   final public function getHtmlSplitRegex() {

      $comments = '!'
              . '(?:'
              . '-(?!->)'
              . '[^\-]*+'
              . ')*+'
              . '(?:-->)?';

      $cdata = '!\[CDATA\['
              . '[^\]]*+'
              . '(?:'
              . '](?!]>)'
              . '[^\]]*+'
              . ')*+'
              . '(?:]]>)?';

      $escaped = '(?='
              . '!--'
              . '|'
              . '!\[CDATA\['
              . ')'
              . '(?(?=!-)'
              . $comments
              . '|'
              . $cdata
              . ')';

      $regex = '/('
              . '<'
              . '(?'
              . $escaped
              . '|'
              . '[^>]*>?'
              . ')'
              . ')/';

      return $regex;
   }

   final public function attrParse($element) {
      $valid = preg_match('%^(<\s*)(/\s*)?([a-zA-Z0-9]+\s*)([^>]*)(>?)$%', $element, $matches);

      if (1 !== $valid) {
         return false;
      }

      $begin = $matches[1];
      $slash = $matches[2];
      $elname = $matches[3];
      $attr = $matches[4];
      $end = $matches[5];

      if ('' !== $slash) {
         return false;
      }

      if (1 === preg_match('%\s*/\s*$%', $attr, $matches)) {
         $xhtml_slash = $matches[0];
         $attr = substr($attr, 0, -strlen($xhtml_slash));
      } else {
         $xhtml_slash = '';
      }

      $attrarr = $this->hairParse($attr);
      if (false === $attrarr) {
         return false;
      }

      array_unshift($attrarr, $begin . $slash . $elname);
      array_push($attrarr, $xhtml_slash . $end);

      return $attrarr;
   }

   final public function hairParse($attr) {
      if ('' === $attr) {
         return array();
      }

      $regex = '(?:'
              . '[-a-zA-Z:]+'
              . '|'
              . '\[\[?[^\[\]]+\]\]?'
              . ')'
              . '(?:'
              . '\s*=\s*'
              . '(?:'
              . '"[^"]*"'
              . '|'
              . "'[^']*'"
              . '|'
              . '[^\s"\']+'
              . '(?:\s|$)'
              . ')'
              . '|'
              . '(?:\s|$)'
              . ')'
              . '\s*';

      $validation = "%^($regex)+$%";
      $extraction = "%$regex%";

      if (1 === preg_match($validation, $attr)) {
         preg_match_all($extraction, $attr, $attrarr);
         return $attrarr[0];
      } else {
         return false;
      }
   }

   final public function parseAtts($text) {
      $atts = array();
      $pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
      $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
      if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
         foreach ($match as $m) {
            if (!empty($m[1])) {
               $atts[strtolower($m[1])] = stripcslashes($m[2]);
            } elseif (!empty($m[3])) {
               $atts[strtolower($m[3])] = stripcslashes($m[4]);
            } elseif (!empty($m[5])) {
               $atts[strtolower($m[5])] = stripcslashes($m[6]);
            } elseif (isset($m[7]) && strlen($m[7])) {
               $atts[] = stripcslashes($m[7]);
            } elseif (isset($m[8]) && strlen($m[8])) {
               $atts[] = stripcslashes($m[8]);
            } elseif (isset($m[9])) {
               $atts[] = stripcslashes($m[9]);
            }
         }
         foreach ($atts as &$value) {
            if (false !== strpos($value, '<')) {
               if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                  $value = '';
               }
            }
         }
      } else {
         $atts = ltrim($text);
      }
      return $atts;
   }

   final public function doShortcodeTag($m) {
      if ($m[1] == '[' && $m[6] == ']') {
         return substr($m[0], 1, -1);
      }
      $tag = $m[2];
      $attr = $this->parseAtts($m[3]);
      $content = isset($m[5]) ? $m[5] : null;
      $output = $m[1] . $this->output($content, $attr) . $m[6];
      return $output;
   }

   public function output($content, $attr) {
      return $content;
   }

}
