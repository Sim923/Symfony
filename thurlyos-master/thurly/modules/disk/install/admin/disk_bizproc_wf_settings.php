<?
define("MODULE_ID", "disk");
if($_REQUEST['entity']=="ThurlyDiskBizProcDocument")
{
	define("ENTITY", "Thurly\\Disk\\BizProcDocument");
}
else
{
	define("ENTITY", "Thurly\\Disk\\BizProcDocumentCompatible");
}
$fp = $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/bizprocdesigner/admin/bizproc_wf_settings.php";
if (file_exists($fp))
	require($fp);