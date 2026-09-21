<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Default accounts are seeded into the users table if it is empty.
| Change these before first login in production.
*/
$config['auth_accounts'] = array(
	array(
		'username' => 'MDRRMO_DULAG',
		'password' => 'One Dulag',
		'role'     => 'admin',
		'name'     => 'MDRRMO Dulag',
	),
	array(
		'username' => 'John Rouque B. Abina',
		'password' => 'John123!',
		'role'     => 'user',
		'name'     => 'John Rouque B. Abina',
	),
);
