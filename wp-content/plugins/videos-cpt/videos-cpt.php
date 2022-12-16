<?php

use Classes\VideosCptPlugin;

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

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Functions */

function videos_cpt_get_class(): VideosCptPlugin {
	require_once plugin_dir_path( __FILE__ ). 'Classes/VideosCptPlugin.php';
	return new VideosCptPlugin();
}

/** Activate the plugin */
function videos_cpt_activate(): void {
	$plugin = videos_cpt_get_class();
	$plugin->activate();
}

/** Deactivate the plugin */
function videos_cpt_deactivate(): void {
	$plugin = videos_cpt_get_class();
	$plugin->deactivate();
}

/* Register the Videos CPT */
function videos_cpt_custom_post_type_init(): void {
	$plugin = videos_cpt_get_class();
	$plugin->registerCustomPostType();
}

/** Add shortcode */
function videos_cpt_shortcode_init(): void {
	$plugin = videos_cpt_get_class();
	$plugin->addShortcode();
}

/** Get shortcode output */
function videos_cpt_get_shortcode(
	$attributes = array(),
	$content = null,
	$tag = ''
): string {
	$plugin = videos_cpt_get_class();
	return $plugin->getShortcode(
		$attributes,
		$tag
	);
}

/** Hide from authors */
function videos_cpt_remove_menu_items(): void {
	$plugin = videos_cpt_get_class();
	$plugin->hideMenuItemFromAuthors();
}

/** Add meta box */
function videos_cpt_add_meta_box( ): void {
	$plugin = videos_cpt_get_class();
	$plugin->addMetaBox('videos_cpt_show_meta_box');
}

/** Show meta box */
function videos_cpt_show_meta_box(): void {
	$plugin = videos_cpt_get_class();
	$plugin->showMetaBox();
}

/** Register activate and deactivate functions */
register_activation_hook(
	__FILE__,
	'videos_cpt_activate'
);
register_deactivation_hook(
	__FILE__,
	'videos_cpt_deactivate'
);

/** Hooks */

/** Add shortcode */
add_action( 'init', 'videos_cpt_shortcode_init' );

/** Register Videos CPT */
add_action( 'init', 'videos_cpt_custom_post_type_init' );

/** Hide from Authors */
add_action( 'admin_menu', 'videos_cpt_remove_menu_items' );

/** Add meta box */
add_action( 'add_meta_boxes', 'videos_cpt_add_meta_box' );