<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

function JSPopupRedirectHandler(&$url, $skip_security_check)
{
	$a = new CAutoSave();
	$a->Reset();

	if(preg_match("#^/thurly/admin/#", $url))
	{
		ob_end_clean();
		echo '<script type="text/javascript">top.BX.WindowManager.Get().Close(); '.(!$_REQUEST['subdialog'] ? 'top.BX.reload(true);' : '').'</script>';
		die();
	}
	else
	{
		ob_end_clean();
		echo '<script type="text/javascript">top.BX.WindowManager.Get().Close(); '.(!$_REQUEST['subdialog'] ? 'top.BX.reload(\''.CUtil::JSEscape($url).'\', true);' : '').'</script>';
		die();
	}
}

AddEventHandler('main', 'OnBeforeLocalRedirect', 'JSPopupRedirectHandler');
?>