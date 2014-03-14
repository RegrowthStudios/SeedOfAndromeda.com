<?php

class EWRporta_Model_Options extends XenForo_Model
{
	public function getOptions()
	{
		$options = $this->_getDb()->fetchAll("SELECT * FROM EWRporta_options");

		$_options = array();
		foreach ($options AS &$option)
		{
			if ($option['data_type'] == 'array')
			{
				$option['option_value'] = @unserialize($option['option_value']);
			}

			$option['shortName'] = str_ireplace($option['block_id'].'_', '', $option['option_id']);
			$_options[$option['block_id']][$option['shortName']] = $option['option_value'];
		}

		return $_options;
	}

	public function getOptionById($optionId)
	{
		if (!$option = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_options
			WHERE option_id = ?
		", $optionId))
		{
			return false;
		}

		return $option;
	}

	public function getOptionsByBlock($blockId)
	{
		return $this->fetchAllKeyed('
			SELECT *
				FROM EWRporta_options
			WHERE block_id = ?
			ORDER by display_order
		', 'option_id', $blockId);
	}

	public function deleteOptionsByBlock($blockId)
	{
		$db = $this->_getDb();
		$db->delete('EWRporta_options', 'block_id = ' . $db->quote($blockId));

		return;
	}

	public function prepareOptions($options)
	{
		foreach ($options AS &$option)
		{
			$option['formatParams'] = $this->getModelFromCache('XenForo_Model_Option')->prepareOptionFormatParams($option['edit_format'], $option['edit_format_params']);
			if ($option['data_type'] == 'array')
			{
				$option['option_value'] = @unserialize($option['option_value']);
				if (!is_array($option['option_value']))
				{
					$option['option_value'] = array();
				}
			}
		}

		return $options;
	}

	public function updateOption($input, $originalId = false)
	{
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Options');
		if ($originalId && $this->getOptionById($originalId))
		{
			$dw->setExistingData($originalId);
			unset($input['option_value']);
		}
		$dw->bulkSet($input);
		$dw->save();

		XenForo_Db::commit($db);

		return $input;
	}

	public function updateOptions($input)
	{
		$dbOptions = $this->getOptionsByBlock($input['block_id']);

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		foreach ($dbOptions AS $dbOption)
		{
			$newValue = $input['options'][$dbOption['option_id']];

			if (is_array($newValue))
			{
				$newValue = serialize($newValue);
			}

			$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Options');
			$dw->setExistingData($dbOption, true);
			$dw->set('option_value', $newValue);
			$dw->save();
		}

		$dw2 = XenForo_DataWriter::create('EWRporta_DataWriter_Blocks');
		$dw2->setExistingData($input);
		$dw2->set('display', $input['display']);
		$dw2->set('cache', $input['cache']);
		$dw2->set('locked', $input['locked']);
		$dw2->set('groups', implode(',', $input['groups']));
		$dw2->save();

		XenForo_Db::commit($db);

		return $input;
	}

	public function importOptionsXml(SimpleXMLElement $xml, $blockId)
	{
		$options = $this->getOptionsByBlock($blockId);
		
		foreach ($xml->option AS $option)
		{
			$optionId = (string)$option['option_id'];
		
			$input = array(
				'option_id' => $optionId,
				'block_id' => $blockId,
				'title' => (string)$option->title,
				'explain' => (string)$option->explain,
				'edit_format' => (string)$option['edit_format'],
				'edit_format_params' => (string)$option->edit_format_params,
				'data_type' => (string)$option['data_type'],
				'sub_options' => (string)$option->sub_options,
				'validation_class' => (string)$option['validation_class'],
				'validation_method' => (string)$option['validation_method'],
				'display_order' => (int)$option['display_order'],
				'option_value' => (string)$option->option_value
			);

			$this->updateOption($input, $option['option_id']);
			unset($options[$optionId]);
		}
		
		foreach ($options AS $option)
		{
			$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Options');
			$dw->setExistingData($option);
			$dw->delete();
		}

		return;
	}

	public function appendOptionsXml(DOMElement $rootNode, $blockId)
	{
		$document = $rootNode->ownerDocument;

		$options = $this->getOptionsByBlock($blockId);
		foreach ($options AS $option)
		{
			$optionNode = $document->createElement('option');
			$optionNode->setAttribute('option_id', $option['option_id']);
			$optionNode->setAttribute('edit_format', $option['edit_format']);
			$optionNode->setAttribute('data_type', $option['data_type']);
			$optionNode->setAttribute('display_order', $option['display_order']);

			if ($option['validation_class'])
			{
				$optionNode->setAttribute('validation_class', $option['validation_class']);
				$optionNode->setAttribute('validation_method', $option['validation_method']);
			}

			XenForo_Helper_DevelopmentXml::createDomElements($optionNode, array(
				'option_value' => str_replace("\r\n", "\n", $option['option_value']),
				'edit_format_params' => str_replace("\r\n", "\n", $option['edit_format_params']),
				'sub_options' => str_replace("\r\n", "\n", $option['sub_options']),
				'title' => str_replace("\r\n", "\n", $option['title'])
			));

			$explainNode = $optionNode->appendChild($document->createElement('explain'));
			$explainNode->appendChild($document->createCDATASection($option['explain']));

			$rootNode->appendChild($optionNode);
		}
	}
}