<?php

/**
 * Videos CPT
 *
 * @package           VideosCpt
 * @author            Tim Rogers
 * @copyright         2022 Tim Rogers
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Videos CPT
 * Description:       This plugin registers the Videos CPT.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Tim Rogers
 * Author URI:        https://etimbo.com
 * Text Domain:       plugin-slug
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

/*
Videos CPT is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Videos CPT is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Videos CPT. If not, see https://www.gnu.org/licenses/gpl-2.0.txt.
*/

use Classes\CustomPostType;
use Classes\Form;
use Classes\MetaBox;
use Classes\Modal;
use Classes\Plugin;
use Classes\Router;
use Classes\Shortcode;
use Classes\TinyMce;
use Classes\User;

require_once plugin_dir_path( __FILE__ ). 'load-classes.php';

// Create classes and inject dependencies
$form = new Form();
$meta_box = new MetaBox($form);
$shortcode = new Shortcode();
$tiny_mce = new TinyMce();
$modal = new Modal();
$user = new User();
$custom_post_type = new CustomPostType($meta_box);
$plugin = new Plugin(
	$custom_post_type,
	$user
);
$router = new Router(
	$plugin,
	$custom_post_type,
	$meta_box,
	$shortcode,
	$tiny_mce,
	$modal
);

$router->addHooks(__FILE__);