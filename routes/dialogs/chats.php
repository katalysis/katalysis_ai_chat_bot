<?php

/** @noinspection DuplicatedCode */

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var \Concrete\Core\Routing\Router $router
 * Base path: /ccm/system/dialogs/chats
 * Namespace: Concrete\Package\use KatalysisAiChatBot\Controller\Dialog\Chats
 */

$router->all('/advanced_search', 'AdvancedSearch::view');
$router->all('/advanced_search/add_field', 'AdvancedSearch::addField');
$router->all('/advanced_search/submit', 'AdvancedSearch::submit');
$router->all('/advanced_search/save_preset', 'AdvancedSearch::savePreset');
$router->all('/advanced_search/preset/edit', 'Preset\Edit::view');
$router->all('/advanced_search/preset/edit/edit_search_preset', 'Preset\Edit::edit_search_preset');
$router->all('/advanced_search/preset/delete', 'Preset\Delete::view');
$router->all('/advanced_search/preset/delete/remove_search_preset', 'Preset\Delete::remove_search_preset');

$router->all('/ccm/system/search/chats/basic', '\Concrete\Package\use KatalysisAiChatBot\Controller\Search\Chats::searchBasic');
$router->all('/ccm/system/search/chats/current', '\Concrete\Package\use KatalysisAiChatBot\Controller\Search\Chats::searchCurrent');
$router->all('/ccm/system/search/chats/preset/{presetID}', '\Concrete\Package\use KatalysisAiChatBot\Controller\Search\Chats::searchPreset');
$router->all('/ccm/system/search/chats/clear', '\Concrete\Package\use KatalysisAiChatBot\Controller\Search\Chats::clearSearch');
