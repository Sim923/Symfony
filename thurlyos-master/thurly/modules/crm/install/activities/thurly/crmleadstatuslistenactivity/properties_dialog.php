<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("crm"))
	return;
?>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPCLSLA_PD_LEAD") ?>:</td>
	<td width="60%">
		<input type="text" name="lead_id" id="id_lead_id" value="<?= htmlspecialcharsbx($arCurrentValues["lead_id"]) ?>" size="20" />
		<input type="button" value="..." onclick="BPAShowSelector('id_lead_id', 'int');" />
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("BPCLSLA_PD_STATUS") ?>:</td>
	<td width="60%">
		<select name="status[]" multiple="multiple">
			<?
			$selected = (array)$arCurrentValues["status"];
			foreach (\CCrmStatus::GetStatusList('STATUS') as $statusId => $statusName)
			{
				$s = CCrmLead::GetStatusSemantics($statusId);
				if ($s != 'process')
					continue;
				?><option value="<?= htmlspecialcharsbx($statusId) ?>"<?= (in_array($statusId, $selected)) ? " selected" : "" ?>><?= htmlspecialcharsbx($statusName) ?></option><?
			}
			?>
		</select>
		<div style="margin: 5px 0; color: grey"><?=GetMessage('BPCLSLA_PD_STATUS_DESCR')?></div>
	</td>
</tr>
