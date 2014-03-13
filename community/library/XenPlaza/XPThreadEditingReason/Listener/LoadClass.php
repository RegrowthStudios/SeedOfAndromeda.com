<?php 
class XenPlaza_XPThreadEditingReason_Listener_LoadClass
{
	public static function loadClassListener($class, &$extend)
	{
		$classes = array(
			'ControllerPublic_Post',
			'ControllerPublic_Thread',
		);
		foreach($classes AS $clas){
			if ($class == 'XenForo_' .$clas)
			{
				$extend[] = 'XenPlaza_XPThreadEditingReason_' .$clas;
			}
		}
	}
	public static function loadClassDatawriter($class, &$extend)
	{
		if ($class == 'XenForo_DataWriter_DiscussionMessage_Post')
		{
			$extend[] = 'XenPlaza_XPThreadEditingReason_DataWriter_DiscussionMessage_Post';
		}
	}
	public static function templatePostRender($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
    {
        if ($templateName == 'post_edit_inline')
        {
			$pos = strpos($content,'<div class="secondaryContent messageContainer">');
			$part2 = $template->create('XP_reason_edit', $template->getParams()) . substr($content, $pos);
			$content = str_replace(substr($content, $pos), $part2 ,$content);
        }
        if ($templateName == 'post_edit')
        {
			$pos = strpos($content,'<fieldset>');
			$part2 = $template->create('XP_reason_edit', $template->getParams()) . substr($content, $pos);
			$content = str_replace(substr($content, $pos), $part2 ,$content);
        }
    }
	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {	
        if ($hookName == 'post_private_controls')
		{
			$ourTemplate = $template->create('Xp_edit_reason_show', $hookParams);
			$rendered = $ourTemplate->render();
			$contents .= $rendered;
		}
    }
}

?>