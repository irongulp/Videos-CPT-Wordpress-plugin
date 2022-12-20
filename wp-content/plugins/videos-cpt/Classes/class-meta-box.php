<?php

namespace Classes;

use function __;
use function add_meta_box;
use function basename;
use function remove_meta_box;
use function remove_post_type_support;
use function wp_create_nonce;

class MetaBox implements Constants {
	public const NONCE_NAME = self::CPT_NAME . '_meta_box_nonce';
	private const META_BOXES_TO_REMOVE = [
		'normal'    => 'postcustom',
		'side'      => 'postimagediv'
	];
	private const VIDEO_TYPES = [
		'YouTube',
		'Vimeo',
		'Dailymotion'
	];

	public function __construct(
		private readonly Form $form
	) { }

	public function set(): void {
		add_meta_box(
			self::CPT_NAME . '-meta-box',
			__('Video details', 'textdomain'),
			[$this, 'show' ],
			self::CPT_NAME,
			'normal',
			'high'
		);

		// Remove unwanted boxes
		remove_post_type_support( self::CPT_NAME, 'editor' );
		foreach ( self::META_BOXES_TO_REMOVE as $type => $metaBox ) {
			remove_meta_box(
				$metaBox,
				self::CPT_NAME,
				$type
			);
		}
	}

	public function show(): void {
		global $post;
		$nonce_value = wp_create_nonce( $this->get_nonce_key() );
		echo $this->form->get(
			$post,
			$this->get_fields(),
			self::NONCE_NAME,
			$nonce_value
		);
	}

	public function get_nonce_key(): string {
		return basename( __FILE__ );
	}

	public function get_fields(): array {
		return [
			[
				'name' => __('Subtitle', 'textdomain'),
				'desc' => __('Subtitle of the video', 'textdomain'),
				'id' => self::CPT_NAME . '_subtitle',
				'type' => 'text',
				'default_value' => null
			],
			[
				'name' => __('Description', 'textdomain'),
				'desc' => __('Description of the video', 'textdomain'),
				'id' => self::CPT_NAME . '_desc',
				'type' => 'textarea',
				'default_value' => null
			],
			[
				'name' => __('Video ID', 'textdomain'),
				'desc' => __('Video ID number', 'textdomain'),
				'id' => self::CPT_NAME . '_id',
				'type' => 'text',
				'default_value' => null
			],
			[
				'name' => __('Type', 'textdomain'),
				'id' => self::CPT_NAME . '_type',
				'type' => 'dropdown',
				'options' => self::VIDEO_TYPES
			]
		];
	}
}