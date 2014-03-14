<?php
define('XF_ROOT', 'community'); // set this!
define('TIMENOW', time());
define('SESSION_BYPASS', false); // if true: logged in user info and sessions are not needed 

require_once(XF_ROOT . '/library/XenForo/Autoloader.php');

XenForo_Autoloader::getInstance()->setupAutoloader(XF_ROOT . '/library');

XenForo_Application::initialize(XF_ROOT . '/library', XF_ROOT);
XenForo_Application::set('page_start_time', TIMENOW);
XenForo_Application::disablePhpErrorHandler();
XenForo_Application::setDebugMode(false);

if (!SESSION_BYPASS)
{
    XenForo_Session::startPublicSession();

    $visitor = XenForo_Visitor::getInstance();

    if ($visitor->getUserId())
    {
        $userModel = XenForo_Model::create('XenForo_Model_User');
        $userinfo = $userModel->getFullUserById($visitor->getUserId());
    }
}

restore_error_handler();
restore_exception_handler();
//header("Content-type: text/plain");
//Uncomment to test:
//var_dump($userModel);
//var_dump($userinfo);