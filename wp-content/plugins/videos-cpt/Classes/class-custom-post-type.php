<?php

namespace Classes;

use function __;
use function defined;
use function delete_post_meta;
use function get_post_meta;
use function register_post_type;
use function update_post_meta;
use function wp_verify_nonce;

class CustomPostType implements Constants {
	private const CAPABILITY_TYPE = 'post';

	public function __construct(
		private readonly MetaBox $meta_box
	) { }

	public function register(): void {
		register_post_type( self::CPT_NAME,
			[
				'labels'      => [
					'name'                      => __( 'Videos', 'textdomain' ),
					'singular_name'             => __( 'Video', 'textdomain' ),
					'add_new_item'              => __('Add New Video', 'textdomain'),
					'edit_item'                 => __('Edit Video', 'textdomain'),
					'new_item'                  => __('New Video', 'textdomain'),
					'view_item'                 => __('View Video', 'textdomain'),
					'view_items'                => __('View Videos', 'textdomain'),
					'search_items'              => __('Search Videos', 'textdomain'),
					'not_found'                 => __('No videos found', 'textdomain'),
					'not_found_in_trash'        => __('No videos found in Trash', 'textdomain'),
					'all_items'                 => __('All Videos', 'textdomain'),
					'filter_items_list'         => __('Filter videos list', 'textdomain'),
					'items_list_navigation'     => __('Videos list navigation', 'textdomain'),
					'items_list'                => __('Videos list', 'textdomain'),
					'item_published'            => __('Video published.', 'textdomain'),
					'item_published_privately'  => __('Video published privately.', 'textdomain'),
					'item_reverted_to_draft'    => __('Video reverted to draft.', 'textdomain'),
					'item_scheduled'            => __('Video scheduled.', 'textdomain'),
					'item_updated'              => __('Video updated.', 'textdomain'),
					'item_link'                 => __('Video Link', 'textdomain'),
					'item_link_description'     => __('A link to a video.', 'textdomain')
				],
				'public'            => false,
				'has_archive'       => false,
				'show_in_menu'      => true,
				'show_ui'           => true,
				'menu_position'     => 20, // Below Pages
				'menu_icon'         => 'dashicons-video-alt',
				'capability_type'   => self::CAPABILITY_TYPE,
				'supports'          => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			]
		);
	}

	public function save( int $postId ): int {
		if ( $this->is_custom_post_type() ) {
			// Protect against CSRF
			if ( !wp_verify_nonce(
				$_POST[$this->meta_box::NONCE_NAME] ?? '',
				$this->meta_box->get_nonce_key()
			) ) {
				return $postId;
			}

			// Skip if autosave
			if ( $this->isAutosave() ) {
				return $postId;
			}

			foreach ( $this->meta_box->get_fields() as $field ) {
				$id = $field['id'];
				$old = get_post_meta( $postId, $id, true );
				$new = $_POST[$id] ?? '';

				if ( $new and $new != $old ) {
					update_post_meta( $postId, $id, $new );
				} elseif ( empty( $new ) && $old ) {
					delete_post_meta( $postId, $id, $old );
				}
			}
		}

		return $postId;
	}

	private function isAutosave(): bool {
		return defined( 'DOING_AUTOSAVE' ) and DOING_AUTOSAVE;
	}

	private function is_custom_post_type(): bool {
		return self::CPT_NAME === ( $_POST['post_type'] ?? ( $_GET['post_type'] ?? '' ) );
	}
}