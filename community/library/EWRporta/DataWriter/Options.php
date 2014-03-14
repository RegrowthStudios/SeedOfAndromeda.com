<?php

class EWRporta_DataWriter_Options extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	protected function _getFields()
	{
		return array(
			'EWRporta_options' => array(
				'option_id'				=> array('type' => self::TYPE_STRING,	'required' => true, 'verification' => array('$this', '_verifyOptionId')),
				'title'					=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => ''),
				'explain'				=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'option_value'			=> array('type' => self::TYPE_UNKNOWN,	'required' => false, 'default' => ''),
				'edit_format'			=> array('type' => self::TYPE_STRING,	'required' => true,
					'allowedValues' => array('textbox', 'spinbox', 'onoff', 'radio', 'select', 'checkbox', 'template', 'callback')
				),
				'edit_format_params'	=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'data_type'				=> array('type' => self::TYPE_STRING,	'required' => true,
					'allowedValues' => array('string', 'integer', 'numeric', 'array', 'boolean', 'positive_integer', 'unsigned_integer', 'unsigned_numeric')
				),
				'sub_options'			=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'validation_class'		=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'validation_method'		=> array('type' => self::TYPE_STRING,	'required' => false, 'default' => ''),
				'display_order'			=> array('type' => self::TYPE_UINT,		'required' => false, 'default' => 0),
				'block_id'				=> array('type' => self::TYPE_STRING,	'required' => true, 'default' => '')
			)
		);
	}

	protected function _getExistingData($data)
	{
		if (!$optionId = $this->_getExistingPrimaryKey($data, 'option_id'))
		{
			return false;
		}

		return array('EWRporta_options' => $this->getModelFromCache('EWRporta_Model_Options')->getOptionById($optionId));
	}

	protected function _getUpdateCondition($tableName)
	{
		return 'option_id = ' . $this->_db->quote($this->getExisting('option_id'));
	}

	protected function _verifyOptionId($optionId)
	{
		if (preg_match('/[^a-zA-Z0-9_]/', $optionId))
		{
			$this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'option_id');
			return false;
		}

		if ($optionId !== $this->getExisting('option_id'))
		{
			$existing = $this->getModelFromCache('EWRporta_Model_Options')->getOptionById($optionId);
			if ($existing)
			{
				$this->error(new XenForo_Phrase('option_ids_must_be_unique'), 'option_id');
				return false;
			}
		}

		return true;
	}

	protected function _preSave()
	{
		if ($this->isChanged('option_value'))
		{
			$optionValue = $this->_validateOptionValuePreSave($this->get('option_value'));

			if ($optionValue === false)
			{
				$this->error(new XenForo_Phrase('please_enter_valid_value_for_this_option'), $this->get('option_id'), false);
			}
			else
			{
				$this->_setInternal('EWRporta_options', 'option_value', $optionValue);
			}
		}
	}

	protected function _validateValidationClassAndMethod($class, $method)
	{
		if ($class && !XenForo_Application::autoload($class))
		{
			$this->error(new XenForo_Phrase('callback_class_x_for_option_y_is_not_valid', array('option' => $this->get('option_id'), 'class' => $class)), 'validation');
			return false;
		}

		return true;
	}

	protected function _validateOptionValuePreSave($optionValue)
	{
		switch ($this->get('data_type'))
		{
			case 'string':  $optionValue = strval($optionValue); break;
			case 'integer': $optionValue = intval($optionValue); break;
			case 'numeric': $optionValue = strval($optionValue) + 0; break;
			case 'boolean': $optionValue = ($optionValue ? 1 : 0); break;

			case 'array':
				if (!is_array($optionValue))
				{
					$unserialized = @unserialize($optionValue);
					if (is_array($unserialized))
					{
						$optionValue = $unserialized;
					}
					else
					{
						$optionValue = array();
					}
				}
				break;

			case 'unsigned_integer':
				$optionValue = max(0, intval($optionValue));
				break;

			case 'unsigned_numeric':
				$optionValue = max(0, (strval($optionValue) + 0));
				break;

			case 'positive_integer':
				$optionValue = max(1, intval($optionValue));
				break;
		}

		$validationClass = $this->get('validation_class');
		$validationMethod = $this->get('validation_method');

		if ($validationClass && $validationMethod && $this->_validateValidationClassAndMethod($validationClass, $validationMethod))
		{
			$success = (boolean)call_user_func_array(
				array($validationClass, $validationMethod),
				array(&$optionValue, $this, $this->get('option_id'))
			);
			if (!$success)
			{
				return false;
			}
		}

		if (is_array($optionValue))
		{
			if ($this->get('data_type') != 'array')
			{
				$this->error(new XenForo_Phrase('only_array_data_types_may_be_represented_as_array_values'), 'data_type');
			}
			else
			{
				$subOptions = preg_split('/(\r\n|\n|\r)+/', trim($this->get('sub_options')), -1, PREG_SPLIT_NO_EMPTY);
				$newOptionValue = array();
				$allowAny = false;

				foreach ($subOptions AS $subOption)
				{
					if ($subOption == '*')
					{
						$allowAny = true;
					}
					else if (!isset($optionValue[$subOption]))
					{
						$newOptionValue[$subOption] = false;
					}
					else
					{
						$newOptionValue[$subOption] = $optionValue[$subOption];
						unset($optionValue[$subOption]);
					}
				}

				if ($allowAny)
				{
					// allow any keys, so bring all the remaining ones over
					$newOptionValue += $optionValue;
				}
				else if (count($optionValue) > 0)
				{
					$this->error(new XenForo_Phrase('following_sub_options_unknown_x', array('subOptions' => implode(', ', array_keys($optionValue)))), 'sub_options');
				}

				$optionValue = $newOptionValue;
			}

			$optionValue = serialize($optionValue);
		}

		return strval($optionValue);
	}
}