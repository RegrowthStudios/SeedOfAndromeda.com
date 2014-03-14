<?php
class BButtonCode_Listener
{
    public static function listen($class, array &$extend)
    {
        if ($class == 'XenForo_BbCode_Formatter_Base')
        {
            $extend[] = 'BButtonCode_BbCode_Formatter_Base';
        }
    }
    
    public static function template_hook ($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
        if ($hookName == 'page_container_content_top')
        {
            $ourTemplate = $template->create('bb_code_tag_button', $template->getParams());
            $rendered = $ourTemplate->render();
            $contents .= $rendered;
        }
        
        if ($hookName == 'help_bb_codes')
        {
            $ourTemplate = $template->create('help_bbcodes_button', $template->getParams());
            $rendered = $ourTemplate->render();
            $contents .= $rendered;            
        }
    }
    
}
