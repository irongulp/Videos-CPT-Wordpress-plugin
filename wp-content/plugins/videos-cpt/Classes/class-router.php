<?php

namespace Classes;

use function add_action;
use function add_filter;
use function register_activation_hook;
use function register_deactivation_hook;

class Router implements Constants {
	public function __construct(
		private readonly Plugin $plugin,
		private readonly CustomPostType $custom_post_type,
		private readonly MetaBox $meta_box,
		private readonly Shortcode $shortcode,
		private readonly TinyMce $tiny_mce,
		private readonly Modal $modal
	) {
		return $this;
	}

	public function addHooks( string $file ): void {
		register_activation_hook( $file,	[$this->plugin, 'activatePlugin'] );
		register_deactivation_hook( $file, [$this->plugin, 'deactivatePlugin'] );

		add_action( 'init', [$this->custom_post_type, 'register'] );
		add_action( 'init', [$this->shortcode, 'add'] );
		add_action( 'admin_menu', [$this->plugin, 'hideMenuItemFromAuthors'] );
		add_action( 'add_meta_boxes', [$this->meta_box, 'set'] );
		add_action( 'save_post', [$this->custom_post_type, 'save'] );
		add_action( 'admin_enqueue_scripts', [$this->modal, 'add_styling'] );
		add_action( 'edit_form_after_editor', [$this->modal, 'show'] );

		add_filter( 'mce_buttons', [$this->tiny_mce, 'add_toolbar_button'] );
		add_filter( 'mce_external_plugins', [$this->tiny_mce, 'add_plugin'] );
		add_filter( 'use_block_editor_for_post', '__return_false');
	}
}