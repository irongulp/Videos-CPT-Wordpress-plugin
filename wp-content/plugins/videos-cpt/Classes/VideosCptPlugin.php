<?php

namespace Classes;

use function add_shortcode;
use function flush_rewrite_rules;
use function is_numeric;

use function remove_menu_page;
use function unregister_post_type;

class VideosCptPlugin {
	private const CPT_NAME = 'videos_cpt';
	private const VIDEO_IMAGE_PATH = '/wp-content/plugins/videos-cpt/svg/video.svg';
	private const DEFAULT_BORDER_COLOR = '#3498db';
	private const DEFAULT_BORDER_WIDTH = '8px';
	private const DEFAULT_CSS_BORDER_WIDTH_UNIT = 'px';
	private const CPT_TEMPLATE = 'single-' . self::CPT_NAME . '.php';

	public function __construct() {
		return $this;
	}

	public function activate(): void {
		// Register the Videos CPT
		$this->registerCustomPostType();
		// Clear the permalinks after the post type has been registered.
		flush_rewrite_rules();
	}

	public function deactivate(): void {
		// Unregister the Videos CPT
		unregister_post_type('videos');
		// Clear the permalinks to remove the Video CPT's rules from the database
		flush_rewrite_rules();
	}

	public function registerCustomPostType(): void {
		register_post_type(self::CPT_NAME,
			[
				'labels'      => [
					'name'          => __('Videos', 'textdomain'),
					'singular_name' => __('Video', 'textdomain'),
				],
				'public'            => true,
				'has_archive'       => false,
				'show_in_menu'      => true,
				'show_ui'           => true,
				'menu_position'     => 20, // Below Pages
				'menu_icon'         => 'dashicons-video-alt',
				'capability_type'       => 'post',
				'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			]
		);
	}

	public function addShortcode(): void {
		add_shortcode(
			'prefix_video',
			self::CPT_NAME . '_get_shortcode'
		);
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
			$borderWidth = $attributes['border_width'];
			if (is_numeric($borderWidth)) {
				$borderWidth .= self::DEFAULT_CSS_BORDER_WIDTH_UNIT;
			}
			$output = $this->getBlock(
				$borderWidth,
				$attributes['border_color']
			);
		}

		if ($isInvalid) {
			$output = '<div>Invalid post id</div>';
		}

		return $output;
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

	public function hideMenuItemFromAuthors(): void {
		$user = wp_get_current_user();
		if ( in_array( 'author', $user->roles ) ) {
			remove_menu_page( 'edit.php?post_type=' . self::CPT_NAME );
		}
	}

	public function addMetaBox(string $function): void {
		add_meta_box(
			self::CPT_NAME . '-meta-box',
			'Video details',
			$function,
			self::CPT_NAME,
			'normal',
			'high'
		);
	}

	public function showMetaBox(): void {
		global $post;
		echo '<input type="hidden" name="' . self::CPT_NAME . '_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

		echo '<table class="form-table">';

		foreach ($this->getMetaBoxFields() as $field) {
			// get current post meta data
			$meta = get_post_meta($post->ID, $field['id'], true);

			echo '<tr>',
			'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
			'<td>';
			switch ($field['type']) {
				case 'text':
					echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ?: $field['std'], '" size="30" style="width:97%" />', '<br />', $field['desc'];
					break;
				case 'textarea':
					echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ?: $field['std'], '</textarea>', '<br />', $field['desc'];
					break;
				case 'select':
					echo '<select name="', $field['id'], '" id="', $field['id'], '">';
					foreach ($field['options'] as $option) {
						echo '<option ', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
					}
					echo '</select>';
					break;
				case 'radio':
					foreach ($field['options'] as $option) {
						echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
					}
					break;
				case 'checkbox':
					echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
					break;
			}
			echo     '</td><td>',
			'</td></tr>';
		}

		echo '</table>';
	}

	private function getMetaBoxFields(): array {
		return [
			[
				'name' => 'Text box',
				'desc' => 'Enter something here',
				'id' => self::CPT_NAME . 'text',
				'type' => 'text',
				'std' => 'Default value 1'
			],
			[
				'name' => 'Textarea',
				'desc' => 'Enter big text here',
				'id' => self::CPT_NAME . 'textarea',
				'type' => 'textarea',
				'std' => 'Default value 2'
			],
			[
				'name' => 'Select box',
				'id' => self::CPT_NAME . 'select',
				'type' => 'select',
				'options' => array('Option 1', 'Option 2', 'Option 3')
			],
			[
				'name' => 'Radio',
				'id' => self::CPT_NAME . 'radio',
				'type' => 'radio',
				'options' => array(
					array('name' => 'Name 1', 'value' => 'Value 1'),
					array('name' => 'Name 2', 'value' => 'Value 2')
				)
			],
			[
				'name' => 'Checkbox',
				'id' => self::CPT_NAME . 'checkbox',
				'type' => 'checkbox'
			]
		];
	}
}