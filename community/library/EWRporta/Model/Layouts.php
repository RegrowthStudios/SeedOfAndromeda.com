<?php

class EWRporta_Model_Layouts extends XenForo_Model
{
	public function getLayouts()
	{
		return $this->fetchAllKeyed("SELECT * FROM EWRporta_layouts ORDER BY layout_id ASC", 'layout_id');
	}

	public function getLayoutById($layoutId)
	{
		if (!$layout = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_layouts
			WHERE layout_id = ?
		", $layoutId))
		{
			return false;
		}

		return $layout;
	}

	public function updateLayout($input)
	{
		foreach ($input['blocks'] AS $key => $position)
		{
			if ($position == 'disabled')
			{
				unset($input['blocks'][$key]);
			}
		}

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Layouts');
		if ($this->getLayoutById($input['layout_id']))
		{
			$dw->setExistingData($input['layout_id']);
		}
		else
		{
			$dw->set('layout_id', $input['layout_id']);
		}
		$dw->set('blocks', serialize($input['blocks']));
		$dw->save();

		XenForo_Db::commit($db);

		return $input;
	}

	public function installLayoutXmlFromFile($fileName)
	{
		if (!file_exists($fileName) || !is_readable($fileName))
		{
			throw new XenForo_Exception(new XenForo_Phrase('please_enter_valid_file_name_requested_file_not_read'), true);
		}

		try
		{
			$document = new SimpleXMLElement($fileName, 0, true);
		}
		catch (Exception $e)
		{
			throw new XenForo_Exception(
				new XenForo_Phrase('provided_file_was_not_valid_xml_file'), true
			);
		}

		return $this->installLayoutXml($document);
	}

	public function installLayoutXml(SimpleXMLElement $xml)
	{
		if ($xml->getName() != 'layout')
		{
			throw new XenForo_Exception(new XenForo_Phrase('provided_file_is_not_a_layout_xml_file'), true);
		}

		$layout = array(
			'layout_id' => (string)$xml['layout_id']
		);

		foreach ($xml->blocks->block AS $block)
		{
			$blockId = (string)$block['block_id'];
			$layout['blocks'][$blockId] = (string)$block;
		}

		return $this->updateLayout($layout);
	}

	public function exportLayout($layout)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('layout');
		$rootNode->setAttribute('layout_id', $layout['layout_id']);
		$document->appendChild($rootNode);

		$dataNode = $rootNode->appendChild($document->createElement('blocks'));

		$blocks = unserialize($layout['blocks']);

		foreach ($blocks AS $key => $block)
		{
			$blockNode = $document->createElement('block', $block);
			$blockNode->setAttribute('block_id', $key);
			$dataNode->appendChild($blockNode);
		}

		return $document;
	}

	public function resetLayout($layout)
	{
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Layouts');
		$dw->setExistingData($layout);
		$dw->delete();

		XenForo_Db::commit($db);

		return true;
	}
}