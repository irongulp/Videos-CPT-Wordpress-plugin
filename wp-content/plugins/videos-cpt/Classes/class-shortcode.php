<?php

namespace Classes;

use function add_shortcode;
use function array_change_key_case;
use function get_post_meta;
use function get_the_title;
use function is_numeric;
use function shortcode_atts;
use function wp_enqueue_style;

class Shortcode implements Constants {

	private const VIDEO_THUMBNAIL_IMAGE_PATH = '/wp-content/plugins/videos-cpt/svg/video.svg';
	private const SHORTCODE_TAG = 'prefix_video';
	private const DEFAULT_BORDER_COLOR = '#3498db';
	private const DEFAULT_BORDER_WIDTH = '8px';
	private const DEFAULT_CSS_BORDER_WIDTH_UNIT = 'px';

	public function add(): void {
		add_shortcode(
			self::SHORTCODE_TAG,
			[$this, 'get' ]
		);
	}

	public function get(
		$attributes = array(),
		$content = null,
		$tag = ''
	): string {
		// Normalize attribute keys, lowercase by default
		$attributes = array_change_key_case( $attributes );

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

		if ( is_numeric( $postId )) {
			$isInvalid = false;
			$title = get_the_title( $postId );
			$subtitle = get_post_meta( $postId, self::CPT_NAME . '_subtitle', true );
			$desc = get_post_meta( $postId, self::CPT_NAME . '_desc', true );
			$borderWidth = $attributes['border_width'];
			if ( is_numeric( $borderWidth )) {
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

		if ( $isInvalid ) {
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
		wp_enqueue_style( 'wp-block-columns-inline-css', '/wp-includes/css/dist/block-library/style.min.css' );
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
}