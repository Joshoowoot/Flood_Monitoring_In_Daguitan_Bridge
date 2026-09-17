<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Default accounts are seeded into the users table if it is empty.
| Change these before first login in production.
*/
$config['auth_accounts'] = array(
	array(
		'username' => 'admin',
		'password' => 'DulagAdmin2026!',
		'role'     => 'admin',
		'name'     => 'MDRRMO Administrator',
	),
	array(
		'username' => 'resident',
		'password' => 'DulagUser2026!',
		'role'     => 'user',
		'name'     => 'Community Resident',
	),
);
