<?php

namespace Classes;

use WP_Post;
use function get_post_meta;

class Form implements Constants {

	public function get(
		WP_Post $post,
		array $fields,
		string $nonce_name,
		string $nonce_value
	): string {
		$form = $this->get_nonce_field(
			$nonce_name,
			$nonce_value
		);
		$form .= '<table class="form-table">';

		foreach ( $fields as $field ) {
			$id = $field['id'];
			$name = $field['name'];
			$default_value = $field['default_value'] ?? '';
			$desc = $field['desc'] ?? '';
			$options = $field['options'] ?? array();
			$current_value = get_post_meta( $post->ID, $id, true );
			
			$form .= $this->get_start(
				$id,
				$name
			);
			switch ( $field['type'] ) {
				case 'text':
					$form .= $this->get_input_text(
						$id,
						$current_value,
						$default_value,
						$desc
					);
					break;
				case 'textarea':
					$form .= $this->get_textarea(
						$id,
						$current_value,
						$default_value,
						$desc
					);
					break;
				case 'dropdown':
					$form .= $this->get_dropdown(
						$id,
						$options,
						$current_value
					);
					break;
				case 'radio':
					$form .= $this->get_radio_buttons(
						$id,
						$options,
						$current_value
					);
					break;
				case 'checkbox':
					$form .= $this->get_checkbox(
						$id,
						$current_value
					);
					break;
			}
			$form .= $this->get_end();
		}
		$form .= '</table>';
		
		return $form;
	}

	/**
	 * Get Nonce Field
	 * Returns a hidden field holding a nonce. This is needed to protect against CSRF.
	 *
	 * @param string $nonce_name
	 * @param string $nonce_value
	 *
	 * @return string
	 */
	private function get_nonce_field(
		string $nonce_name,
		string $nonce_value
	): string {
		$content = '<input ';
		$content .= 'type="hidden" ';
		$content .= 'name="' . $nonce_name . '" ';
		$content .= 'value="' . $nonce_value . '"';
		$content .= '/>';
		
		return $content;
	}
	
	private function get_start(
		string $id,
		string $name
	): string {
		$content = '<tr>';
		$content .= '<th style="width:20%"><label for="' . $id . '">' . $name . '</label></th>';
		$content .= '<td>';
		
		return $content;
	}
	
	private function get_input_text(
		string $id,
		string $current_value,
		string $default_value,
		string $desc
	): string {
		$content = '<input type="text" ';
		$content .= 'name="'. $id . '" ';
		$content .= 'id="' . $id . '" ';
		$content .= 'value="';
		$content .= $current_value ?? $default_value;
		$content .= '" ';
		$content .= 'size="30" ';
		$content .= 'style="width:97%" />';
		$content .= '<br />';
		$content .= $desc;

		return $content;
	}
	
	private function get_textarea(
		string $id,
		string $current_value,
		string $default_value,
		string $desc
	): string {
		$content = '<textarea name="' . $id . '" ';
		$content .= 'id="' . $id . '" ';
		$content .= 'cols="60" ';
		$content .= 'rows="4" ';
		$content .= 'style="width:97%">';
		$content .= $current_value ?: $default_value;
		$content .= '</textarea>';
		$content .= '<br />';
		$content .= $desc;

		return $content;
	}
	
	private function get_dropdown(
		string $id,
		array $options,
		string $current_value
	): string {
		$content = '<select name="' . $id . '" ';
		$content .= 'id="' . $id . '">';
		foreach ( $options as $option ) {
			$content .= '<option ';
			$content .= $current_value === $option ? ' selected="selected"' : '';
			$content .= '>';
			$content .= $option;
			$content .= '</option>';
		}
		$content .= '</select>';
		
		return $content;
	}
	
	private function get_radio_buttons(
		string $id,
		array $options,
		string $current_value		
	): string {
		$content = '';
		foreach ( $options as $option ) {
			$content .= '<input ';
			$content .= 'type="radio" ';
			$content .= 'name="' . $id . '" ';
			$content .= 'value="' . $option['value'] . '"';
			$content .= $current_value === $option['value'] ? ' checked="checked"' : '';
			$content .= ' />';
			$content .= $option['name'];
		}
		
		return $content;
	}
	
	private function get_checkbox(
		string $id,
		string $current_value
	): string {
		$content = '<input ';
		$content .= 'type="checkbox" ';
		$content .= 'name="' . $id . '" ';
		$content .= 'id="' . $id . '"';
		$content .= $current_value ? ' checked="checked"' : '';
		$content .= ' />';

		return $content;
	}

	private function get_end(): string {
		$content = '</td><td>';
		$content .= '</td></tr>';

		return $content;
	}
}