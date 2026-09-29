<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Evacuation_center_model extends CI_Model {

	protected $data_file;

	public function __construct()
	{
		parent::__construct();
		$this->data_file = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'evacuation_centers.json';
	}

	public function all()
	{
		if ( ! is_file($this->data_file))
		{
			return array();
		}
		$data = json_decode((string) @file_get_contents($this->data_file), TRUE);
		return is_array($data) ? array_values($data) : array();
	}

	public function find($id)
	{
		foreach ($this->all() as $center)
		{
			if ((int) $center['id'] === (int) $id)
			{
				return $center;
			}
		}
		return NULL;
	}

	public function save($input, $id = 0)
	{
		$centers = $this->all();
		$record = array(
			'id'       => (int) $id,
			'name'     => trim((string) (isset($input['name']) ? $input['name'] : '')),
			'barangay' => trim((string) (isset($input['barangay']) ? $input['barangay'] : '')),
			'address'  => trim((string) (isset($input['address']) ? $input['address'] : '')),
			'latitude' => (float) (isset($input['latitude']) ? $input['latitude'] : 0),
			'longitude'=> (float) (isset($input['longitude']) ? $input['longitude'] : 0),
			'capacity' => trim((string) (isset($input['capacity']) ? $input['capacity'] : 'Confirm with MDRRMO')),
			'phone'    => trim((string) (isset($input['phone']) ? $input['phone'] : '053 325 0000')),
			'active'   => ! isset($input['active']) || ! empty($input['active']),
		);
		if ($record['id'] < 1)
		{
			$ids = array_map(function ($item) { return (int) $item['id']; }, $centers);
			$record['id'] = empty($ids) ? 1 : max($ids) + 1;
		}
		$updated = FALSE;
		foreach ($centers as $index => $center)
		{
			if ((int) $center['id'] === $record['id'])
			{
				$centers[$index] = $record;
				$updated = TRUE;
				break;
			}
		}
		if ( ! $updated)
		{
			$centers[] = $record;
		}
		return $this->write($centers) ? $record : NULL;
	}

	public function delete($id)
	{
		$remaining = array();
		$deleted = FALSE;
		foreach ($this->all() as $center)
		{
			if ((int) $center['id'] === (int) $id)
			{
				$deleted = TRUE;
				continue;
			}
			$remaining[] = $center;
		}
		return $deleted && $this->write($remaining);
	}

	protected function write($centers)
	{
		return @file_put_contents($this->data_file, json_encode(array_values($centers), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== FALSE;
	}
}
