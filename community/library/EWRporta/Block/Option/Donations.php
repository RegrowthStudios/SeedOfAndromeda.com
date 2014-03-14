<?php

class EWRporta_Block_Option_Donations
{
	public static function renderOption(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
	{
		$values = $preparedOption['option_value'];

		$donations = array();
		if (!empty($values))
		{
			foreach ($values AS $value)
			{
				$donations[] = $value;
			}
		}

		$editLink = $view->createTemplateObject('option_list_option_editlink', array(
			'preparedOption' => $preparedOption,
			'canEditOptionDefinition' => $canEdit
		));

		return $view->createTemplateObject('EWRblock_Donations_option', array(
			'fieldPrefix' => $fieldPrefix,
			'listedFieldName' => $fieldPrefix . '_listed[]',
			'preparedOption' => $preparedOption,
			'formatParams' => $preparedOption['formatParams'],
			'editLink' => $editLink,
			'donations' => $donations,
			'nextCounter' => count($donations)
		));
	}

	public static function verifyOption(array &$options, XenForo_DataWriter $dw, $fieldName)
	{
		foreach ($options AS $key => &$option)
		{
			$option['goal'] = max(0, intval($option['goal']));
			$option['id'] = preg_replace('#[^\w]#i', '', $option['id']);

			if (!$option['id'])
			{
				unset($options[$key]);
			}

			if (!$option['email'])
			{
				$option['email'] = XenForo_Application::get('options')->payPalPrimaryAccount;
			}
		}

		return true;
	}
}