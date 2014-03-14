<?php

class EWRporta_Option_StyleChooser
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$value = $preparedOption['option_value'];

		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));

		$styleModel = self::_getStyleModel();

		$styleOptions = $styleModel->getStylesForOptionsTag(
			$value['style']
		);

		return $view->createTemplateObject('option_template_styleChooser_EWRporta', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'formatParams' => $styleOptions,
			'editLink' => $editLink
		));
	}

	public static function verifyOption(array &$options, XenForo_DataWriter $dw, $fieldName)
	{
		if (!empty($options['force']))
		{
			echo "FORCED!"; exit;
		}

		echo "NOT FORCED!"; exit;
	}

	protected static function _getStyleModel()
	{
		return XenForo_Model::create('XenForo_Model_Style');
	}
}