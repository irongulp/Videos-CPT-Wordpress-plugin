<?php

namespace Classes;

use function __;
use function add_action;
use function add_shortcode;
use function array_intersect;
use function defined;
use function flush_rewrite_rules;
use function get_post_meta;
use function get_the_title;
use function is_numeric;
use function register_activation_hook;
use function register_deactivation_hook;
use function remove_menu_page;
use function remove_meta_box;
use function unregister_post_type;
use function wp_get_current_user;

class Plugin {
	private const CPT_NAME = 'videos_cpt';
	private const VIDEO_THUMBNAIL_IMAGE_PATH = '/wp-content/plugins/videos-cpt/svg/video.svg';
	private const SHORTCODE_TAG = 'prefix_video';
	private const DEFAULT_BORDER_COLOR = '#3498db';
	private const DEFAULT_BORDER_WIDTH = '8px';
	private const DEFAULT_CSS_BORDER_WIDTH_UNIT = 'px';
	private const CAPABILITY_TYPE = 'post';
	private const META_BOXES_TO_REMOVE = [
		'normal'    => 'postcustom',
		'side'      => 'postimagediv'
	];
	private const ALLOWED_ROLES = [
		'administrator',
		'editor'
	];
	private const VIDEO_TYPES = [
		'YouTube',
		'Vimeo',
		'Dailymotion'
	];

	public function __construct() {
		return $this;
	}

	public function addHooks(string $file): void {
		register_activation_hook($file,	[$this, 'activatePlugin' ]);
		register_deactivation_hook($file, [$this, 'deactivatePlugin' ]);
		add_action('init', [$this, 'registerCustomPostType']);
		add_action('init', [$this, 'addShortcode']);
		add_action('add_meta_boxes', [$this, 'setMetaBoxes' ]);
		add_action('admin_menu', [$this, 'hideMenuItemFromAuthors']);
		add_action('save_post', [$this, 'savePost']);
	}

	public function activatePlugin(): void {
		$this->registerCustomPostType();
		// Clear the permalinks after the post type has been registered.
		flush_rewrite_rules();
	}

	public function deactivatePlugin(): void {
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
				'public'            => false,
				'has_archive'       => false,
				'show_in_menu'      => true,
				'show_ui'           => true,
				'menu_position'     => 20, // Below Pages
				'menu_icon'         => 'dashicons-video-alt',
				'capability_type'       => self::CAPABILITY_TYPE,
				'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			]
		);
	}

	public function addShortcode(): void {
		add_shortcode(
			self::SHORTCODE_TAG,
			[$this, 'getShortcode']
		);
	}

	public function getShortcode(
		$attributes = array(),
		$content = null,
		$tag = ''
	): string {
		// Normalize attribute keys, lowercase by default
		$attributes = array_change_key_case($attributes);

		// Override default attributes with user attributes
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
		$postId = $attributes['id'];

		if (is_numeric($postId)) {
			$isInvalid = false;
			$title = get_the_title($postId);
			$subtitle = get_post_meta($postId, self::CPT_NAME . '_subtitle', true);
			$desc = get_post_meta($postId, self::CPT_NAME . '_desc', true);
			$borderWidth = $attributes['border_width'];
			if (is_numeric($borderWidth)) {
				$borderWidth .= self::DEFAULT_CSS_BORDER_WIDTH_UNIT;
			}
			$output = $this->getBlock(
				$title,
				$subtitle,
				$desc,
				$borderWidth,
				$attributes['border_color']
			);
		}

		if ($isInvalid) {
			$output = '<div>Invalid ' . self::SHORTCODE_TAG . '</div>';
		}

		return $output;
	}

	private function getBlock(
		string $title,
		string $subtitle,
		string $desc,
		string $borderWidth,
		string $borderColor
	): string {
		wp_enqueue_style('wp-block-columns-inline-css', '/wp-includes/css/dist/block-library/style.min.css');
		$output = '<div class="is-layout-flex wp-container-10 wp-block-columns" ';
		$output .= 'style="border-width:' . $borderWidth . '; border-color:' . $borderColor . ';">';
		$output .= '<div class="is-layout-flow wp-block-column" style="flex-basis:33.33%; display: flex; flex-direction: column; justify-content: center;">';
		$output .= '<figure class="is-layout-flex wp-block-gallery-6 wp-block-gallery has-nested-images columns-default is-cropped;">';
		$output .= '<img src="' . self::VIDEO_THUMBNAIL_IMAGE_PATH . '" tag="video">';
		$output .= '</figure>';
		$output .= '</div>';


		$output .= '<div class="is-layout-flow wp-block-column" style="flex-basis:66.66%">';
		$output .= '<h4>' . $title . '</h4>';
		$output .= '<h5>' . $subtitle . '</h5>';
		$output .= '<p>' . $desc . '</p>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function hideMenuItemFromAuthors(): void {
		if (!$this->userIsAllowed()) {
			remove_menu_page( 'edit.php?post_type=' . self::CPT_NAME );
		}
	}

	/**
	 * User Is Allowed
	 * Returns true if the user is allowed to edit, otherwise returns false (i.e. if the user is Author or below).
	 * @return bool
	 */
	private function userIsAllowed(): bool {
		$userIsAllowed = false;
		$user = wp_get_current_user();
		if (array_intersect(self::ALLOWED_ROLES, $user->roles)) {
			$userIsAllowed = true;
		}

		return $userIsAllowed;
	}

	public function setMetaBoxes(): void {
		add_meta_box(
			self::CPT_NAME . '-meta-box',
			'Video details',
			[$this, 'showMetaBox'],
			self::CPT_NAME,
			'normal',
			'high'
		);

		// Remove unwanted boxes
		remove_post_type_support( self::CPT_NAME, 'editor' );
		foreach (self::META_BOXES_TO_REMOVE as $type => $metaBox) {
			remove_meta_box(
				$metaBox,
				self::CPT_NAME,
				$type
			);
		}
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
				'name' => 'Subtitle',
				'desc' => 'Subtitle of the video',
				'id' => self::CPT_NAME . '_subtitle',
				'type' => 'text',
				'std' => null
			],
			[
				'name' => 'Description',
				'desc' => 'Description of the video',
				'id' => self::CPT_NAME . '_desc',
				'type' => 'textarea',
				'std' => 'Description'
			],
			[
				'name' => 'Video ID',
				'desc' => 'Video ID number',
				'id' => self::CPT_NAME . '_id',
				'type' => 'text',
				'std' => null
			],
			[
				'name' => 'Type',
				'id' => self::CPT_NAME . '_type',
				'type' => 'select',
				'options' => self::VIDEO_TYPES
			]
		];
	}

	public function savePost(int $postId): int {
		// Skip if autosave
		if ($this->isAutosave()) {
			return $postId;
		}

		if ($this->isCpt()) {
			foreach ($this->getMetaBoxFields() as $field) {
				$id = $field['id'];
				$old = get_post_meta($postId, $id, true);
				$new = $_POST[$id];

				if ($new and $new != $old) {
					update_post_meta($postId, $id, $new);
				} elseif (empty($new) && $old) {
					delete_post_meta($postId, $id, $old);
				}
			}
		}

		return $postId;
	}

	private function isAutosave(): bool {
		return defined('DOING_AUTOSAVE') and DOING_AUTOSAVE;
	}

	private function isCpt(): bool {
		return $_POST['post_type'] ?? $_GET['post_type'] ?? '' == self::CPT_NAME;
	}
}