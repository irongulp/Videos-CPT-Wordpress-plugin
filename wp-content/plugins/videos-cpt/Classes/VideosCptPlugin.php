<?php

namespace Classes;

use function add_shortcode;
use function flush_rewrite_rules;
use function is_numeric;
use function is_plugin_active;
use function unregister_post_type;

class VideosCptPlugin {
	private const VIDEO_IMAGE_PATH = '/wp-content/plugins/videos-cpt/svg/video.svg';
	private const DEFAULT_BORDER_COLOR = '#3498db';
	private const DEFAULT_BORDER_WIDTH = '8px';

	public function __construct() {
		return $this;
	}

	public function activate(): void {
		// Register the Videos CPT
		$this->registerCpt();
		// Clear the permalinks after the post type has been registered.
		flush_rewrite_rules();
	}

	public function deactivate(): void {
		// Unregister the Videos CPT
		unregister_post_type('videos');
		// Clear the permalinks to remove the Video CPT's rules from the database
		flush_rewrite_rules();
	}

	private function registerCpt(): void {
		register_post_type('videos_cpt',
			array(
				'labels'      => array(
					'name'          => __('Videos', 'textdomain'),
					'singular_name' => __('Video', 'textdomain'),
				),
				'public'      => true,
				'has_archive' => true,
				'show_in_menu' => 'edit.php'
			)
		);
	}

	public function addShortcode(): void {
		if ($this->pluginIsActive()){
			add_shortcode(
				'prefix_video',
				'videos_cpt_get_shortcode'
			);
		}
	}

	public function getShortcode(
		array $attributes,
		string $tag
	): string {
		// normalize attribute keys, lowercase
		$attributes = array_change_key_case($attributes);

		// override default attributes with user attributes
		$attributes = shortcode_atts(
			[
				'id'            => null,
				'border_color'  => self::DEFAULT_BORDER_COLOR,
				'border_width'  => self::DEFAULT_BORDER_WIDTH
			],
			$attributes,
			$tag
		);

		$isInvalid = true;
		$output = '';

		if (is_numeric($attributes['id'])) {
			$isInvalid = false;
			$output = $this->getBlock(
				$attributes['border_width'],
				$attributes['border_color']
			);
		}

		if ($isInvalid) {
			$output = '<div>Invalid post id</div>';
		}

		return $output;
	}

	private function pluginIsActive(): bool {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active('videos-cpt/videos-cpt.php');
	}

	private function getBlock(
		string $borderWidth,
		string $borderColor
	): string {
		wp_enqueue_style('wp-block-columns-inline-css', '/wp-includes/css/dist/block-library/style.min.css');
		$output = '<div class="is-layout-flex wp-container-10 wp-block-columns" ';
		$output .= 'style="border-width:' . $borderWidth . '; border-color:' . $borderColor . ';">';
		$output .= '<div class="is-layout-flow wp-block-column" style="flex-basis:33.33%; display: flex; flex-direction: column; justify-content: center;">';
		$output .= '<figure class="is-layout-flex wp-block-gallery-6 wp-block-gallery has-nested-images columns-default is-cropped;">';
		$output .= '<img src="' . self::VIDEO_IMAGE_PATH . '" tag="video">';
		$output .= '</figure>';
		$output .= '</div>';


		$output .= '<div class="is-layout-flow wp-block-column" style="flex-basis:66.66%">';
		$output .= '<h4>This is the title</h4>';
		$output .= '<h5>This is the subtitle</h5>';
		$output .= '<p>This is the description</p>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}
}