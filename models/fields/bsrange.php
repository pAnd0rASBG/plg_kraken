<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldBSRange extends JFormField {
	
	protected $type = 'BSRange';

	// getLabel() left out

	public function getInput() {
		JHtml::script('plg_kraken/bootstrap-slider.min.js', FALSE, TRUE);
		JHtml::stylesheet('plg_kraken/bootstrap-slider.min.css', array(), TRUE);
		$value = empty($this->value) ? $this->default : $this->value;

		$field = '
		<input id="'.$this->id.'" name="'.$this->name.'" data-slider-id="bsslider" type="text" data-slider-min="1'.$this->min.'" data-slider-max="100'.$this->max.'" data-slider-step="1'.$this->step.'" data-slider-value="'.$value.'"/>
		<script>
		var bsslider = new Slider("#'.$this->id.'", {
			tooltip: \'always\'
		});
		</script>
		';
		return $field;
	}

    protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		// Initialize some field attributes.
		$extraData = array(
			'max' => $this->max,
			'min' => $this->min,
			'step' => $this->step,
		);

		return array_merge($data, $extraData);
	}

}