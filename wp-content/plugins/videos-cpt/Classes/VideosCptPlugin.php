<?php

namespace Classes;

use function flush_rewrite_rules;
use function unregister_post_type;

class VideosCptPlugin {

	public function __construct() {
		return $this;
	}

	public function activate(): void {
		// Clear the permalinks after the post type has been registered.
		flush_rewrite_rules();
	}

	public function deactivate(): void {
		// Unregister the Videos CPT
		unregister_post_type('videos');
		// Clear the permalinks to remove the Video CPT's rules from the database
		flush_rewrite_rules();
	}
}