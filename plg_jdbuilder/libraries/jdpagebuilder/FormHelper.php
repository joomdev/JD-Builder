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

class FormHelper
{

   public static function sortByOrdering($a, $b)
   {
      $a_ordering = isset($a['ordering']) ? $a['ordering'] : 0;
      $b_ordering = isset($b['ordering']) ? $b['ordering'] : 0;

      if ($a_ordering == $b_ordering) {
         return 0;
      }

      if ($a_ordering != 0 && $b_ordering != 0) {
         return (($a_ordering < $b_ordering) ? -1 : 1);
      }

      if ($a_ordering == 0) {
         return 1;
      }

      if ($b_ordering == 0) {
         return -1;
      }
   }

   public static function displayExpression($expression = '')
   {
      $expression = str_replace(' [OR] ', ' || ', $expression);
      $expression = str_replace('[OR]', ' || ', $expression);
      $expression = str_replace(' [AND] ', ' && ', $expression);
      $expression = str_replace('[AND]', ' && ', $expression);
      $expression = str_replace('params.', '_this.params.', $expression);
      return $expression;
   }

   public static function getSpacingValue($object = null, $property = "")
   {
      if (empty($object)) {
         return [];
      }
      $property = empty($property) ? '' : $property . '-';
      $unit = !empty($object->unit) ? $object->unit : 'px';
      $return = [];

      if ($object->lock == 1 && $object->top != '') {
         $return[$property . 'top'] = $object->top . $unit;
         $return[$property . 'right'] = $object->top . $unit;
         $return[$property . 'bottom'] = $object->top . $unit;
         $return[$property . 'left'] = $object->top . $unit;
      } else {
         if ($object->top != '') {
            $return[$property . 'top'] = $object->top . $unit;
         }
         if ($object->right != '') {
            $return[$property . 'right'] = $object->right . $unit;
         }
         if ($object->bottom != '') {
            $return[$property . 'bottom'] = $object->bottom . $unit;
         }
         if ($object->left != '') {
            $return[$property . 'left'] = $object->left . $unit;
         }
      }

      return $return;
   }
}
