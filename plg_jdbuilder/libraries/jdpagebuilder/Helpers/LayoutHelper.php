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

class LayoutHelper
{
    public static function removeID(&$object)
    {
        if (isset($object['id'])) {
            unset($object['id']);
        }
        if (!isset($object['params'])) {
            $object['params'] = [];
        }
        switch ($object['type']) {
            case 'layout':
                foreach ($object['sections'] as &$section) {
                    self::removeID($section);
                }
                break;
            case 'section':
                foreach ($object['rows'] as &$row) {
                    self::removeID($row);
                }
                break;
            case 'row':
                foreach ($object['cols'] as &$col) {
                    self::removeID($col);
                }
                break;
            case 'column':
                foreach ($object['elements'] as &$element) {
                    self::removeID($element);
                }
                break;
            case 'inner-row':
                foreach ($object['cols'] as &$col) {
                    self::removeID($col);
                }
                break;
        }
    }

    public static function fixResonsiveFields($params)
    {
        foreach ($params as $prop => $value) {
            if ($value !== null && is_array($value) && isset($value['md'])) {
                if (isset($value['sm']) && is_array($value['sm']) && isset($value['sm']['md'])) {
                    $value['sm'] = $value['sm']['md'];
                }

                if (isset($value['xs']) && is_array($value['xs']) && isset($value['xs']['md'])) {
                    $value['xs'] = $value['xs']['md'];
                }
            }
        }
        return $params;
    }

    public static function refreshObjectID(&$object, $type = '', $layoutID = 0, $sectionIndex = null, $rowIndex = null, $columnIndex = null, $elementIndex = null)
    {
        $object['params'] = self::fixResonsiveFields($object['params']);
        if (!isset($object['id']) || empty($object['id'])) {
            if ($object['type'] === 'inner-row') {
                $type = 'inner-row';
            }
            switch ($type) {
                case 'section':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex);
                    break;
                case 'row':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex);
                    break;
                case 'inner-row':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
                    break;
                case 'inner-column':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
                    break;
                case 'column':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex);
                    break;
                case 'element':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
                    break;
            }
        }

        switch ($type) {
            case 'layout':
                foreach ($object['sections'] as $sIndex => &$section) {
                    self::refreshID($section, 'section', $layoutID, $sIndex);
                }
                break;
            case 'section':

                foreach ($object['rows'] as $rIndex => &$row) {
                    self::refreshID($row, 'row', $layoutID, $sectionIndex, $rIndex);
                }
                break;
            case 'row':
                foreach ($object['cols'] as $cIndex => &$col) {
                    self::refreshID($col, 'column', $layoutID, $sectionIndex, $rowIndex, $cIndex);
                }
                break;
            case 'inner-row':
                foreach ($object['cols'] as $icIndex => &$col) {
                    self::refreshID($col, 'inner-column', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $icIndex);
                }
                break;
            case 'inner-column':
                $innerColumnIndex = $elementIndex;
                foreach ($object['elements'] as $eIndex => &$element) {
                    self::refreshID($element, 'element', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $innerColumnIndex, $eIndex);
                }
                break;
            case 'column':
                foreach ($object['elements'] as $eIndex => &$element) {
                    self::refreshID($element, 'element', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $eIndex);
                }
                break;
        }
    }

    public static function generateID($type = null, $layoutID = null, $sectionIndex = null, $rowIndex = null, $columnIndex = null, $elementIndex = null)
    {
        $id = [];

        if ($type !== null) {
            $id[] = 'jd';
            if ($type == 'inner-row' || $type == 'inner-column') {
                if ($type == 'inner-row') {
                    $id[] = 'ir' . '-';
                } else {
                    $id[] = 'ic' . '-';
                }
            } else {
                $id[] = substr($type, 0, 1) . '-';
            }
            $id[] = substr(dechex(rand()), 2, 2);
        }

        if ($layoutID !== null) {
            if (!is_numeric($layoutID)) {
                $layoutIDSplit = explode('-', $layoutID);
                if (count($layoutIDSplit) > 1) {
                    $id[] = $layoutIDSplit[1];
                } else {
                    $id[] = $layoutID;
                }
            } else {
                $id[] = $layoutID;
            }
        }

        if ($sectionIndex !== null) {
            $id[] = $sectionIndex;
        }

        if ($rowIndex !== null) {
            $id[] = $rowIndex;
        }

        if ($columnIndex !== null) {
            $id[] = $columnIndex;
        }

        if ($elementIndex !== null) {
            $id[] = $elementIndex;
        }

        $id[] = substr(time(), -10);
        $id[] = substr(dechex(rand()), 3, 2);
        return implode('', $id);
    }

    public static function refreshID(&$object, $type, $layoutID = 0, $sectionIndex = null, $rowIndex = null, $columnIndex = null, $elementIndex = null)
    {
        $object['params'] = self::fixResonsiveFields($object['params']);

        if (!isset($object['id']) || empty($object['id'])) {
            if ($object['type'] === 'inner-row') {
                $type = 'inner-row';
            }
            switch ($type) {
                case 'section':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex);
                    break;
                case 'row':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex);
                    break;
                case 'inner-row':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
                    break;
                case 'inner-column':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
                    break;
                case 'column':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex);
                    break;
                case 'element':
                    $object['id'] = self::generateID($type, $layoutID, $sectionIndex, $rowIndex, $columnIndex, $elementIndex);
                    break;
            }
        }

        switch ($type) {
            case 'layout':
                foreach ($object['sections'] as $sIndex => &$section) {
                    self::refreshID($section, 'section', $layoutID, $sIndex);
                }
                break;
            case 'section':
                foreach ($object['rows'] as $rIndex => &$row) {
                    self::refreshID($row, 'row', $layoutID, $sectionIndex, $rIndex);
                }
                break;
            case 'row':
                foreach ($object['cols'] as $cIndex => &$col) {
                    self::refreshID($col, 'column', $layoutID, $sectionIndex, $rowIndex, $cIndex);
                }
                break;
            case 'inner-row':
                foreach ($object['cols'] as $icIndex => &$col) {
                    self::refreshID($col, 'inner-column', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $icIndex);
                }
                break;
            case 'inner-column':
                $innerColumnIndex = $elementIndex;
                foreach ($object['elements'] as $eIndex => &$element) {
                    self::refreshID($element, 'element', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $innerColumnIndex, $eIndex);
                }
                break;
            case 'column':

                foreach ($object['elements'] as $eIndex => &$element) {
                    self::refreshID($element, 'element', $layoutID, $sectionIndex, $rowIndex, $columnIndex, $eIndex);
                }
                break;
        }
    }
}
