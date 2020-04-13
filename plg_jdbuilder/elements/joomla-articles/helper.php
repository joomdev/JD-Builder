<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use JDPageBuilder\ElementHelper as ElementHelper;

class JDBuilderJoomlaArticlesElementHelper extends ElementHelper
{
    public static function getArticles($categories = [], $limit = null, $ordering = '', $subcategories = null, $featured = '', $direction = '', $viewmore = '', $format = '')
    {
        $app = \JFactory::getApplication();

        $option = $app->input->get('option', '');
        $ajax = $option == 'com_ajax' ? true : false;
        if (empty($categories)) {
            $categories = $app->input->get('categories', []);
        }
        if (empty($limit)) {
            $limit = $app->input->get('count', 10);
        }
        if (empty($ordering)) {
            $ordering = $app->input->get('ordering', 'a.ordering', 'RAW');
        }
        if (empty($featured)) {
            $featured = $app->input->get('featured', 'show');
        }
        if (empty($direction)) {
            $direction = $app->input->get('direction', 'ASC');
        }
        if (empty($viewmore)) {
            $viewmore = $app->input->get('viewmore', 0, 'INT');
        }
        if (empty($format)) {
            $format = $app->input->get('dateFormat', 'd M, Y', 'RAW');
        }
        if (empty($format)) {
            $format = 'd M, Y';
        }

        if ($subcategories == null) {
            $subcategories = $app->input->get('subcategories', false, 'RAW');
            $subcategories = $subcategories === 'true' ? true : false;
        }

        // Get the dbo
        $db = \JFactory::getDbo();

        // Get an instance of the generic articles model
        $model = \JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

        // Set application parameters in model
        $appParams = new \JRegistry();
        $model->setState('params', $appParams);

        $model->setState('list.start', 0);
        $model->setState('filter.published', 1);

        // Set the filters based on the module params
        $model->setState('list.limit', $limit);

        // This module does not use tags data
        $model->setState('load_tags', false);

        // Access filter
        $access = !\JComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = \JAccess::getAuthorisedViewLevels(\JFactory::getUser()->get('id'));
        $model->setState('filter.access', $access);

        if (!empty($categories) && $subcategories) {
            // Get an instance of the generic categories model
            $categoriesModel = \JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
            $levels = 9999;
            $categoriesModel->setState('filter.get_children', $levels);
            $categoriesModel->setState('filter.published', 1);
            $categoriesModel->setState('filter.access', $access);
            $additional_catids = array();

            foreach ($categories as $catid) {
                $categoriesModel->setState('filter.parentId', $catid);
                $items = $categoriesModel->getItems(true);
                if ($items) {
                    foreach ($items as $category) {
                        $condition = (($category->level - $categoriesModel->getParent()->level) <= $levels);

                        if ($condition) {
                            $additional_catids[] = $category->id;
                        }
                    }
                }
            }

            $categories = array_unique(array_merge($categories, $additional_catids));
        }

        // Category filter
        $model->setState('filter.category_id', $categories);

        // Featured switch
        $model->setState('filter.featured', $featured);

        // Set ordering
        if ($ordering == 'random') {
            $model->setState('list.ordering', \JFactory::getDbo()->getQuery(true)->Rand());
        } else {
            $model->setState('list.ordering', $ordering);
            $model->setState('list.direction', $direction);
        }

        $items = $model->getItems();
        $dFormat  = $format;

        foreach ($items as &$item) {
            $item->readmore = strlen(trim($item->fulltext));
            $item->slug = $item->id . ':' . $item->alias;
            $item->created_formatted = date($format, strtotime($item->created));
            $item->modified_formatted = date($format, strtotime($item->modified));
            $item->published_formatted = date($format, strtotime($item->publish_up));

            /** @deprecated Catslug is deprecated, use catid instead. 4.0 */
            $item->catslug = $item->catid . ':' . $item->category_alias;
            $item->catlink  = \JRoute::_(\ContentHelperRoute::getCategoryRoute($item->catid));
            if ($access || in_array($item->access, $authorised)) {
                // We know that user has the privilege to view the article
                $item->link     = \JRoute::_(\ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
                $item->linkText = \JText::_('MOD_ARTICLES_NEWS_READMORE');
            } else {
                $item->link = new \JUri(\JRoute::_('index.php?option=com_users&view=login', false));
                $item->link->setVar('return', base64_encode(\ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)));
                $item->linkText = \JText::_('MOD_ARTICLES_NEWS_READMORE_REGISTER');
            }

            $item->introtext = preg_replace('/<img[^>]*>/', '', $item->introtext);
            $item->introtext = strip_tags($item->introtext);


            $images = json_decode($item->images);
            $item->imageSrc = '';
            $item->imageAlt = '';
            $item->imageCaption = '';

            if (!empty($images->image_intro)) {
                $item->imageSrc = \JURI::root() . htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8');
                $item->imageAlt = htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8');

                if ($images->image_intro_caption) {
                    $item->imageCaption = htmlspecialchars($images->image_intro_caption, ENT_COMPAT, 'UTF-8');
                }
            } elseif (!empty($images->image_fulltext)) {
                $item->imageSrc = \JURI::root() . htmlspecialchars($images->image_fulltext, ENT_COMPAT, 'UTF-8');
                $item->imageAlt = htmlspecialchars($images->image_fulltext_alt, ENT_COMPAT, 'UTF-8');

                if ($images->image_intro_caption) {
                    $item->imageCaption = htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'UTF-8');
                }
            }
        }
        return ['items' => $items, 'viewmore' => \JDPageBuilder\Helper::getMenuLinkByItemId($viewmore)];
    }
}
