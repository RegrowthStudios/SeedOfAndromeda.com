<?php

class EWRporta_Model_Templates extends XenForo_Model
{
	public function getTemplatesByBlock($blockId, $styleId = 0)
	{
		$title = 'EWRblock_'.$blockId;

		return $this->fetchAllKeyed('
			SELECT *
				FROM xf_template
			WHERE title LIKE ?
				AND style_id = ?
		', 'title', array($title.'%', $styleId));
	}

	public function getAdminTemplatesByBlock($blockId)
	{
		$title = 'EWRblock_'.$blockId;

		return $this->fetchAllKeyed('
			SELECT *
				FROM xf_admin_template
			WHERE title LIKE ?
		', 'title', $title.'%');
	}

	public function deleteTemplatesByBlock($blockId)
	{
		$title = 'EWRblock_'.$blockId;

		$db = $this->_getDb();
		$db->delete('xf_template', 'style_id = 0 AND title LIKE ' . $db->quote($title.'%'));
		$db->delete('xf_admin_template', 'title LIKE ' . $db->quote($title.'%'));

		return;
	}

	public function importTemplatesXml(SimpleXMLElement $xml, $blockId)
	{
		$existingTemplates = $this->getTemplatesByBlock($blockId);

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		foreach ($xml->template AS $template)
		{
			$templateName = (string)$template['title'];

			$dw = XenForo_DataWriter::create('XenForo_DataWriter_Template');
			if (isset($existingTemplates[$templateName]))
			{
				$dw->setExistingData($existingTemplates[$templateName], true);
			}
			$dw->bulkSet(array(
				'style_id' => '0',
				'title' => $templateName,
				'template' => XenForo_Helper_DevelopmentXml::processSimpleXmlCdata($template),
			));
			$dw->save();
		}

		XenForo_Db::commit($db);

		return;
	}

	public function importAdminTemplatesXml(SimpleXMLElement $xml, $blockId)
	{
		$existingAdminTemplates = $this->getAdminTemplatesByBlock($blockId);

		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);

		foreach ($xml->template AS $template)
		{
			$templateName = (string)$template['title'];

			$dw = XenForo_DataWriter::create('XenForo_DataWriter_AdminTemplate');
			if (isset($existingAdminTemplates[$templateName]))
			{
				$dw->setExistingData($existingAdminTemplates[$templateName], true);
			}
			$dw->bulkSet(array(
				'title' => $templateName,
				'template' => XenForo_Helper_DevelopmentXml::processSimpleXmlCdata($template),
			));
			$dw->save();
		}

		XenForo_Db::commit($db);

		return;
	}

	public function appendTemplatesXml(DOMElement $rootNode, $blockId)
	{
		$document = $rootNode->ownerDocument;

		$templates = $this->getTemplatesByBlock($blockId);
		foreach ($templates AS $template)
		{
			$templateNode = $document->createElement('template');
			$templateNode->setAttribute('title', $template['title']);
			$templateNode->appendChild(XenForo_Helper_DevelopmentXml::createDomCdataSection($document, $template['template']));

			$rootNode->appendChild($templateNode);
		}
	}

	public function appendAdminTemplatesXml(DOMElement $rootNode, $blockId)
	{
		$document = $rootNode->ownerDocument;

		$adminTemplates = $this->getAdminTemplatesByBlock($blockId);
		foreach ($adminTemplates AS $template)
		{
			$templateNode = $document->createElement('template');
			$templateNode->setAttribute('title', $template['title']);
			$templateNode->appendChild(XenForo_Helper_DevelopmentXml::createDomCdataSection($document, $template['template']));

			$rootNode->appendChild($templateNode);
		}
	}
}