<?php

class EWRporta_Model_Blocks extends XenForo_Model
{
	public function getAllBlocks()
	{
		return $this->fetchAllKeyed("SELECT * FROM EWRporta_blocks ORDER BY block_id ASC", 'block_id');
	}

	public function getBlockById($blockId)
	{
		if (!$block = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRporta_blocks
			WHERE block_id = ?
		", $blockId))
		{
			return false;
		}

		return $block;
	}

	public function getBlocks($getCookies = false, $layout1 = false, $layout2 = false, $layout3 = false)
	{
		if ($layout1)
		{
			if (
				($layout1 && $layout = $this->getModelFromCache('EWRporta_Model_Layouts')->getLayoutById($layout1)) ||
				($layout2 && $layout = $this->getModelFromCache('EWRporta_Model_Layouts')->getLayoutById($layout2)) ||
				($layout3 && $layout = $this->getModelFromCache('EWRporta_Model_Layouts')->getLayoutById($layout3))
			)
			{
				$layout = unserialize($layout['blocks']);
			}
			elseif ($layout1 != 'portal')
			{
				return array();
			}
		}

		$blocks = $this->fetchAllKeyed("
			SELECT *, 'disabled' AS position
				FROM EWRporta_blocks
			WHERE active = 1
			ORDER BY block_id ASC
		", 'block_id');

		if (!empty($layout))
		{
			$_blocks = array();

			foreach ($layout AS $key => $position)
			{
				if (!empty($blocks[$key]))
				{
					$_blocks[$key] = array('position' => $position) +  $blocks[$key];
				}
			}

			$blocks = $_blocks + $blocks;
		}

		if ($getCookies && $cookies = XenForo_Helper_Cookie::getCookie('EWRporta'))
		{
			$_blocks = array();

			foreach ($cookies AS $key => $cookie)
			{
				if (!empty($blocks[$key]) && !empty($cookie['position']))
				{
					$position = $blocks[$key]['locked'] ? $blocks[$key]['position'] : $cookie['position'];
					$_blocks[$key] = array('position' => $position) +  $blocks[$key];
				}
			}

			$blocks = $_blocks + $blocks;
		}

		return $blocks;
	}

	public function getBlockParams($block, $page = 1, $params = array())
	{
		$template = 'EWRporta_Block_'.$block['block_id'];

		if ($block['category'] || $page > 1)
		{
			$params['option'] = $block['options'];
			$params['page'] = $page;
			$params['category'] = $block['category'];
			$params['position'] = $block['position'];

			switch ($block['block_id'])
			{
				case 'RecentNews':
					$model = new $template;
					$params[$block['block_id']] = $model->getModule($params['option'], $page, $params['category']);
					return $params;
			}
		}

		if (XenForo_Application::autoload($template))
		{
			$model = new $template;
		}

		if (strtotime($block['cache'], $block['caches']['date']) < XenForo_Application::$time)
		{
			$params['option'] = $block['options'];

			if (isset($model))
			{
				if (method_exists($model, 'getModule'))
				{
					$params[$block['block_id']] = $model->getModule($params['option'], $page);
				}
			}

			if ($block['cache'] != 'now')
			{
				$this->getModelFromCache('EWRporta_Model_Caches')->buildBlockCache($block, serialize($params));
			}
		}
		else
		{
			$params = unserialize($block['caches']['results']);
		}

		if (isset($model))
		{
			if (method_exists($model, 'getBypass'))
			{
				$params['layout'] = $block['layout'];
				$params[$block['block_id']] = $model->getBypass($params);
			}
		}

		$params['position'] = $block['position'];
		return $params;
	}

	public function updateBlock($input, $originalId = false)
	{
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Blocks');
		if ($originalId && $this->getBlockById($originalId))
		{
			$dw->setExistingData($originalId);
		}
		$dw->bulkSet($input);
		$dw->save();

		XenForo_Db::commit($db);

		return $input;
	}

	public function canEditBlocks()
	{
		return XenForo_Application::debugMode();
	}

	public function installBlockXmlFromFile($fileName)
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
			throw new XenForo_Exception(new XenForo_Phrase('provided_file_was_not_valid_xml_file'), true);
		}

		return $this->installBlockXml($document);
	}

	public function installBlockXml(SimpleXMLElement $xml)
	{
		if ($xml->getName() != 'block')
		{
			throw new XenForo_Exception(new XenForo_Phrase('provided_file_is_not_a_block_xml_file'), true);
		}

		$blockData = array(
			'block_id' => (string)$xml['block_id'],
			'title' => (string)$xml['title'],
			'version_string' => (string)$xml['version_string'],
			'version_id' => (int)$xml['version_id'],
			'install_callback_class' => (string)$xml['install_callback_class'],
			'install_callback_method' => (string)$xml['install_callback_method'],
			'uninstall_callback_class' => (string)$xml['uninstall_callback_class'],
			'uninstall_callback_method' => (string)$xml['uninstall_callback_method'],
			'url' => (string)$xml['url'],
			'cache' => (string)$xml['cache'],
		);

		$existingBlock = $this->getBlockById($blockData['block_id']);

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		if ($blockData['install_callback_class'] && $blockData['install_callback_method'])
		{
			call_user_func(
				array($blockData['install_callback_class'], $blockData['install_callback_method']),
				$existingBlock,
				$blockData
			);
		}

		$blockDw = XenForo_DataWriter::create('EWRporta_DataWriter_Blocks');
		if ($existingBlock)
		{
			$blockDw->setExistingData($existingBlock, true);
			unset($blockData['cache']);
		}
		$blockDw->bulkSet($blockData);
		$blockDw->save();
		
		$this->getModelFromCache('EWRporta_Model_Templates')->importAdminTemplatesXml($xml->admin_templates, $blockData['block_id']);
		$this->getModelFromCache('EWRporta_Model_Templates')->importTemplatesXml($xml->templates, $blockData['block_id']);
		$this->getModelFromCache('EWRporta_Model_CodeEvents')->importListenersXml($xml->listeners, $blockData['block_id']);
		$this->getModelFromCache('EWRporta_Model_Options')->importOptionsXml($xml->options, $blockData['block_id']);
		$this->getModelFromCache('EWRporta_Model_Phrases')->importPhrasesXml($xml->phrases, $blockData['block_id']);
		$this->getModelFromCache('XenForo_Model_RoutePrefix')->importPrefixesAddOnXml($xml->route_prefixes, 'EWRblock_'.$blockData['block_id']);

		XenForo_Db::commit($db);

		return;
	}

	public function exportBlock($block)
	{
		$document = new DOMDocument('1.0', 'utf-8');
		$document->formatOutput = true;

		$rootNode = $document->createElement('block');
		$rootNode->setAttribute('block_id', $block['block_id']);
		$rootNode->setAttribute('title', $block['title']);
		$rootNode->setAttribute('version_string', $block['version_string']);
		$rootNode->setAttribute('version_id', $block['version_id']);
		$rootNode->setAttribute('url', $block['url']);
		$rootNode->setAttribute('install_callback_class', $block['install_callback_class']);
		$rootNode->setAttribute('install_callback_method', $block['install_callback_method']);
		$rootNode->setAttribute('uninstall_callback_class', $block['uninstall_callback_class']);
		$rootNode->setAttribute('uninstall_callback_method', $block['uninstall_callback_method']);
		$rootNode->setAttribute('cache', $block['cache']);
		$document->appendChild($rootNode);

		$dataNode = $rootNode->appendChild($document->createElement('admin_templates'));
		$this->getModelFromCache('EWRporta_Model_Templates')->appendAdminTemplatesXml($dataNode, $block['block_id']);

		$dataNode = $rootNode->appendChild($document->createElement('listeners'));
		$this->getModelFromCache('EWRporta_Model_CodeEvents')->appendListenerXml($dataNode, $block['block_id']);

		$dataNode = $rootNode->appendChild($document->createElement('options'));
		$this->getModelFromCache('EWRporta_Model_Options')->appendOptionsXml($dataNode, $block['block_id']);

		$dataNode = $rootNode->appendChild($document->createElement('phrases'));
		$this->getModelFromCache('EWRporta_Model_Phrases')->appendPhrasesXml($dataNode, $block['block_id']);

		$dataNode = $rootNode->appendChild($document->createElement('route_prefixes'));
		$this->getModelFromCache('XenForo_Model_RoutePrefix')->appendPrefixesAddOnXml($dataNode, 'EWRblock_'.$block['block_id']);

		$dataNode = $rootNode->appendChild($document->createElement('templates'));
		$this->getModelFromCache('EWRporta_Model_Templates')->appendTemplatesXml($dataNode, $block['block_id']);

		return $document;
	}

	public function uninstallBlock($block)
	{
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		$dw = XenForo_DataWriter::create('EWRporta_DataWriter_Blocks');
		$dw->setExistingData($block);
		$dw->delete();

		$this->getModelFromCache('EWRporta_Model_Caches')->deleteCacheByBlock($block['block_id']);
		$this->getModelFromCache('EWRporta_Model_Options')->deleteOptionsByBlock($block['block_id']);
		$this->getModelFromCache('EWRporta_Model_Phrases')->deletePhrasesByBlock($block['block_id']);
		$this->getModelFromCache('EWRporta_Model_Templates')->deleteTemplatesByBlock($block['block_id']);
		$this->getModelFromCache('EWRporta_Model_CodeEvents')->deleteListenersByBlock($block['block_id']);
		$this->getModelFromCache('XenForo_Model_RoutePrefix')->deletePrefixesForAddOn('EWRblock_'.$block['block_id']);

		XenForo_Db::commit($db);

		return true;
	}
}