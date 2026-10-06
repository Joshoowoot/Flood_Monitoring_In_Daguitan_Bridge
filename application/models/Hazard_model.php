<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Hazard_model extends CI_Model {

	protected $data_file;

	public function __construct()
	{
		parent::__construct();
		$this->data_file = APPPATH . 'data' . DIRECTORY_SEPARATOR . 'hazard_pins.json';
	}

	public function types()
	{
		return array(
			'flood' => array('label' => 'Flood-prone', 'color' => '#dc2626'),
			'landslide' => array('label' => 'Landslide-prone', 'color' => '#ea580c'),
			'storm_surge' => array('label' => 'Storm-surge-prone', 'color' => '#2563eb'),
			'fire' => array('label' => 'Fire hazard', 'color' => '#7c3aed'),
		);
	}

	public function barangays()
	{
		return array(
			'Alegre' => 'Alegre',
			'Arado' => 'Arado',
			'Barbo' => 'Barbo (Poblacion)',
			'Batug' => 'Batug',
			'Bolongtohan' => 'Bolongtohan',
			'Bulod' => 'Bulod',
			'Buntay' => 'Buntay (Poblacion)',
			'Cabacungan' => 'Cabacungan',
			'Cabarasan' => 'Cabarasan',
			'Cabato-an' => 'Cabato-an',
			'Calipayan' => 'Calipayan',
			'Calubian' => 'Calubian',
			'Cambula District' => 'Cambula District (Poblacion)',
			'Camitoc' => 'Camitoc',
			'Camote' => 'Camote',
			'Candao' => 'Candao (Poblacion)',
			'Catmonan' => 'Catmonan (Poblacion)',
			'Combis' => 'Combis (Poblacion)',
			'Dacay' => 'Dacay',
			'Del Carmen' => 'Del Carmen',
			'Del Pilar' => 'Del Pilar',
			'Fatima' => 'Fatima',
			'General Roxas' => 'General Roxas',
			'Highway' => 'Highway (Poblacion)',
			'Luan' => 'Luan',
			'Magsaysay' => 'Magsaysay',
			'Maricum' => 'Maricum',
			'Market Site' => 'Market Site (Poblacion)',
			'Rawis' => 'Rawis',
			'Rizal' => 'Rizal',
			'Romualdez' => 'Romualdez',
			'Sabang Daguitan' => 'Sabang Daguitan',
			'Salvacion' => 'Salvacion',
			'San Agustin' => 'San Agustin',
			'San Antonio' => 'San Antonio',
			'San Isidro' => 'San Isidro',
			'San Jose' => 'San Jose',
			'San Miguel' => 'San Miguel (Poblacion)',
			'San Rafael' => 'San Rafael',
			'San Vicente' => 'San Vicente',
			'Serrano' => 'Serrano (Poblacion)',
			'Sungi' => 'Sungi (Poblacion)',
			'Tabu' => 'Tabu',
			'Tigbao' => 'Tigbao',
			'Victory' => 'Victory',
		);
	}

	public function all()
	{
		$data = $this->read_records();
		if ($data === FALSE)
		{
			return array();
		}

		$types = $this->types();
		$hazards = array();
		foreach ($data as $hazard)
		{
			if ( ! is_array($hazard) || ! isset($hazard['id'], $hazard['type'])
				|| ! is_string($hazard['type']) || ! isset($types[$hazard['type']]))
			{
				continue;
			}
			$hazard['type_label'] = $types[$hazard['type']]['label'];
			$hazard['color'] = $types[$hazard['type']]['color'];
			$hazards[] = $hazard;
		}
		return array_values($hazards);
	}

	public function find($id)
	{
		foreach ($this->all() as $hazard)
		{
			if ((int) $hazard['id'] === (int) $id)
			{
				return $hazard;
			}
		}
		return NULL;
	}

	public function save($input, $id = 0)
	{
		$types = $this->types();
		$barangays = $this->barangays();
		$name = trim((string) (isset($input['name']) ? $input['name'] : ''));
		$barangay = trim((string) (isset($input['barangay']) ? $input['barangay'] : ''));
		$description = trim((string) (isset($input['description']) ? $input['description'] : ''));
		$type = trim((string) (isset($input['type']) ? $input['type'] : ''));
		$latitude = isset($input['latitude']) ? $input['latitude'] : '';
		$longitude = isset($input['longitude']) ? $input['longitude'] : '';

		if ($name === '' || strlen($name) > 160)
		{
			return array('ok' => FALSE, 'error' => 'Enter a location name up to 160 characters.');
		}
		if ( ! isset($barangays[$barangay]))
		{
			return array('ok' => FALSE, 'error' => 'Choose one of the listed Dulag barangays.');
		}
		if (strlen($description) > 220)
		{
			return array('ok' => FALSE, 'error' => 'The description must be 220 characters or fewer.');
		}
		if ( ! isset($types[$type]))
		{
			return array('ok' => FALSE, 'error' => 'Choose a valid hazard type.');
		}
		if ( ! is_numeric($latitude) || ! is_numeric($longitude)
			|| (float) $latitude < 9 || (float) $latitude > 12
			|| (float) $longitude < 123 || (float) $longitude > 127)
		{
			return array('ok' => FALSE, 'error' => 'Choose a valid map location in the Dulag area.');
		}

		$stored_hazards = $this->read_records();
		if ($stored_hazards === FALSE)
		{
			return array('ok' => FALSE, 'error' => 'Hazard pin data could not be read. Please repair the data file before saving.');
		}
		$hazards = $this->all();
		$record = array(
			'id' => (int) $id,
			'name' => $name,
			'barangay' => $barangay,
			'description' => $description,
			'type' => $type,
			'latitude' => round((float) $latitude, 6),
			'longitude' => round((float) $longitude, 6),
			'active' => ! empty($input['active']),
		);

		if ($record['id'] > 0)
		{
			$found = FALSE;
			foreach ($hazards as $index => $hazard)
			{
				if ((int) $hazard['id'] === $record['id'])
				{
					$hazards[$index] = $record;
					$found = TRUE;
					break;
				}
			}
			if ( ! $found)
			{
				return array('ok' => FALSE, 'error' => 'This hazard pin no longer exists. Refresh the page and try again.');
			}
		}
		else
		{
			$ids = array_map(function ($hazard) { return (int) $hazard['id']; }, $hazards);
			$record['id'] = empty($ids) ? 1 : max($ids) + 1;
			$hazards[] = $record;
		}

		if ( ! $this->write($hazards))
		{
			return array('ok' => FALSE, 'error' => 'Could not save the hazard pin. Check that the application data folder is writable.');
		}
		return array('ok' => TRUE, 'record' => $record);
	}

	public function delete($id)
	{
		$remaining = array();
		$deleted = FALSE;
		foreach ($this->all() as $hazard)
		{
			if ((int) $hazard['id'] === (int) $id)
			{
				$deleted = TRUE;
				continue;
			}
			$remaining[] = $hazard;
		}
		return $deleted && $this->write($remaining);
	}

	protected function write($hazards)
	{
		$json = json_encode(array_values($hazards), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		if ($json === FALSE)
		{
			log_message('error', 'Hazard pins could not be encoded as JSON.');
			return FALSE;
		}
		return @file_put_contents($this->data_file, $json, LOCK_EX) !== FALSE;
	}

	protected function read_records()
	{
		if ( ! is_file($this->data_file))
		{
			return array();
		}

		$content = @file_get_contents($this->data_file);
		$data = ($content === FALSE) ? NULL : json_decode($content, TRUE);
		if ( ! is_array($data))
		{
			log_message('error', 'Hazard pin data could not be read as valid JSON.');
			return FALSE;
		}
		return $data;
	}
}
