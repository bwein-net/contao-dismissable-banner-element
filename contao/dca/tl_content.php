<?php

declare(strict_types=1);

/*
 * This file is part of Dismissable Banner Element for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['dismissableBanner'] = '
    {type_legend},type,headline;
    {text_legend},text;
    {image_legend},addImage;
    {link_legend},dismissableBannerAddLink;
    {banner_legend},dismissableBannerExpiryLimit;
    {template_legend:hide},customTpl;
    {protected_legend:hide},protected;
    {expert_legend:hide},guests,cssID;
    {invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'dismissableBannerAddLink';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['dismissableBannerAddLink'] = 'url,target,linkTitle,titleText,rel';
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'dismissableBannerExpiryLimit';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['dismissableBannerExpiryLimit'] = 'dismissableBannerExpiryTime';

$GLOBALS['TL_DCA']['tl_content']['fields']['dismissableBannerAddLink'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['dismissableBannerExpiryLimit'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['dismissableBannerExpiryTime'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['tl_class' => 'w50'],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];
