<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Jdbuilder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 Hitesh Aggarwal
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
/**
 * Content Component Category Tree
 *
 * @since  1.6
 */
class JdbuilderCategories extends JCategories
{
    /**
     * Class constructor
     *
     * @param   array  $options  Array of options
     *
     * @since   11.1
     */
    public function __construct($options = array())
    {
        $options['table'] = '#__jdbuilder_pages';
        $options['extension'] = 'com_jdbuilder';

        parent::__construct($options);
    }
}
