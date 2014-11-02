<?php


/*
 * Set DOMTemplate node capture array.
 * @see \Dom\Template::$capture
 *
 * Customised array of node names or attribute names to collect the nodes for.
 * For example:
 *   Node Name = 'module': All DOMElements with the name <module></module> will be captured
 *   Attr Name = '@attr-name': All DOMElements containing the attr name 'attr-name' will be captured
 *
 */
$config['system.dom.capture'] = array('module');

/*
 * Set the full path and Url Path to the
 * folder containing the themed templates.
 */
$config['system.theme.path'] = $config->getSitePath() . '/theme';
$config['system.theme.url'] = $config->getSiteUrl() . '/theme';
$config['system.theme.default.name'] = 'default';
$config['system.theme.default.xmlPath'] = '/xml';
$config['system.theme.default.themeFile'] = 'public.tpl';

// -- The following are set dynamically in controller/Theme.php
// -- This is here for reference only
$config['system.theme.selected'] = '';
$config['system.theme.selected.name'] = '';
$config['system.theme.selected.path'] = '';
$config['system.theme.selected.url'] = '';
$config['system.theme.selected.themeFile'] = '';


