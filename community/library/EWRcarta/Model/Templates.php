<?php

class EWRcarta_Model_Templates extends XenForo_Model
{
	public function getTemplateBySlug($pageSlug)
	{
		if (!$template = $this->_getDb()->fetchRow("
			SELECT *
				FROM EWRcarta_templates
			WHERE template_name = ?
		", $pageSlug))
		{
			return false;
		}

		return $template;
	}

	public function updateTemplate($input)
	{
		$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_Templates');

		if ($input['template_name'])
		{
			$dw->setExistingData($input);
		}

		$dw->bulkSet(array(
			'template_name'    => $input['template_newname'],
			'template_content' => $input['template_content'],
		));
		$dw->save();

		return true;
	}

	public function deleteTemplate($input)
	{
		$dw = XenForo_DataWriter::create('EWRcarta_DataWriter_Templates');
		$dw->setExistingData($input);
		$dw->delete();

		return true;
	}
}