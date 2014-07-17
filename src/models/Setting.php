<?php namespace Regulus\Fractal;

use Illuminate\Database\Eloquent\Model as Eloquent;

use Illuminate\Support\Facades\Config;

use Aquanode\Formation\Formation as Form;

class Setting extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var    string
	 */
	protected $table = 'settings';

	/**
	 * Get the HTML field for a setting.
	 *
	 * @return string
	 */
	public function getField()
	{
		$name = $this->getFieldName();

		if ($this->type == "Boolean") {
			$options = $this->options != "" ? explode(', ', $this->options) : ['Yes', 'No'];

			$html = Form::field($name, 'radio-set', array(
				'label'   => $this->getLabel(),
				'options' => Form::booleanOptions($options),
				'value'   => (int) $this->value,
			));
		} else if ($this->type == "Integer") {
			$range   = true;
			$options = explode(':', $this->options);
			if (count($options) == 1) {
				$range   = false;
				$options = explode(', ', $this->options);
			}

			if (count($options) > 1) {
				if ($range) {
					$optionsAdditional = explode('; +', $options[1]);

					$html = Form::field($name, 'select', array(
						'label'   => $this->getLabel(),
						'options' => Form::numberOptions($options[0], $optionsAdditional[0], (isset($optionsAdditional[1]) ? $optionsAdditional[1] : 1)),
						'value'   => $this->value,
					));
				} else {
					$html = Form::field($name, 'select', array(
						'label'   => $this->getLabel(),
						'options' => Form::simpleOptions($options),
						'value'   => $this->value,
					));
				}
			} else {
				$html = Form::field($name, 'number', array('label' => $this->getLabel(), 'value' => $this->value));
			}
		} else {
			if ($this->options != "") {
				$options = explode(', ', $this->options);

				//options is a method; call it to get actual options
				if (count($options) == 1 && strpos($options[0], '::') !== false) {
					$function = explode('::', $options[0]);
					$class    = $function[0];
					$method   = substr($function[1], 0, (strlen($function[1]) - 2));
					$options  = $class::{$method}();
				}

				$html = Form::field($name, 'select', array(
					'label'   => $this->getLabel(),
					'options' => Form::simpleOptions($options),
					'value'   => $this->value,
				));
			} else {
				$html = Form::field($name, 'text', array('label' => $this->getLabel()));
			}
		}

		return $html;
	}

	/**
	 * Get the HTML field name for a setting.
	 *
	 * @return string
	 */
	public function getFieldName()
	{
		return str_replace(' ', '_', strtolower($this->name));
	}

	/**
	 * Get the label for a setting.
	 *
	 * @return string
	 */
	public function getLabel()
	{
		if ($this->label == "")
			return $this->name;

		return $this->label;
	}

	/**
	 * Get the value of a setting by name.
	 *
	 * @param  string   $name
	 * @param  mixed    $default
	 * @return mixed
	 */
	public static function value($name, $default = false)
	{
		$setting = static::where('name', '=', $name)->first();
		if (empty($setting)) return $default;

		if ($setting->type == "List")
			return explode(', ', $setting->value);

		return $setting->value;
	}

}