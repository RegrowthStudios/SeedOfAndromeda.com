<?php

class EWRcarta_Listener_Template
{
	public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		switch ($hookName)
		{
			case 'search_form_tabs':
			{
				$contents .= $template->create('EWRcarta_Search_Tab', $template->getParams());
				break;
			}
			case 'thread_view_form_before':
			{
				if (in_array($hookParams['thread']['node_id'], XenForo_Application::get('options')->EWRcarta_wikiforum))
				{
					$pagesModel = XenForo_Model::create('EWRcarta_Model_Pages');
					
					if ($hookParams['page'] = $pagesModel->getPageByThread($hookParams['thread']['thread_id']))
					{
						$permsModel = XenForo_Model::create('EWRcarta_Model_Perms');
						$hookParams['perms'] = $permsModel->getPermissions();
						
						$attachModel = XenForo_Model::create('XenForo_Model_Attachment');
						$hookParams['page']['attachments'] = $attachModel->getAttachmentsByContentId('wiki', $hookParams['page']['page_id']);
						
						$contents = $template->create('EWRcarta_Thread_Tabs', $hookParams) . $contents;
					}
				}
				break;
			}
		}
	}
}