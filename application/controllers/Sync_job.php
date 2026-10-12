<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Sync_job extends CI_Controller {

	public function run()
	{
		if ( ! $this->input->is_cli_request())
		{
			show_404();
			return;
		}

		$this->load->model('Sync_model');
		$result = $this->Sync_model->flush();
		echo $result['message'] . PHP_EOL;
		if ( ! empty($result['ok']))
		{
			exit(0);
		}
		exit(1);
	}
}
