{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
<link rel="stylesheet" type="text/css" media="all" href="jscalendar/calendar-win2k-cold-1.css">
<script type="text/javascript" src="jscalendar/calendar.js"></script>
<script type="text/javascript" src="jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="jscalendar/calendar-setup.js"></script>
<script type="text/javascript" src="include/js/dtlviewajax.js"></script>
<!--[if lte IE 8]><script type="text/javascript" src="modules/Forecasts/flot/excanvas.min.js"></script><![endif]-->
<script type="text/javascript" src="modules/Forecasts/flot/jquery.flot.js"></script>
<script type="text/javascript" src="modules/Forecasts/flot/jquery.flot.highlighter.js"></script>
<script type="text/javascript" src="modules/Forecasts/flot/jquery.flot.pyramid.js"></script>
<span id="crmspanid" style="display:none;position:absolute;" onmouseover="show('crmspanid');">
	<a class="link" align="right" href="javascript:;">{$APP.LBL_EDIT_BUTTON}</a>
</span>
<div id="convertleaddiv" style="display:block;position:absolute;left:225px;top:150px;"></div>
<script>
var gVTModule = '{vtlib_purify($smarty.request.module)}';
{literal}
function showHideStatus(sId,anchorImgId, sImagePath) {
	var oObj = document.getElementById(sId);
	if (oObj.style.display == 'block') {
		oObj.style.display = 'none';
		if (anchorImgId !=null) {
{/literal}
			document.getElementById(anchorImgId).src = 'themes/images/inactivate.gif';
			document.getElementById(anchorImgId).alt = '{'LBL_Show'|@getTranslatedString:'Settings'}';
			document.getElementById(anchorImgId).title = '{'LBL_Show'|@getTranslatedString:'Settings'}';
			document.getElementById(anchorImgId).parentElement.className = 'exp_coll_block activate';
{literal}
		}
	} else {
		oObj.style.display = 'block';
		if (anchorImgId !=null) {
{/literal}
			document.getElementById(anchorImgId).src = 'themes/images/activate.gif';
			document.getElementById(anchorImgId).alt = '{'LBL_Hide'|@getTranslatedString:'Settings'}';
			document.getElementById(anchorImgId).title = '{'LBL_Hide'|@getTranslatedString:'Settings'}';
			document.getElementById(anchorImgId).parentElement.className = 'exp_coll_block inactivate';
{literal}
		}
	}
}
{/literal}
</script>

<div id="lstRecordLayout" class="layerPopup" style="display:none;width:325px;height:300px;"></div>

<table width="100%" cellpadding="2" cellspacing="0" border="0" class="detailview_wrapper_table">
	<tr>
		<td class="detailview_wrapper_cell">

			{include file='Buttons_List.tpl' isDetailView=true}

			<!-- Contents -->
			<table border=0 cellspacing=0 cellpadding=0 width=98% align=center>
				<tr>
					<td valign=top><img src="{vtiger_imageurl('showPanelTopLeft.gif', $THEME)}"></td>
					<td class="showPanelBg" valign=top width=100%>
						<!-- PUBLIC CONTENTS STARTS-->
						<div class="small" style="padding:14px" onclick="hndCancelOutsideClick();">
						{include file='applicationmessage.tpl'}
						<!-- Entity and More information tabs -->
						<table border=0 cellspacing=0 cellpadding=0 width=95% align=center>
							<tr>
								<td>
								<table border=0 cellspacing=0 cellpadding=3 width=100% class="small">
				<tr>
					<td class="dvtTabCache" style="width:10px" nowrap>&nbsp;</td>
					<td class="dvtSelectedCell" align=center nowrap>{$SINGLE_MOD|@getTranslatedString:$MODULE} {$APP.LBL_INFORMATION}</td>
					<td class="dvtTabCache" style="width:10px">&nbsp;</td>
						{if $SinglePane_View eq 'false' && $IS_REL_LIST neq false && $IS_REL_LIST|@count > 0}
					<td class="dvtUnSelectedCell" onmouseout="fnHideDrop('More_Information_Modules_List');" onmouseover="fnDropDown(this,'More_Information_Modules_List');" align="center" nowrap>
						<a href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}">{$APP.LBL_MORE} {$APP.LBL_INFORMATION}</a>
						<div onmouseover="fnShowDrop('More_Information_Modules_List')" onmouseout="fnHideDrop('More_Information_Modules_List')" id="More_Information_Modules_List" class="drop_mnu" style="left: 502px; top: 76px; display: none;">
							<table border="0" cellpadding="0" cellspacing="0" width="100%">
							{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
								<tr>
								<td>
									<a class="drop_down" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}#tbl_{$MODULE}_{$_RELATED_MODULE}">
										{$_RELATED_MODULE|@getTranslatedString:$_RELATED_MODULE}
									</a>
								</td>
								</tr>
							{/foreach}
							</table>
						</div>
					</td>
					{/if}
					<td class="dvtTabCache" align="right" style="width:100%">
					<span class='slds-float_right'>
						{if $EDIT_PERMISSION eq 'yes'}
						<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="crmbutton small edit"
							onclick="DetailView.return_module.value='{$MODULE}';DetailView.return_action.value='DetailView';DetailView.return_id.value='{$ID}';DetailView.module.value='{$MODULE}';submitFormForAction('DetailView','EditView');"
							type="button" name="Edit" value="&nbsp;{$APP.LBL_EDIT_BUTTON_LABEL}&nbsp;">&nbsp;
						{/if}
						{if $CREATE_PERMISSION eq 'permitted'}
						<input title="{$APP.LBL_DUPLICATE_BUTTON_TITLE}" accessKey="{$APP.LBL_DUPLICATE_BUTTON_KEY}" class="crmbutton small create"
							onclick="DetailView.return_module.value='{$MODULE}';DetailView.return_action.value='DetailView';DetailView.isDuplicate.value='true';DetailView.module.value='{$MODULE}';submitFormForAction('DetailView','EditView');"
							type="button" name="Duplicate" value="{$APP.LBL_DUPLICATE_BUTTON_LABEL}">&nbsp;
						{/if}
						{if $DELETE eq 'permitted'}
						<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="crmbutton small delete"
							onclick="DetailView.return_module.value='{$MODULE}';DetailView.return_action.value='index';{if $MODULE eq 'Accounts'}var confirmMsg='{$APP.NTC_ACCOUNT_DELETE_CONFIRMATION}' {else}var confirmMsg='{$APP.NTC_DELETE_CONFIRMATION}'{/if};submitFormForActionWithConfirmation('DetailView', 'Delete', confirmMsg);"
							type="button" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}">&nbsp;
						{/if}
						{if $privrecord neq ''}
						<img align="absmiddle" title="{$APP.LNK_LIST_PREVIOUS}" accessKey="{$APP.LNK_LIST_PREVIOUS}"
							onclick="location.href='index.php?module={$MODULE}&action=DetailView&record={$privrecord}&start={$privrecordstart}'" name="privrecord"
							value="{$APP.LNK_LIST_PREVIOUS}" src="{vtiger_imageurl('rec_prev.gif', $THEME)}">&nbsp;
						{else}
						<img align="absmiddle" title="{$APP.LNK_LIST_PREVIOUS}" src="{vtiger_imageurl('rec_prev_disabled.gif', $THEME)}">
						{/if}
						{if $privrecord neq '' || $nextrecord neq ''}
						<img align="absmiddle" title="{$APP.LBL_JUMP_BTN}" accessKey="{$APP.LBL_JUMP_BTN}" name="jumpBtnIdTop" id="jumpBtnIdTop"
							onclick="var obj=this;var lhref=getListOfRecords(obj, '{$MODULE}', {$ID}, '');" src="{vtiger_imageurl('rec_jump.gif', $THEME)}">&nbsp;
						{/if}
						{if $nextrecord neq ''}
						<img align="absmiddle" title="{$APP.LNK_LIST_NEXT}" accessKey="{$APP.LNK_LIST_NEXT}" name="nextrecord"
							onclick="location.href='index.php?module={$MODULE}&action=DetailView&record={$nextrecord}&start={$nextrecordstart}'"
							src="{vtiger_imageurl('rec_next.gif', $THEME)}">&nbsp;
						{else}
						<img align="absmiddle" title="{$APP.LNK_LIST_NEXT}" src="{vtiger_imageurl('rec_next_disabled.gif', $THEME)}">&nbsp;
						{/if}
						<img align="absmiddle" title="{$APP.TOGGLE_ACTIONS}" src="{vtiger_imageurl('menu-icon.png', $THEME)}" width="16px;"
							onclick="{literal}if (document.getElementById('actioncolumn').style.display=='none') {document.getElementById('actioncolumn').style.display='table-cell';}else{document.getElementById('actioncolumn').style.display='none';}{/literal}">&nbsp;
					</span>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
		<td>
		<table style="width:100%;text-align:left;">
		<tr><td style="width:60%;text-align:left;vertical-align: top;">
		<table style="width:100%;text-align:right;">
		<tr><td>
		<form action="index.php" method="post" name="DetailView" id="form">
		{include file='DetailViewHidden.tpl'}

		<table style="width:100%;text-align:right;">
		<tr>
		<td class="dvtCellLabel" align="left">{$MOD.year}</td>
		<td class="dvtCellInfo" align="left">{$YEAR}</td>
		<td class="dvtCellLabel" align="left">{$MOD.frequency}</td>
		<td class="dvtCellInfo" align="left">{$FREQ}</td>
		<td class="dvtCellLabel" align="left">{$MOD.period}</td>
		<td class="dvtCellInfo" align="left">{$PERIOD}</td>
		<td class="dvtCellLabel" align="left">{$MOD.relatedto}</td>
		<td class="dvtCellInfo" align="left">{$RELTO}</td>
		<td class="dvtCellLabel" align="left">{$MOD.only_related}</td>
		<td class="dvtCellInfo" align="left">{$ONLYR}</td>
		<td class="dvtCellLabel" align="left">{$MOD.Category}</td>
		<td class="dvtCellInfo" align="left">{$FCCAT}</td>
		<td class="dvtCellLabel" align="left">{$MOD.use_category}</td>
		<td class="dvtCellInfo" align="left">{$USECAT}</td>
		<td class="dvtCellLabel" align="left">{$MOD.only_user}</td>
		<td class="dvtCellInfo" align="left">{$ONLYA}</td>
		</tr>
		</table>
		<table style="width:100%;text-align:right;">
			<tr>
				<th>{$MOD.LBL_MONTH}</th>
				<th>{$MOD.LBL_QUOTA}</th>
				<th>{$MOD.LBL_PCQUOTA}</th>
				<th>{$MOD.LBL_CLOSED}</th>
				<th>{$MOD.LBL_COMMITTED}</th>
				<th>{$MOD.LBL_BESTCASE}</th>
				<th>{$MOD.LBL_PIPELINE}</th>
			</tr>
			{foreach from=$MONTH_DATA key=m item=DATA}
			<tr>
				<td>{$MOD.MonthNames.$m}</td>
				<td>{$CURRENCY_SIMBOL}{$DATA.quota}</td>
				<td>{$DATA.pcquota} %</td>
				<td>{$CURRENCY_SIMBOL}{$DATA.closed}</td>
				<td>{$CURRENCY_SIMBOL}{$DATA.committed}</td>
				<td>{$CURRENCY_SIMBOL}{$DATA.bestcase}</td>
				<td>{$CURRENCY_SIMBOL}{$DATA.pipeline}</td>
			</tr>
			{/foreach}
			<tr>
				<th>{$MOD.LBL_TOTALS}</th>
				<th>{$CURRENCY_SIMBOL}{$TOTALS.quota}</th>
				<th>{$TOTALS.pcquota} %</th>
				<th>{$CURRENCY_SIMBOL}{$TOTALS.closed}</th>
				<th>{$CURRENCY_SIMBOL}{$TOTALS.committed}</th>
				<th>{$CURRENCY_SIMBOL}{$TOTALS.bestcase}</th>
				<th>{$CURRENCY_SIMBOL}{$TOTALS.pipeline}</th>
			</tr>
		</table>
		</form>
		{if $SinglePane_View eq 'true' && $IS_REL_LIST|@count > 0}
		{include file= 'RelatedListNew.tpl'}
		{/if}
		</td></tr></table>
		</td>
		<td style="width:20%;text-align:center;vertical-align: top;" id="actioncolumn"><div id="pyramid" style="width:90%;height:300px;margin: 8px auto;"></div></td>
		</table>
		</td>
		</tr>
	<tr>
		<td>
			<table border=0 cellspacing=0 cellpadding=3 width=100% class="small">
				<tr>
					<td class="dvtTabCacheBottom" style="width:10px" nowrap>&nbsp;</td>
					<td class="dvtSelectedCellBottom" align=center nowrap>{$SINGLE_MOD|@getTranslatedString:$MODULE} {$APP.LBL_INFORMATION}</td>
					<td class="dvtTabCacheBottom" style="width:10px">&nbsp;</td>
					{if $SinglePane_View eq 'false' && $IS_REL_LIST neq false && $IS_REL_LIST|@count > 0}
					<td class="dvtUnSelectedCell" align=center nowrap>
						<a href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}">{$APP.LBL_MORE} {$APP.LBL_INFORMATION}</a>
					</td>
					{/if}
					<td class="dvtTabCacheBottom" align="right" style="width:100%">
						<span class='slds-float_right'>
						{if $EDIT_PERMISSION eq 'yes'}
							<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="crmbutton small edit"
								onclick="DetailView.return_module.value='{$MODULE}';DetailView.return_action.value='DetailView';DetailView.return_id.value='{$ID}';DetailView.module.value='{$MODULE}';submitFormForAction('DetailView','EditView');"
								type="submit" name="Edit" value="&nbsp;{$APP.LBL_EDIT_BUTTON_LABEL}&nbsp;">&nbsp;
						{/if}
						{if $CREATE_PERMISSION eq 'permitted'}
							<input title="{$APP.LBL_DUPLICATE_BUTTON_TITLE}" accessKey="{$APP.LBL_DUPLICATE_BUTTON_KEY}" class="crmbutton small create"
								onclick="DetailView.return_module.value='{$MODULE}';DetailView.return_action.value='DetailView';DetailView.isDuplicate.value='true';DetailView.module.value='{$MODULE}'; submitFormForAction('DetailView','EditView');"
								type="submit" name="Duplicate" value="{$APP.LBL_DUPLICATE_BUTTON_LABEL}">&nbsp;
						{/if}
						{if $DELETE eq 'permitted'}
							<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="crmbutton small delete"
								onclick="DetailView.return_module.value='{$MODULE}';DetailView.return_action.value='index';{if $MODULE eq 'Accounts'}var confirmMsg='{$APP.NTC_ACCOUNT_DELETE_CONFIRMATION}'{else}var confirmMsg='{$APP.NTC_DELETE_CONFIRMATION}'{/if};submitFormForActionWithConfirmation('DetailView', 'Delete', confirmMsg);"
								type="button" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}">&nbsp;
						{/if}
						{if $privrecord neq ''}
							<img align="absmiddle" title="{$APP.LNK_LIST_PREVIOUS}" accessKey="{$APP.LNK_LIST_PREVIOUS}" name="privrecord"
								onclick="location.href='index.php?module={$MODULE}&action=DetailView&record={$privrecord}'" value="{$APP.LNK_LIST_PREVIOUS}"
								src="{vtiger_imageurl('rec_prev.gif', $THEME)}">&nbsp;
						{else}
							<img align="absmiddle" title="{$APP.LNK_LIST_PREVIOUS}" src="{vtiger_imageurl('rec_prev_disabled.gif', $THEME)}">
						{/if}
						{if $privrecord neq '' || $nextrecord neq ''}
							<img align="absmiddle" title="{$APP.LBL_JUMP_BTN}" accessKey="{$APP.LBL_JUMP_BTN}" name="jumpBtnIdBottom" id="jumpBtnIdBottom"
								onclick="var obj=this;var lhref=getListOfRecords(obj, '{$MODULE}', {$ID}, '');" src="{vtiger_imageurl('rec_jump.gif', $THEME)}">&nbsp;
						{/if}
						{if $nextrecord neq ''}
							<img align="absmiddle" title="{$APP.LNK_LIST_NEXT}" accessKey="{$APP.LNK_LIST_NEXT}" name="nextrecord"
								onclick="location.href='index.php?module={$MODULE}&action=DetailView&record={$nextrecord}'" src="{vtiger_imageurl('rec_next.gif', $THEME)}">&nbsp;
						{else}
							<img align="absmiddle" title="{$APP.LNK_LIST_NEXT}" src="{vtiger_imageurl('rec_next_disabled.gif', $THEME)}">&nbsp;
						{/if}
							<img align="absmiddle" title="{$APP.TOGGLE_ACTIONS}" src="{vtiger_imageurl('menu-icon.png', $THEME)}" width="16px;"
								onclick="{literal}if (document.getElementById('actioncolumn').style.display=='none') {document.getElementById('actioncolumn').style.display='table-cell';}else{document.getElementById('actioncolumn').style.display='none';}{/literal}">&nbsp;
						</span>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<script type="text/javascript">
{literal}
function flot_showTooltip(x, y, contents) {
	jQuery('<div id="flot_tooltip">' + contents + '</div>').css(
		{position: 'absolute',display: 'none',top: y + 5,left: x + 5,border: '1px solid #fdd',padding: '2px','background-color': '#fee',opacity: 0.80}
	).appendTo("body").fadeIn(200);
}
jQuery(document).ready(function() {
	var previousPoint = null;
	var d1 = [
		{{/literal}value:{$funnelVals.8.sum},label:"{'Negotiation/Review'|@getTranslatedString:'Potentials'} ({$funnelVals.8.cnt}){literal}"},
		{{/literal}value:{$funnelVals.7.sum},label:"{'Proposal/Price Quote'|@getTranslatedString:'Potentials'} ({$funnelVals.7.cnt}){literal}"},
		{{/literal}value:{$funnelVals.6.sum},label:"{'Perception Analysis'|@getTranslatedString:'Potentials'} ({$funnelVals.6.cnt}){literal}"},
		{{/literal}value:{$funnelVals.5.sum},label:"{'Value Proposition'|@getTranslatedString:'Potentials'} ({$funnelVals.5.cnt}){literal}"},
		{{/literal}value:{$funnelVals.4.sum},label:"{'Needs Analysis'|@getTranslatedString:'Potentials'} ({$funnelVals.4.cnt}){literal}"},
		{{/literal}value:{$funnelVals.3.sum},label:"{'Id. Decision Makers'|@getTranslatedString:'Potentials'} ({$funnelVals.3.cnt}){literal}"},
		{{/literal}value:{$funnelVals.2.sum},label:"{'Qualification'|@getTranslatedString:'Potentials'} ({$funnelVals.2.cnt}){literal}"},
		{{/literal}value:{$funnelVals.1.sum},label:"{'Prospecting'|@getTranslatedString:'Potentials'} ({$funnelVals.1.cnt}){literal}"}
	];
	var options1 = { series: {pyramid: {active: true,show: true}},grid: { hoverable: true, clickable: true}};
	var p1 = jQuery.plot(jQuery('#pyramid'), [ d1 ], options1);
	jQuery('#pyramid').bind('plothover', function(event,pos, item){
		if (item) {
			if (item.found) {
				if (previousPoint != item.datapoint) {
					previousPoint = item.datapoint;
					jQuery("#flot_tooltip").remove();
					flot_showTooltip(pos.pageX, pos.pageY, item.label + ' >> ' + item.value);
				}
			}
		} else {
			jQuery('#flot_tooltip').remove();
			previousPoint = null;
		}
	});
});
{/literal}
</script>
<script>
	var fieldname = new Array({$VALIDATION_DATA_FIELDNAME});
	var fieldlabel = new Array({$VALIDATION_DATA_FIELDLABEL});
	var fielddatatype = new Array({$VALIDATION_DATA_FIELDDATATYPE});
</script>
</td>
	<td align=right valign=top><img src="{vtiger_imageurl('showPanelTopRight.gif', $THEME)}"></td>
</tr></table>
