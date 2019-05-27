<?php
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
require_once 'modules/Forecasts/Forecasts.php';

function updatePotential($potentialId) {
	global $adb;
	$forecastIds = array();
	// Update Forecasts previously related to this potential
	$query = "select crmid from vtiger_crmentityrel where module='Forecasts' and relcrmid={$potentialId}";
	$res = $adb->query($query);
	while ($row=$adb->getNextRow($res)) {
		$forecastIds[] = $row['crmid'];
	}
	// Update Forecasts that match the new potential data
	$query = 'select crm_p.smownerid, p.closingdate, p.productcategory
		from vtiger_potential p
		join vtiger_crmentity crm_p on crm_p.crmid=p.potentialid
		where p.potentialid=?';
	$res = $adb->pquery($query, array($potentialId));
	$userId = $adb->query_result($res, 0, 'smownerid');
	$pCategory = $adb->query_result($res, 0, 'productcategory');
	$time = strtotime($adb->query_result($res, 0, 'closingdate'));
	$year = date('Y', $time);
	$month = date('n', $time);
	$bimonthly_p = floor(($month-1)/2)+1;
	$quaterly_p = floor(($month-1)/3)+1;
	$sixmonthly_p = floor(($month-1)/6)+1;
	$query = "select distinct f.forecastsid
		from vtiger_forecasts f
		join vtiger_crmentity crm_f on crm_f.crmid=f.forecastsid and crm_f.deleted=0
		left join vtiger_seproductsrel pr_rel on pr_rel.crmid={$potentialId}
		left join vtiger_crmentityrel se_rel on se_rel.crmid={$potentialId}
		where f.year={$year}
		and (
			f.frequency='YEARLY'
			or (f.frequency='MONTHLY' and f.period='PERIOD_{$month}')
			or (f.frequency='BIMONTHLY' and f.period='PERIOD_{$bimonthly_p}')
			or (f.frequency='QUARTERLY' and f.period='PERIOD_{$quaterly_p}')
			or (f.frequency='SIXMONTHLY' and f.period='PERIOD_{$sixmonthly_p}')
		) and (
			f.only_user=0
			or crm_f.smownerid={$userId}
		) and (
			(f.use_category=1 and f.productcategory='{$pCategory}')
			or
			(f.only_related=1 and (pr_rel.productid=f.relatedto or se_rel.relcrmid=f.relatedto))
			or
			(f.only_related=0 and f.use_category=0)
		)";

	$res = $adb->query($query);
	while ($row=$adb->getNextRow($res)) {
		if (!in_array($row['forecastsid'], $forecastIds)) {
			$forecastIds[] = $row['forecastsid'];
		}
	}
	// Actually update Forecasts
	$f = CRMEntity::getInstance('Forecasts');
	foreach ($forecastIds as $forecastId) {
		$f->id = $forecastId;
		$f->retrieve_entity_info($forecastId, 'Forecasts');
		$f->updateForecast();
	}
}
?>
