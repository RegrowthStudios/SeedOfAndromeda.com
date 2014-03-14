<?php

class EWRporta_Block_Option_RawHyperText3
{
	public static function verifyHTML(&$option, XenForo_DataWriter $dw, $fieldName)
	{
		$templateModel = XenForo_Model::create('XenForo_Model_Template');
		$template = $templateModel->getTemplateInStyleByTitle('EWRblock_RawHyperText3', 0);
		
		$data = array(
			'title' => $template['title'],
			'template' => $template['template'],
			'style_id' => $template['style_id'],
			'addon_id' => $template['addon_id']
		);
		$data['template'] = $option;
		
		$writer = XenForo_DataWriter::create('XenForo_DataWriter_Template');
		$writer->setExistingData($template['template_id']);
		$writer->bulkSet($data);
		$writer->save();

		return true;
	}
	
	public static function verifyCSS(&$option, XenForo_DataWriter $dw, $fieldName)
	{
		$templateModel = XenForo_Model::create('XenForo_Model_Template');
		$template = $templateModel->getTemplateInStyleByTitle('EWRblock_RawHyperText3.css', 0);
		
		$data = array(
			'title' => $template['title'],
			'template' => $template['template'],
			'style_id' => $template['style_id'],
			'addon_id' => $template['addon_id']
		);
		$data['template'] = $option;
		
		$writer = XenForo_DataWriter::create('XenForo_DataWriter_Template');
		$writer->setExistingData($template['template_id']);
		$writer->bulkSet($data);
		$writer->save();

		return true;
	}
}