<?php

namespace Classes;

use function array_push;
use function plugin_dir_url;

class TinyMce {

	private const TINY_MCE_JAVASCRIPT_PATH = 'js/tinymce-video-shortcode-class.js';

	public function add_plugin( array $plugin ): array {
		$plugin['custom_link_class'] = plugin_dir_url( __FILE__ ) . self::TINY_MCE_JAVASCRIPT_PATH;
		return $plugin;
	}

	public function add_toolbar_button( array $buttons ): array {
		array_push( $buttons, '|', 'custom_link_class' );
		return $buttons;
	}

}