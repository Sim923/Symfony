<?
define("ADMIN_MODULE_NAME", "clouds");

/*.require_module 'standard';.*/
/*.require_module 'pcre';.*/
/*.require_module 'thurly_main_include_prolog_admin_before';.*/
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
if(!$USER->CanDoOperation("clouds_config"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

/*.require_module 'thurly_clouds_include';.*/
if(!CModule::IncludeModule('clouds'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$io = CBXVirtualIo::GetInstance();
$sTableID = "tbl_clouds_storage_list";
$oSort = new CAdminSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);
$bOnTheMove = isset($_GET["themove"]);

$upload_dir = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload");
$bHasLocalStorage = file_exists($upload_dir) && (is_dir($upload_dir) || is_link($upload_dir)) && is_writable($upload_dir);

$arID = $lAdmin->GroupAction();
$action = isset($_REQUEST["action"]) && is_string($_REQUEST["action"])? "$_REQUEST[action]": "";
if(is_array($arID))
{
	foreach($arID as $ID)
	{
		if(strlen($ID) <= 0 || intval($ID) <= 0)
			continue;

		switch($action)
		{
		case "delete":
			$ob = new CCloudStorageBucket(intval($ID));
			if(!$ob->Delete())
			{
				$e = $APPLICATION->GetException();
				$lAdmin->AddUpdateError($e->GetString(), $ID);
			}
			break;
		case "deactivate":
			$ob = new CCloudStorageBucket(intval($ID));
			if($ob->ACTIVE === "Y")
				$ob->Update(array("ACTIVE"=>"N"));
			break;
		case "activate":
			$ob = new CCloudStorageBucket(intval($ID));
			if($ob->ACTIVE === "N")
				$ob->Update(array("ACTIVE"=>"Y"));
			break;
		case "download":
			$ob = new CCloudStorageBucket(intval($ID));
			if($ob->Init() && $ob->ACTIVE === "Y")
			{
				if(isset($_SESSION["last_file_id"]))
					$last_file_id = intval($_SESSION["last_file_id"]);
				else
					$last_file_id = 0;

				if(isset($_SESSION["last_file_pos"]))
					$last_file_pos = doubleval($_SESSION["last_file_pos"]);
				else
					$last_file_pos = 0;

				$rsNextFile = $DB->Query("
					SELECT MIN(ID) ID, COUNT(1) CNT, SUM(FILE_SIZE) FILE_SIZE
					FROM b_file
					WHERE ID > ".intval($last_file_id)."
					AND HANDLER_ID = '".$DB->ForSQL($ob->ID)."'
				");

				$lAdmin->BeginPrologContent();
				if(
					is_array($ar = $rsNextFile->Fetch())
					&& (intval($ar["ID"]) > 0)
				)
				{
					$bNextFile = true;
					$bFileMoved = false;
					$maxPartSize = 1024*1024; //1M

					$arFile = CFile::GetFileArray($ar["ID"]);
					$filePath = preg_replace("#[\\\\\\/]+#", "/", "/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"]);
					$absPath = preg_replace("#[\\\\\\/]+#", "/", $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main", "upload_dir", "upload").$filePath);
					$absPath = $io->GetPhysicalName($absPath);
					$absTempPath = $absPath."~";

					if(!file_exists($absPath))
					{
						CheckDirPath($absTempPath);

						$obRequest = new CHTTP;
						$obRequest->follow_redirect = true;
						$obRequest->fp = fopen($absTempPath, "ab");
						if(is_resource($obRequest->fp))
						{
							if($arFile["FILE_SIZE"] > $maxPartSize)
							{
								$obRequest->additional_headers["Range"] = sprintf("bytes=%u-%u", $last_file_pos, ($last_file_pos+$maxPartSize > $arFile["FILE_SIZE"]? $arFile["FILE_SIZE"]: $last_file_pos+$maxPartSize)-1);
							}

							$res = $obRequest->HTTPQuery('GET', $ob->GetFileSRC($arFile));

							fclose($obRequest->fp);
							unset($obRequest->fp);

							if($res && ($obRequest->status == 200 || $obRequest->status == 206))
							{
								$bFileMoved = true;
								if($arFile["FILE_SIZE"] > $maxPartSize)
								{
									$last_file_pos += $maxPartSize;
									$_SESSION["last_file_pos"] = $last_file_pos;
									$bNextFile = false;
								}
								else
								{
									$last_file_pos = $arFile["FILE_SIZE"];
								}

								if(
									array_key_exists("Content-Range", $obRequest->headers)
									&& preg_match("/(\\d+)-(\\d+)\\/(\\d+)\$/", $obRequest->headers["Content-Range"], $match)
								)
									$FILE_SIZE = $match[3];
								elseif(
									array_key_exists("Content-Length", $obRequest->headers)
									&& preg_match("/^(\\d+)\$/", $obRequest->headers["Content-Length"], $match)
									&& $match[1] > $maxPartSize
								)
									$FILE_SIZE = 0; //Chunk download not supported
								else
									$FILE_SIZE = $arFile["FILE_SIZE"];

								if($last_file_pos > $FILE_SIZE)
								{
									$last_file_pos = $arFile["FILE_SIZE"];
									$bFileMoved = true;
									$bNextFile = true;
								}
							}
							else
							{
								//An error occured
								@unlink($absTempPath);
								$bFileMoved = false;
							}
						}
					}

					if($bNextFile)
					{
						$_SESSION["last_file_id"] = $ar["ID"];
						$_SESSION["last_file_pos"] = 0.0;

						if($bFileMoved)
						{
							rename($absTempPath, $absPath);
							$ob->DeleteFile($filePath);
							$DB->Query("
								UPDATE b_file
								SET HANDLER_ID = null
								WHERE ID = ".intval($arFile["ID"])."
							");
							CFile::CleanCache($arFile["ID"]);
							$ob->DecFileCounter((float)$arFile["FILE_SIZE"]);
							$ob->Update(array("LAST_FILE_ID" => 0));
						}
					}

					CAdminMessage::ShowMessage(array(
						"TYPE"=>"PROGRESS",
						"MESSAGE"=>GetMessage("CLO_STORAGE_LIST_DOWNLOAD_IN_PROGRESS"),
						"DETAILS"=>GetMessage("CLO_STORAGE_LIST_DOWNLOAD_PROGRESS", array(
							"#remain#" => $ar["CNT"] - $bNextFile,
							"#bytes#" => CFile::FormatSize($ar["FILE_SIZE"] - $last_file_pos),
						)),
						"HTML"=>true,
						"BUTTONS" => array(
							array(
								"VALUE" => GetMessage("CLO_STORAGE_LIST_STOP"),
								"ONCLICK" => 'window.location = \'/thurly/admin/clouds_storage_list.php?lang='.LANGUAGE_ID.'\'',
							),
						),
					));

					$bOnTheMove = true;
					echo '<script>', $lAdmin->ActionDoGroup($ID, "download", "themove=y"), '</script>';
				}
				else
				{
					unset($_SESSION["last_file_id"]);
					unset($_SESSION["last_file_pos"]);

					CAdminMessage::ShowMessage(array(
						"MESSAGE"=>GetMessage("CLO_STORAGE_LIST_DOWNLOAD_DONE"),
						"TYPE"=>"OK",
						"HTML"=>true,
					));
					$bOnTheMove = false;
				}
				$lAdmin->EndPrologContent();
			}
			break;
		case "move":
			$message = /*.(CAdminMessage).*/null;
			$ob = new CCloudStorageBucket(intval($ID));
			if($ob->ACTIVE === "Y" && $ob->READ_ONLY === "N")
			{
				$_done = 0;
				$_size = 0.0;
				$_skip = 0;

				if(intval($ob->LAST_FILE_ID) > 0)
				{
					if(isset($_SESSION["arMoveStat_done"]))
						$_done = intval($_SESSION["arMoveStat_done"]);
					if(isset($_SESSION["arMoveStat_size"]))
						$_size = doubleval($_SESSION["arMoveStat_size"]);
					if(isset($_SESSION["arMoveStat_skip"]))
						$_skip = intval($_SESSION["arMoveStat_skip"]);
				}

				$files_per_step = 50;
				$rsNextFile = $DB->Query($DB->TopSQL("
					SELECT *
					FROM b_file
					WHERE ID > ".intval($ob->LAST_FILE_ID)."
					AND (HANDLER_ID IS NULL OR HANDLER_ID <> '".$DB->ForSQL($ob->ID)."')
					ORDER BY ID ASC
				", $files_per_step));

				$counter = 0;
				$bWasMoved = false;
				$moveResult = CCloudStorage::FILE_SKIPPED;
				while(
					$moveResult == CCloudStorage::FILE_PARTLY_UPLOADED
					|| is_array($arFile = $rsNextFile->Fetch())
				)
				{
					CCloudStorage::FixFileContentType($arFile);
					$moveResult = CCloudStorage::MoveFile($arFile, $ob);
					if($moveResult == CCloudStorage::FILE_MOVED)
					{
						$DB->Query("
							UPDATE b_file
							SET HANDLER_ID = '".$DB->ForSQL($ob->ID)."'
							WHERE ID = ".intval($arFile["ID"])."
						");
						CFile::CleanCache($arFile["ID"]);
						$_done += 1;
						$_size += doubleval($arFile["FILE_SIZE"]);
						$bWasMoved = true;
						$ob->Update(array("LAST_FILE_ID" => $arFile["ID"]));
						$counter++;
					}
					elseif($moveResult == CCloudStorage::FILE_SKIPPED)
					{
						$e = $APPLICATION->GetException();
						if(is_object($e))
						{
							$message = new CAdminMessage(GetMessage("CLO_STORAGE_LIST_MOVE_FILE_ERROR"), $e);
							break;
						}
						else
						{
							$_skip += 1;
							$ob->Update(array("LAST_FILE_ID" => $arFile["ID"]));
							$counter++;
						}
					}
					else//if($moveResult == CCloudStorage::FILE_PARTLY_UPLOADED)
					{
						$bWasMoved = true;
					}

					if($bWasMoved)
					{
						usleep(300);
						break;
					}
				}

				$lAdmin->BeginPrologContent();
				if(is_object($message))
				{
					echo $message->Show();
				}
				elseif($counter < $files_per_step && !$bWasMoved)
				{
					CAdminMessage::ShowMessage(array(
						"MESSAGE"=>GetMessage("CLO_STORAGE_LIST_MOVE_DONE"),
						"DETAILS"=>GetMessage("CLO_STORAGE_LIST_MOVE_PROGRESS", array(
							"#bytes#" => CFile::FormatSize($_size),
							"#total#" => $_done + $_skip,
							"#moved#" => $_done,
							"#skiped#" => $_skip,
						)),
						"HTML"=>true,
						"TYPE"=>"OK",
					));
					$bOnTheMove = false;
					$ob->Update(array("LAST_FILE_ID" => false));
				}
				else
				{
					CAdminMessage::ShowMessage(array(
						"TYPE"=>"PROGRESS",
						"MESSAGE"=>GetMessage("CLO_STORAGE_LIST_MOVE_IN_PROGRESS"),
						"DETAILS"=>GetMessage("CLO_STORAGE_LIST_MOVE_PROGRESS", array(
							"#bytes#" => CFile::FormatSize($_size + CCloudStorage::$part_count*CCloudStorage::$part_size),
							"#total#" => $_done + $_skip,
							"#moved#" => $_done,
							"#skiped#" => $_skip,
						)),
						"HTML"=>true,
						"BUTTONS" => array(
							array(
								"VALUE" => GetMessage("CLO_STORAGE_LIST_STOP"),
								"ONCLICK" => 'window.location = \'/thurly/admin/clouds_storage_list.php?lang='.LANGUAGE_ID.'\'',
							),
						),
					));
					$bOnTheMove = true;
					echo '<script>', $lAdmin->ActionDoGroup($ID, "move", "themove=y"), '</script>';
				}
				$lAdmin->EndPrologContent();

				$_SESSION["arMoveStat_done"] = $_done;
				$_SESSION["arMoveStat_size"] = $_size;
				$_SESSION["arMoveStat_skip"] = $_skip;
			}
			break;
		default:
			break;
		}
	}
}

$arHeaders = array(
	array(
		"id" => "SORT",
		"content" => GetMessage("CLO_STORAGE_LIST_SORT"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "ID",
		"content" => GetMessage("CLO_STORAGE_LIST_ID"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "ACTIVE",
		"content" => GetMessage("CLO_STORAGE_LIST_ACTIVE"),
		"align" => "center",
		"default" => true,
	),
	array(
		"id" => "FILE_COUNT",
		"content" => GetMessage("CLO_STORAGE_LIST_FILE_COUNT"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "FILE_SIZE",
		"content" => GetMessage("CLO_STORAGE_LIST_FILE_SIZE"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "READ_ONLY",
		"content" => GetMessage("CLO_STORAGE_LIST_MODE"),
		"align" => "center",
		"default" => true,
	),
	array(
		"id" => "SERVICE",
		"content" => GetMessage("CLO_STORAGE_LIST_SERVICE"),
		"default" => true,
	),
	array(
		"id" => "BUCKET",
		"content" => GetMessage("CLO_STORAGE_LIST_BUCKET"),
		"align" => "center",
		"default" => true,
	),
);
$lAdmin->AddHeaders($arHeaders);

$rsData = CCloudStorageBucket::GetList(array("SORT"=>"DESC", "ID"=>"ASC"));
$rsData = new CAdminResult($rsData, $sTableID);
while(is_array($arRes = $rsData->Fetch()))
{
	$row =& $lAdmin->AddRow($arRes["ID"], $arRes);

	$row->AddViewField("ID", '<a href="clouds_storage_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes["ID"].'">'.$arRes["ID"].'</a>');

	if($arRes["ACTIVE"] === "Y")
		$html = '<div class="lamp-green"></div>';
	else
		$html = '<div class="lamp-red"></div>';

	$row->AddViewField("ACTIVE", $html);
	$row->AddViewField("READ_ONLY", $arRes["READ_ONLY"]==="Y"? GetMessage("CLO_STORAGE_LIST_READ_ONLY"): GetMessage("CLO_STORAGE_LIST_READ_WRITE"));
	$row->AddViewField("SERVICE", CCloudStorage::GetServiceDescription($arRes["SERVICE_ID"]));
	$row->AddViewField("FILE_SIZE", CFile::FormatSize($arRes["FILE_SIZE"]));

	$arActions = array(
		array(
			"ICON" => "edit",
			"DEFAULT" => true,
			"TEXT" => GetMessage("CLO_STORAGE_LIST_EDIT"),
			"ACTION" => $lAdmin->ActionRedirect('clouds_storage_edit.php?lang='.LANGUAGE_ID.'&ID='.$arRes["ID"])
		)
	);
	$arActions[] = array("SEPARATOR"=>"Y");

	if($arRes["ACTIVE"] === "Y")
	{
		if($arRes["READ_ONLY"] !== "Y")
		{
			if(intval($arRes["LAST_FILE_ID"]) > 0)
			{
				$arActions[] = array(
					"TEXT"=>GetMessage("CLO_STORAGE_LIST_CONT_MOVE_FILES"),
					"ACTION"=>$lAdmin->ActionDoGroup($arRes["ID"], "move")
				);
			}
			else
			{
				$arActions[] = array(
					"TEXT"=>GetMessage("CLO_STORAGE_LIST_START_MOVE_FILES"),
					"ACTION"=>$lAdmin->ActionDoGroup($arRes["ID"], "move")
				);
			}
		}

		if($bHasLocalStorage)
		{
			$arActions[] = array(
				"TEXT"=>GetMessage("CLO_STORAGE_LIST_MOVE_LOCAL"),
				"ACTION"=>"if(confirm('".GetMessage("CLO_STORAGE_LIST_MOVE_LOCAL_CONF")."')) ".$lAdmin->ActionDoGroup($arRes["ID"], "download")
			);
		}

		$arActions[] = array(
			"TEXT"=>GetMessage("CLO_STORAGE_LIST_DEACTIVATE"),
			"ACTION"=>"if(confirm('".GetMessage("CLO_STORAGE_LIST_DEACTIVATE_CONF")."')) ".$lAdmin->ActionDoGroup($arRes["ID"], "deactivate")
		);
	}
	else
	{
		$arActions[] = array(
			"TEXT"=>GetMessage("CLO_STORAGE_LIST_ACTIVATE"),
			"ACTION"=>$lAdmin->ActionDoGroup($arRes["ID"], "activate")
		);
	}

	if(intval($arRes["B_FILE_COUNT"]) > 0)
	{
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("CLO_STORAGE_LIST_DELETE"),
			"ACTION"=>"alert('".GetMessage("CLO_STORAGE_LIST_CANNOT_DELETE")."')"
		);
	}
	else
	{
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("CLO_STORAGE_LIST_DELETE"),
			"ACTION"=>"if(confirm('".GetMessage("CLO_STORAGE_LIST_DELETE_CONF")."')) ".$lAdmin->ActionDoGroup($arRes["ID"], "delete")
		);
	}

	if(!empty($arActions) && !$bOnTheMove)
		$row->AddActions($arActions);

}

$arFooter = array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $rsData->SelectedRowsCount(),
	),
	array(
		"counter" => true,
		"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
		"value" => 0,
	),
);

$lAdmin->AddFooter($arFooter);

$aContext = array(
	array(
		"TEXT" => GetMessage("CLO_STORAGE_LIST_ADD"),
		"LINK" => "/thurly/admin/clouds_storage_edit.php?lang=".LANGUAGE_ID,
		"TITLE" => GetMessage("CLO_STORAGE_LIST_ADD_TITLE"),
		"ICON" => "btn_new",
	),
);

$lAdmin->AddAdminContextMenu($aContext, /*$bShowExcel=*/false);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CLO_STORAGE_LIST_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_admin.php");
?>