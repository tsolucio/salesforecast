/*************************************************************************************************
 * Copyright 2013 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO vtiger CRM customizations.
 * You can copy, adapt and distribute the work under the "Attribution-NonCommercial-ShareAlike"
 * Vizsage Public License (the "License"). You may not use this file except in compliance with the
 * License. Roughly speaking, non-commercial users may share and modify this code, but must give credit
 * and share improvements. However, for proper details please read the full License, available at
 * http://vizsage.com/license/Vizsage-License-BY-NC-SA.html and the handy reference for understanding
 * the full license at http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html. Unless required by
 * applicable law or agreed to in writing, any software distributed under the License is distributed
 * on an  "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the
 * License terms of Creative Commons Attribution-NonCommercial-ShareAlike 3.0 (the License).
 *************************************************************************************************
 *  Module       : Forecasts
 *  Version      : 5.4.0
 *  Author       : JPL TSolucio, S. L.
 *************************************************************************************************/

jQuery(document).ready(function() {
	if (jQuery('select[name="frequency"]').val()!=undefined) {
		jQuery('select[name="frequency"]').change(toggleFCFields);
		jQuery('select[name="period"]').change(toggleFCFields);
		toggleFCFields();
	}
	if (jQuery('input[name="only_related"]').val()!=undefined) {
		jQuery('input[name="only_related"]').change(toggleFCCat);
		toggleFCCat();
	}
	if (jQuery('input[name="use_category"]').val()!=undefined) {
		jQuery('input[name="use_category"]').change(toggleFCRel);
		toggleFCRel();
	}
});

function toggleFCCat() {
	if (jQuery('input[name="only_related"]').is(':checked')) {
		fld = jQuery('input[name="use_category"]');
		fld.prop('disabled',true);
		fld.css('background-color','gray');
		fld = jQuery('select[name="productcategory"]');
		fld.prop('disabled',true);
		fld.css('background-color','gray');
	} else {
		fld = jQuery('input[name="use_category"]');
		fld.removeAttr('disabled');
		fld.css('background-color','white');
		fld = jQuery('select[name="productcategory"]');
		fld.removeAttr('disabled');
		fld.css('background-color','white');
	}
}

function toggleFCRel() {
	if (jQuery('input[name="use_category"]').is(':checked')) {
		fld = jQuery('input[name="only_related"]');
		fld.prop('disabled',true);
		fld.css('background-color','gray');
		fld = jQuery('input[name="relatedto_display"]');
		fld.prop('disabled',true);
		fld.css('background-color','gray');
		fld.closest('td').children("img").hide();
	} else {
		fld = jQuery('input[name="only_related"]');
		fld.removeAttr('disabled');
		fld.css('background-color','white');
		fld = jQuery('input[name="relatedto_display"]');
		fld.removeAttr('disabled');
		fld.css('background-color','white');
		fld.closest('td').children("img").show();
	}
}

function toggleFCFields() {
	if (jQuery('select[name="frequency"] option:selected').val()==undefined)
		return 0;
	var freq = jQuery('select[name="frequency"] option:selected').val();
	var perd = Number(jQuery('select[name="period"] option:selected').val().substring(7));
	var ini = 1;var fin = 1;var mescnt = 1;
	var act_these = {};
	switch (freq) {
		case 'MONTHLY':
			ini = perd;
			fin = perd;
			break;
		case 'BIMONTHLY':
			ini = (perd - 1) * 2 + 1;
			fin = ini + 1;
			break;
		case 'QUARTERLY':
			ini = (perd - 1) * 3 + 1;
			fin = ini + 2;
			break;
		case 'SIXMONTHLY':
			ini = (perd - 1) * 6 + 1;
			fin = ini + 5;
			break;
		case 'YEARLY':
		default:
			ini = 1;
			fin = 12;
			break;
	}
	for (mescnt = ini; mescnt <= fin; mescnt++) {
		act_these['m'+mescnt.toString()] = 1;
	}
	var blkflds = { q: 'quota_', c: 'committed_', b: 'bestcase_'};
	for (mescnt = 1; mescnt <= 12; mescnt++) {
		var curmes = 'm'+mescnt.toString();
		for (var fldname in blkflds) {
			var fld = jQuery('input[name="'+blkflds[fldname]+mescnt.toString()+'"]');
			fld.removeAttr('readonly');
			fld.css('background-color','white');
			if (!(curmes in act_these)) {
				fld.prop('readonly',true);
				fld.val(0);
				fld.css('background-color','gray');
			}
		}
	}
}
