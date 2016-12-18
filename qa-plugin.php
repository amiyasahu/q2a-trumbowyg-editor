<?php
/*
	Plugin Name: Q2A Trumbowyg Editor
	Plugin URI:
	Plugin Description: Question2Answer Wrapper for Trumbowyg - A lightweight WYSIWYG editor
	Plugin Version: 1.0-beta
	Plugin Date: 2016-12-18
	Plugin Author: Amiya Sahu
	Plugin Author URI: http://www.amiyasahu.com
	Plugin License: MIT
	Plugin Minimum Question2Answer Version: 1.3
	Plugin Update Check URI:
*/


if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

/**
 * Register the editor module 
 */
qa_register_plugin_module('editor', 'qa-trumbowyg-editor.php', 'qa_trumbowyg_editor', 'Trumbowyg Editor');

/**
 * Register a page module for handeling the upload actions 
 */
qa_register_plugin_module('page', 'qa-trumbowyg-upload.php', 'qa_trumbowyg_upload', 'Trumbowyg Upload');
