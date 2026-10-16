<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('maintain_admin_session'))
{
	function maintain_admin_session($ci)
	{
		if ($ci->session->userdata('auth_role') !== 'admin')
		{
			return;
		}

		$last_activity = (int) $ci->session->userdata('auth_last_activity');
		if ($last_activity < 1 || $last_activity < time() - 7200)
		{
			$ci->session->unset_userdata(array(
				'auth_user',
				'auth_name',
				'auth_role',
				'auth_phone',
				'auth_barangay',
				'auth_uid',
				'auth_id',
				'auth_last_activity',
			));
			return;
		}

		$ci->session->set_userdata('auth_last_activity', time());
	}
}
