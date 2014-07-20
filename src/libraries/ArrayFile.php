<?php namespace Regulus\Fractal;

class ArrayFile {

	/**
	 * The current data of the PHP array file.
	 *
	 * @var    string
	 */
	protected $data;

	/**
	 * The current number of levels deep into the array.
	 *
	 * @var    integer
	 */
	protected $arrayLevelsDeep = 0;

	/**
	 * Create an array file.
	 *
	 * @param  array    $array
	 * @return ArrayFile
	 */
	public static function create($array = array())
	{
		$file = new static;

		$file->data = "<?php";

		$file->addDataToArray(null, $array);

		return $file;
	}

	/**
	 * Create and save an array file.
	 *
	 * @param  string   $path
	 * @param  array    $array
	 * @return string
	 */
	public static function save($path, $array = array())
	{
		$file = static::create($array);

		if (substr($path, -1) == "/")
			$path .= "file";

		if (substr($path, -4) != ".php")
			$path .= ".php";

		if (!is_file($path)) {
			$fp = fopen($path, 'w+');
			fwrite($fp, $file->getData());
			fclose($fp);
			chmod($path, 0777);
		} else {
			file_put_contents($path, $file->getData());
		}


		return $path;
	}

	/**
	 * Get the data of a PHP array file.
	 *
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Add the current item to the array file data.
	 *
	 * @param  array    $item
	 * @param  array    $data
	 * @param  boolean  $associative
	 * @param  integer  $maxNameLength
	 * @return void
	 */
	private function addDataToArray($item, $data, $associative = true, $maxNameLength = 1)
	{
		$this->data .= "\n";

		if (is_array($data) || is_object($data))
			$this->data .= "\n";

		$this->addTabsToArray();

		if ($associative && !is_null($item)) {
			$this->data .= "'".$item."'";

			for ($s = 0; $s <= $maxNameLength - strlen($item); $s++)
				$this->data .= " ";

			$this->data .= "=> ";
		}

		if (is_array($data) || is_object($data)) {
			if (is_object($data))
				$data = (array) $data;

			if (is_null($item))
				$this->data .= "return array(\n";
			else
				$this->data .= "[";

			if (!empty($data)) {
				$this->arrayLevelsDeep ++;

				$associative2 = array_keys($data) !== range(0, count($data) - 1);

				$maxNameLength = 1;
				if ($associative2) {
					foreach ($data as $setting2 => $data2) {
						if (strlen($setting2) > $maxNameLength)
							$maxNameLength = strlen($setting2);
					}
				}

				foreach ($data as $setting2 => $data2)
					$this->addDataToArray($setting2, $data2, $associative2, $maxNameLength);

				$this->data .= "\n";

				if (is_array(end($data)) || is_object(end($data)))
					$this->data .= "\n";

				$this->arrayLevelsDeep --;

				$this->addTabsToArray();
			}

			if (is_null($item))
				$this->data .= "\n); //Exported from DB on ".date('m/d/Y \a\t g:ia');
			else
				$this->data .= "],\n";

		} else {
			if (is_bool($data)) {
				$this->data .= $data ? "true" : "false";
			} else if (is_int($data) || is_float($data)) {
				$this->data .= $data;
			} else {
				$this->data .= "'".str_replace("'", "\'", $data)."'";
			}

			$this->data .= ",";
		}
	}

	/**
	 * Add the correct number of tabs for the current array depth.
	 *
	 * @return void
	 */
	private function addTabsToArray()
	{
		for ($l = 1; $l <= $this->arrayLevelsDeep; $l++)
			$this->data .= "\t";
	}

}