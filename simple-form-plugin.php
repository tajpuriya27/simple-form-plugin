<?php
/**
 * Plugin Name:     Simple Form Plugin
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     simple-form-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 */

defined( 'ABSPATH' ) || exit;

require __DIR__ . '/Plugin.php';

use SimplePluginForm\Plugin;
Plugin::instance();
