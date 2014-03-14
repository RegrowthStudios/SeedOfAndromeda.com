<?php

class XenResource_ViewPublic_Helper_Resource
{
	/**
	 * Helper to fetch the title of a custom resource field from its ID
	 *
	 * @param string $field
	 *
	 * @return XenForo_Phrase
	 */
	public static function getResourceFieldTitle($fieldId)
	{
		return new XenForo_Phrase("resource_field_$fieldId");
	}

	/**
	 * Gets the HTML value of the resource field.
	 *
	 * @param array $resource
	 * @param array|string $field If string, field ID
	 * @param mixed $value Value of the field; if null, pulls from field_value in field
	 *
	 * @return string
	 */
	public static function getResourceFieldValueHtml(array $resource, $field, $value = null)
	{
		if (!is_array($field))
		{
			$fields = XenForo_Model::create('XenResource_Model_ResourceField')->getResourceFieldCache();
			if (!isset($fields[$field]))
			{
				return '';
			}

			$field = $fields[$field];
		}

		if (!XenForo_Application::isRegistered('view'))
		{
			return 'No view registered';
		}

		if ($value === null && isset($field['field_value']))
		{
			$value = $field['field_value'];
		}

		if ($value === '' || $value === null)
		{
			return '';
		}

		$multiChoice = false;
		$choice = '';
		$view = XenForo_Application::get('view');

		switch ($field['field_type'])
		{
			case 'radio':
			case 'select':
				$choice = $value;
				$value = new XenForo_Phrase("resource_field_$field[field_id]_choice_$value");
				$value->setPhraseNameOnInvalid(false);
				$valueRaw = $value;
				break;

			case 'checkbox':
			case 'multiselect':
				$multiChoice = true;
				if (!is_array($value) || count($value) == 0)
				{
					return '';
				}

				$newValues = array();
				foreach ($value AS $id => $choice)
				{
					$phrase = new XenForo_Phrase("resource_field_$field[field_id]_choice_$choice");
					$phrase->setPhraseNameOnInvalid(false);

					$newValues[$choice] = $phrase;
				}
				$value = $newValues;
				$valueRaw = $value;
				break;

			case 'bbcode':
				$valueRaw = htmlspecialchars(XenForo_Helper_String::censorString($value));

				$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $view)));
				$value = $bbCodeParser->render($value, array(
					'noFollowDefault' => empty($resource['isTrusted'])
				));
				break;

			case 'textbox':
			case 'textarea':
			default:
				$valueRaw = htmlspecialchars(XenForo_Helper_String::censorString($value));
				$value = XenForo_Template_Helper_Core::callHelper('bodytext', array($value));
		}

		if (!empty($field['display_template']))
		{
			if ($multiChoice && is_array($value))
			{
				foreach ($value AS $choice => &$thisValue)
				{
					$thisValue = strtr($field['display_template'], array(
						'{$fieldId}' => $field['field_id'],
						'{$value}' => $thisValue,
						'{$valueRaw}' => $thisValue,
						'{$valueUrl}' => urlencode($thisValue),
						'{$choice}' => $choice,
					));
				}
			}
			else
			{
				$value = strtr($field['display_template'], array(
					'{$fieldId}' => $field['field_id'],
					'{$value}' => $value,
					'{$valueRaw}' => $valueRaw,
					'{$valueUrl}' => urlencode($value),
					'{$choice}' => $choice,
				));
			}
		}

		if (is_array($value))
		{
			if (empty($value))
			{
				return '';
			}
			return '<ul class="plainList"><li>' . implode('</li><li>', $value) . '</li></ul>';
		}

		return $value;
	}

	public static function getResourcePrefixGroupTitle($prefixGroupId)
	{
		return new XenForo_Phrase('resource_prefix_group_' . $prefixGroupId);
	}

	public static function getResourcePrefixCache()
	{
		$prefixes = XenForo_Application::getSimpleCacheData('resourcePrefixes');
		return $prefixes ? $prefixes : array();
	}

	/**
	 * Helper to display a resource prefix for the specified prefix ID/resource. Can take an array.
	 *
	 * @param integer|array $prefixId Prefix ID or array with key of prefix_id
	 * @param string $outputType Type of output; options are html (marked up), plain (plain text), escaped (plain text escaped)
	 * @param string|null $append Value to append if there is a prefix (eg, a space); if null, defaults to space (html) or dash (plain)
	 *
	 * @return string
	 */
	public static function getResourcePrefixTitle($prefixId, $outputType = 'html', $append = null)
	{
		if (is_array($prefixId))
		{
			if (!isset($prefixId['prefix_id']))
			{
				return '';
			}

			$prefixId = $prefixId['prefix_id'];
		}

		$prefixId = intval($prefixId);
		$prefixes = self::getResourcePrefixCache();

		if (!$prefixId || !isset($prefixes[$prefixId]))
		{
			return '';
		}

		$text = new XenForo_Phrase('resource_prefix_' . $prefixId);
		$text = $text->render(false);
		if ($text === '')
		{
			return '';
		}

		switch ($outputType)
		{
			case 'html':
				$text = '<span class="' . htmlspecialchars($prefixes[$prefixId]) . '">'
					. htmlspecialchars($text) . '</span>';
				if ($append === null)
				{
					$append = ' ';
				}
				break;

			case 'plain':
				break; // ok as is

			case 'escaped':
			default:
				$text = htmlspecialchars($text); // just be safe and escape everything else
		}

		if ($append === null)
		{
			$append = ' - ';
		}

		return $text . $append;
	}

	public static function getResourceIconUrl(array $resource)
	{
		if (!empty($resource['icon_date']))
		{
			$group = floor($resource['resource_id'] / 1000);
			return XenForo_Application::$externalDataUrl . "/resource_icons/$group/$resource[resource_id].jpg?$resource[icon_date]";
		}

		if (!$imagePath = XenForo_Template_Helper_Core::styleProperty('imagePath'))
		{
			$imagePath = 'styles/default';
		}

		return "{$imagePath}/xenresource/resource_icon.png";
	}
}