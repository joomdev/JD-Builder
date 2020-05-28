<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
extract($displayData);
if ($element->indexMode) {
    return;
}
$element->addClass('jdb-joomla-articles');
$categories = $element->params->get('categories', []);
$articleCount = $element->params->get('articleCount', 10);
$articleOrdering = $element->params->get('articleOrdering', 'a.ordering');
$direction = $element->params->get('articleOrderingDirection', 'ASC');
$featured = $element->params->get('featuredArticle', 'show');
$viewReadLink = $element->params->get('viewReadLink', 0);
$subcategories = $element->params->get('subcategoryArticles', false);
$linkOn = $element->params->get('linkOn', 'title');
$titleLink = ($linkOn == 'title' || $linkOn == 'title-thumbnail') ? true : false;
$thumbnailLink = ($linkOn == 'thumbnail' || $linkOn == 'title-thumbnail') ? true : false;
$dateFormat = $element->params->get('metaDateFormat', 'd M, Y');

$data = \JDBuilderJoomlaArticlesElementHelper::getArticles($categories, $articleCount, $articleOrdering, $subcategories, $featured, $direction, $viewReadLink, $dateFormat);

$items = $data['items'];

$layout = $element->params->get('articleLayout', 'grid');

$rowColsClass = ['jdb-row'];
if ($layout == 'grid') {
    $layoutClass = 'jdb-jarticles jdb-jarticles-grid-view';
    $columns = $element->params->get('columns', null);
    if (!empty($columns)) {
        foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
            if (isset($columns->{$deviceKey}) && !empty($columns->{$deviceKey})) {
                switch ($device) {
                    case 'desktop':
                        $rowColsClass[] = 'jdb-row-cols-lg-' . $columns->{$deviceKey};
                        break;
                    case 'tablet':
                        $rowColsClass[] = 'jdb-row-cols-sm-' . $columns->{$deviceKey};
                        break;
                    case 'mobile':
                        $rowColsClass[] = 'jdb-row-cols-' . $columns->{$deviceKey};
                        break;
                }
            }
        }
    }
} else {
    $layoutClass = 'jdb-jarticles jdb-jarticles-list-view';
    if ($layout == "list-alternate") {
        $layoutClass .= ' jdb-jarticles-list-view-alternate';
    }
    $rowColsClass[] = 'jdb-row-cols-1';
}

$articleMetaData = $element->params->get('articleMetaData', []);
$mIcon = $element->params->get('articleMetaIcons', true);
if ($mIcon) {
    \JDPageBuilder\Builder::loadFontLibraryByIcon('far fa-user');
}

$titleTag = $element->params->get('titleHtmlTag', 'h2');

$readMoreText = $element->params->get('readmoreText', '');
$viewmoreText = $element->params->get('viewmoreText', '');
?>
<div class="<?php echo $layoutClass; ?>">
    <div class="<?php echo implode(' ', $rowColsClass); ?>">
        <?php foreach ($items as $item) { ?>
            <div class="jdb-col jdb-jarticle-wrapper">
                <article itemprop="blogPost" itemscope="" itemtype="https://schema.org/BlogPosting" class="jdb-jarticle">
                    <?php if (!empty($item->imageSrc) && $element->params->get('articleThumbnail', true)) { ?>
                        <div class="jdb-jarticle-img-wrap">
                            <?php if ($thumbnailLink) { ?>
                                <a title="<?php echo $item->title; ?>" href="<?php echo $item->link; ?>">
                                <?php } ?>
                                <img class="jdb-jarticle-img" src="<?php echo $item->imageSrc; ?>" alt="<?php echo $item->imageAlt; ?>" itemprop="thumbnailUrl">
                                <?php if ($thumbnailLink) { ?>
                                    </a">
                                <?php } ?>
                        </div>
                    <?php } ?>
                    <div class="jdb-jarticle-body">
                        <<?php echo $titleTag; ?> itemprop="name" class="jdb-jarticle-title">
                            <?php if ($titleLink) { ?>
                                <a itemprop="url" href="<?php echo $item->link; ?>" title="<?php echo $item->title; ?>">
                                <?php } else { ?>
                                    <span>
                                    <?php } ?>
                                    <?php echo $item->title; ?>
                                    <?php if ($titleLink) { ?>
                                </a>
                            <?php } else { ?>
                                </span>
                            <?php } ?>

                        </<?php echo $titleTag; ?>>
                        <?php if (!empty($articleMetaData)) { ?>
                            <div class="jdb-jarticle-meta-info">
                                <?php if (in_array('author', $articleMetaData)) { ?>
                                    <span class="jdb-jarticle-author">
                                        <?php if ($mIcon) { ?><i class="far fa-user"></i><?php } ?>
                                        <?php echo $item->created_by_alias ?: $item->author; ?></span>
                                <?php } ?>
                                <?php if (in_array('category', $articleMetaData)) { ?>
                                    <span class="jdb-jarticle-category">
                                        <?php if ($mIcon) { ?><i class="far fa-folder"></i><?php } ?>
                                        <?php echo $item->category_title; ?>
                                    </span>
                                <?php } ?>
                                <?php if (in_array('publish-date', $articleMetaData)) { ?>
                                    <span class="jdb-jarticle-published-date">
                                        <?php if ($mIcon) { ?><i class="far fa-calendar-check"></i><?php } ?>
                                        <?php echo JText::_('JDB_JARTICLES_META_PUBLISHED_ON');?> <?php echo $item->published_formatted; ?></span>
                                <?php } ?>
                                <?php if (in_array('created-date', $articleMetaData)) { ?>
                                    <span class="jdb-jarticle-created-date">
                                        <?php if ($mIcon) { ?><i class="far fa-calendar-plus"></i><?php } ?>
                                        <?php echo JText::_('JDB_JARTICLES_META_CREATED_ON');?> <?php echo $item->created_formatted; ?></span>
                                <?php } ?>
                                <?php if (in_array('modified-date', $articleMetaData)) { ?>
                                    <span class="jdb-jarticle-modified-date">
                                        <?php if ($mIcon) { ?><i class="far fa-calendar-alt"></i><?php } ?>
                                        <?php echo JText::_('JDB_JARTICLES_META_MODIFIED_ON');?> <?php echo $item->modified_formatted; ?></span>
                                <?php } ?>
                                <?php if (in_array('hits', $articleMetaData)) { ?>
                                    <span class="jdb-jarticle-hits">
                                        <?php if ($mIcon) { ?><i class="far fa-eye"></i><?php } ?>
                                        <?php echo JText::_('JDB_JARTICLES_META_HITS');?> <?php echo $item->hits; ?></span>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php if ($element->params->get('articleIntro', true)) { ?>
                            <p class="jdb-jarticle-introtext"><?php echo \JDPageBuilder\Helper::chopString($item->introtext, $element->params->get('articleIntroLimit', 150)); ?></p>
                        <?php } ?>
                        <?php
                        if (!empty($readMoreText)) {
                            echo JDPageBuilder\Helper::renderButtonValue('readmore', $element, $readMoreText, [], 'link', $item->link);
                        ?>
                        <?php } ?>
                    </div>
                </article>
            </div>
        <?php } ?>
    </div>
</div>
<?php if (!empty($viewmoreText) && !empty($data['viewmore'])) {
    echo JDPageBuilder\Helper::renderButtonValue('viewmore', $element, $viewmoreText, [], 'link', $data['viewmore']);
} ?>

<?php
// meta styling
$metaStyle = new JDPageBuilder\Element\ElementStyle(".jdb-jarticle-meta-info");
$metaTitleStyle = new JDPageBuilder\Element\ElementStyle(".jdb-jarticle-meta-info span:not(:last-child)");
$metaIconStyle = new JDPageBuilder\Element\ElementStyle(".jdb-jarticle-meta-info span i");

$element->addChildrenStyle([$metaStyle, $metaTitleStyle, $metaIconStyle]);

$metaStyle->addCss('color', $element->params->get('metaColor', ''));
$metaIconStyle->addCss('color', $element->params->get('metaIconColor', ''));

$spacing = $element->params->get('metaSpacing', null);
if (!empty($spacing)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($spacing->{$deviceKey}) && JDPageBuilder\Helper::checkSliderValue($spacing->{$deviceKey})) {
            $metaTitleStyle->addCss('margin-right', $spacing->{$deviceKey}->value . 'px', $device);
        }
    }
}

$margin = $element->params->get('metaMargin', null);
if (!empty($margin)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($margin->{$deviceKey}) && !empty($margin->{$deviceKey})) {
            $metaStyle->addStyle(JDPageBuilder\Helper::spacingValue($margin->{$deviceKey}, "margin"), $device);
        }
    }
}

$typography = $element->params->get('metaTypography', null);
if (!empty($typography)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
            $metaStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
            if (isset($typography->{$deviceKey}->alignment) && !empty($typography->{$deviceKey}->alignment)) {
                $justifyContent = $typography->{$deviceKey}->alignment;
                $justifyContent = $justifyContent == 'left' ? 'flex-start' : ($justifyContent == 'right' ? 'flex-end' : ($justifyContent == 'center' ? 'center' : ($justifyContent == 'justify' ? 'space-between' : '')));
                $metaStyle->addCss('justify-content', $justifyContent, $device);
            }
        }
    }
}

// title & content styling
$titleStyle = new JDPageBuilder\Element\ElementStyle(".jdb-jarticle-title");
$titleChildStyle = new JDPageBuilder\Element\ElementStyle(".jdb-jarticle-title > span,.jdb-jarticle-title > a");
$titleChildHoverStyle = new JDPageBuilder\Element\ElementStyle(".jdb-jarticle-title > span:hover,.jdb-jarticle-title > a:hover");
$contentStyle = new JDPageBuilder\Element\ElementStyle(".jdb-jarticle-introtext");

$element->addChildrenStyle([$titleStyle, $titleChildStyle, $titleChildHoverStyle, $contentStyle]);
$titleChildStyle->addCss('color', $element->params->get('titleColor', ''));
if ($titleLink) {
    $titleChildHoverStyle->addCss('color', $element->params->get('titleHoverColor', ''));
}
$contentStyle->addCss('color', $element->params->get('contentColor', ''));

$typography = $element->params->get('titleTypography', null);
if (!empty($typography)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
            $titleStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
        }
    }
}

$typography = $element->params->get('contentTypography', null);
if (!empty($typography)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($typography->{$deviceKey}) && !empty($typography->{$deviceKey})) {
            $contentStyle->addStyle(JDPageBuilder\Helper::typographyValue($typography->{$deviceKey}), $device);
        }
    }
}

$margin = $element->params->get('titleMargin', null);
if (!empty($margin)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($margin->{$deviceKey}) && !empty($margin->{$deviceKey})) {
            $titleStyle->addStyle(JDPageBuilder\Helper::spacingValue($margin->{$deviceKey}, "margin"), $device);
        }
    }
}

$margin = $element->params->get('contentMargin', null);
if (!empty($margin)) {
    foreach (JDPageBuilder\Helper::$devices as $deviceKey => $device) {
        if (isset($margin->{$deviceKey}) && !empty($margin->{$deviceKey})) {
            $contentStyle->addStyle(JDPageBuilder\Helper::spacingValue($margin->{$deviceKey}, "margin"), $device);
        }
    }
}
?>