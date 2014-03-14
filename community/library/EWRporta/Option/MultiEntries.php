<?php

class EWRporta_Option_MultiEntries
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$values = $preparedOption['option_value'];

		$entries = array();
		if (!empty($values))
		{
			foreach ($values AS $value)
			{
				$entries[] = $value;
			}
		}

		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));

		return $view->createTemplateObject('option_template_multiEntries_EWRporta', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'formatParams' => $preparedOption['formatParams'],
			'editLink' => $editLink,
			'entries' => $entries,
			'nextCounter' => count($entries)
		));
	}

	public static function verifyOption(array &$options, XenForo_DataWriter $dw, $fieldName)
	{
		foreach ($options AS $key => &$option)
		{
			if (!$option)
			{
				unset($options[$key]);
			}
		}

		return true;
	}
}