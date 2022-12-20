<?php

namespace Classes;

use function array_intersect;
use function wp_get_current_user;

class User {

	private const ALLOWED_ROLES = [
		'administrator',
		'editor'
	];

	/**
	 * User Is Allowed
	 * Returns true if the user is allowed to edit, otherwise returns false ( i.e. if the user is Author or below ).
	 * @return bool
	 */
	public function isAllowedToEdit(): bool {
		$userIsAllowed = false;
		$user = wp_get_current_user();
		if ( array_intersect(self::ALLOWED_ROLES, $user->roles )) {
			$userIsAllowed = true;
		}

		return $userIsAllowed;
	}
}