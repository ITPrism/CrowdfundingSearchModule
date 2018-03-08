<?php
/**
 * @package      Crowdfunding
 * @subpackage   Modules
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2017 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Crowdfunding.init');

$moduleclassSfx = htmlspecialchars($params->get('moduleclass_sfx'));

// Include HTML helper
if ($params->get('enable_chosen', 0)) {
    JHtml::_('formbehavior.chosen', 'select.js-modsearch-filter');
}

$viewName     = $app->input->getCmd('view');
$filterPhrase = $app->getUserStateFromRequest('mod_crowdfundingsearch.filter_phrase', 'filter_phrase', '');

// Get options
$displayCountries    = (int)$params->get('display_countries', 0);
$displayCategories   = (int)$params->get('display_categories', 0);
$displayProjectTypes = (int)$params->get('display_project_type', 0);

// Prepare caching.
$cache = null;
if ($app->get('caching', 0)) {
    $cache = JFactory::getCache('com_crowdfunding', '');
    $cache->setLifeTime(Prism\Constants::TIME_SECONDS_24H);
}

if ($displayCountries) {
    $countries = null;
    // Get the countries from the cache.
    if ($cache !== null) {
        $countries = $cache->get(Crowdfunding\Constants::CACHE_COUNTRIES_CODES);
        $countries = is_array($countries) ? $countries : null;
    }

    if ($countries === null) {
        $countries = new Crowdfunding\Countries(JFactory::getDbo());
        $countries->load();
        $countries = $countries->toOptions('code', 'name');
        $countries = is_array($countries) ? $countries : array();

        // Store the countries into the cache.
        if ($cache !== null and count($countries) > 0) {
            $cache->store($countries, Crowdfunding\Constants::CACHE_COUNTRIES_CODES);
        }
    }

    $filterCountry = $app->getUserStateFromRequest('mod_crowdfundingsearch.filter_country', 'filter_country');

    $option = JHtml::_('select.option', '', JText::_('MOD_CROWDFUNDINGSEARCH_SELECT_COUNTRY'));
    $option = array($option);

    $countries = array_merge($option, $countries);
}

if ($displayCategories) {
    $categories = null;
    // Get the categories from the cache.
    if ($cache !== null) {
        $categories = $cache->get(Crowdfunding\Constants::CACHE_CATEGORIES);
        $categories = is_array($categories) ? $categories : null;
    }

    if ($categories === null) {
        $config     = array(
            'filter.published' => 1
        );
        $categories = JHtml::_('category.options', 'com_crowdfunding', $config);
        $categories = is_array($categories) ? $categories : array();

        // Store the categories into the cache.
        if ($cache !== null and count($categories) > 0) {
            $cache->store($categories, Crowdfunding\Constants::CACHE_CATEGORIES);
        }
    }

    $categoryId     = (strcmp($viewName, 'category') === 0) ? $app->input->getInt('id') : null;
    if ($categoryId !== null and (int)$categoryId > 0) {
        $app->input->set('filter_category', (int)$categoryId);
    }

    $filterCategory = $app->getUserStateFromRequest('mod_crowdfundingsearch.filter_category', 'filter_category', $categoryId);

    $option = JHtml::_('select.option', 0, JText::_('MOD_CROWDFUNDINGSEARCH_SELECT_CATEGORY'));
    $option = array($option);

    $categories = array_merge($option, $categories);
}

if ($displayProjectTypes) {
    $projectTypes = null;
    // Get the types from the cache.
    if ($cache !== null) {
        $projectTypes = $cache->get(Crowdfunding\Constants::CACHE_PROJECT_TYPES);
        $projectTypes = is_array($projectTypes) ? $projectTypes : null;
    }

    if ($projectTypes === null) {
        $types        = new Crowdfunding\Types(JFactory::getDbo());
        $types->load();

        $projectTypes = $types->toOptions();
        $projectTypes = is_array($projectTypes) ? $projectTypes : array();

        // Store the types into the cache.
        if ($cache !== null and count($projectTypes) > 0) {
            $cache->store($projectTypes, Crowdfunding\Constants::CACHE_PROJECT_TYPES);
        }
    }

    $filterProjectType = $app->getUserStateFromRequest('mod_crowdfundingfilters.filter_projecttype', 'filter_projecttype');

    $optionSelect = array(0 =>
        array(
          'value' => 0,
          'text'  => JText::_('MOD_CROWDFUNDINGSEARCH_SELECT_PROJECT_TYPE')
        )
    );
    $projectTypes = array_merge($optionSelect, $projectTypes);
}

require JModuleHelper::getLayoutPath('mod_crowdfundingsearch', $params->get('layout', 'default'));