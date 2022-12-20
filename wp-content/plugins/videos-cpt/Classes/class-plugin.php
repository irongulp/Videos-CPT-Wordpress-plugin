<?php

namespace Classes;

use function flush_rewrite_rules;
use function remove_menu_page;
use function unregister_post_type;

class Plugin implements Constants {
	public function __construct(
		private readonly CustomPostType $custom_post_type,
		private readonly User $user
	) { }

	public function activatePlugin(): void {
		$this->custom_post_type->register();
		// Clear the permalinks after the post type has been registered.
		flush_rewrite_rules();
	}

	public function deactivatePlugin(): void {
		unregister_post_type( 'videos' );
		// Clear the permalinks to remove this plugin's rules from the database
		flush_rewrite_rules();
	}

	public function hideMenuItemFromAuthors(): void {
		if ( !$this->user->isAllowedToEdit() ) {
			remove_menu_page( 'edit.php?post_type=' . self::CPT_NAME );
		}
	}
}