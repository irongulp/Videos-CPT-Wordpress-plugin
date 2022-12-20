<?php

namespace Classes;

use function __;
use function plugin_dir_url;
use function wp_enqueue_style;
use function wp_register_style;

class Modal {

	private const MODAL_STYLESHEET_PATH = 'css/modal.css';
	public function show(): void {
		$post_id_label = __('Post ID', 'textdomain');
		$border_width_label = __('Border width', 'textdomain');
		$border_color_label = __('Border colour', 'textdomain');
		$insert_button_label = __('Insert', 'textdomain');
		$cancel_button_label = __('Cancel', 'textdomain');

		$modal = '<div id="video-shortcode-modal" class="video-shortcode-modal">';
		$modal .= '<div class="video-shortcode-modal-content">';
		$modal .= '<h3>' . __('Video shortcode', 'textdomain') . '</h3>';
		$modal .= '<table class="form-table">';
		$modal .= '<tr>';
		$modal .= '<th style="width:50%"><label for="video-cpt-post-id">';
		$modal .= $post_id_label;
		$modal .= '</label></th>';
		$modal .= '<td>';
		$modal .= '<input type="text" name="post-id" id="video-cpt-post-id" value="" size="30" style="width:97%" />';
		$modal .= '</td></tr>';
		$modal .= '<tr>';
		$modal .= '<th style="width:50%"><label for="video-cpt-border-width">' . $border_width_label . '</label></th>';
		$modal .= '<td>';
		$modal .= '<input type="text" name="border-width" id="video-cpt-border-width" value="" size="30" style="width:97%" />';
		$modal .= '</td></tr>';
		$modal .= '<tr>';
		$modal .= '<th style="width:50%"><label for="video-cpt-border-color">' .$border_color_label . '</label></th>';
		$modal .= '<td>';
		$modal .= '<input type="color" id="video-cpt-border-color" value="#3498db">';
		$modal .= '</td></tr>';
		$modal .= '</table>';
		$modal .= '<div><span class="button" id="video-cpt-insert">' . $insert_button_label .'</span>&nbsp;';
		$modal .= '<span class="button" id="video-cpt-close">' . $cancel_button_label . '</span></div>';
		$modal .= '</div>';
		$modal .= '</div>';

		echo $modal;
	}

	public function add_styling(): void {
		wp_register_style('modal', plugin_dir_url( __FILE__ ) . self::MODAL_STYLESHEET_PATH);
		wp_enqueue_style('modal');
	}
}