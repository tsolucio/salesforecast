<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'data/CRMEntity.php';
require_once 'data/Tracker.php';
require_once 'modules/Forecasts/forecasts.inc.php';

class Forecasts extends CRMEntity {
	public $db;
	public $log;

	public $table_name = 'vtiger_forecasts';
	public $table_index= 'forecastsid';
	public $column_fields = array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;
	public $HasDirectImageField = false;
	public $moduleIcon = array('library' => 'standard', 'containerClass' => 'slds-icon_container slds-icon-standard-account', 'class' => 'slds-icon', 'icon'=>'forecasts');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = array('vtiger_forecastscf', 'forecastsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = array('vtiger_crmentity', 'vtiger_forecasts', 'vtiger_forecastscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_forecasts'   => 'forecastsid',
		'vtiger_forecastscf' => 'forecastsid',
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = array(
		/* Format: Field Label => array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'Forecasts Nr.'=> array('forecasts' => 'forecast_no'),
		'Assigned To' => array('crmentity' => 'smownerid')
	);
	public $list_fields_name = array(
		/* Format: Field Label => fieldname */
		'Forecasts Nr.'=> 'forecast_no',
		'Assigned To' => 'assigned_user_id'
	);

	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'forecast_no';

	// For Popup listview and UI type support
	public $search_fields = array(
		/* Format: Field Label => array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'Forecasts Nr.'=> array('forecasts' => 'forecast_no')
	);
	public $search_fields_name = array(
		/* Format: Field Label => fieldname */
		'Forecasts Nr.'=> 'forecast_no'
	);

	// For Popup window record selection
	public $popup_fields = array('forecast_no');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	public $sortby_fields = array();

	// For Alphabetical search
	public $def_basicsearch_col = 'forecast_no';

	// Column value to use on detail view record text display
	public $def_detailview_recname = 'forecast_no';

	// Required Information for enabling Import feature
	public $required_fields = array('forecast_no'=>1);

	// Callback function list during Importing
	public $special_functions = array('set_import_assigned_user');

	public $default_order_by = 'forecast_no';
	public $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = array('createdtime', 'modifiedtime', 'forecast_no');

	public function save_module($module) {
		global $adb;
		if ($this->HasDirectImageField) {
			$this->insertIntoAttachment($this->id, $module);
		}
		// Check values
		$period = $this->getPeriod();
		$max_period = 12/$this->getMonthsForPeriod();
		if ($period>$max_period || $period<1) {
			if ($period>$max_period) {
				$period = $max_period;
			}
			if ($period<1) {
				$period = 1;
			}
		}

		// Calculate values and totals
		$first_month = $this->getFirstMonth();
		$last_month = $this->getLastMonth();
		$total_quota = 0;
		$total_committed = 0;
		$total_bestcase = 0;
		$quota = array();
		$committed = array();
		$bestcase = array();
		$pcquota = array();
		for ($month=1; $month<=12; $month++) {
			if ($month>=$first_month && $month<=$last_month) {
				$quota[$month] = CurrencyField::convertToDBFormat($this->column_fields['quota_'.$month], null, true);
				$committed[$month] = CurrencyField::convertToDBFormat($this->column_fields['committed_'.$month], null, true);
				$bestcase[$month] = CurrencyField::convertToDBFormat($this->column_fields['bestcase_'.$month], null, true);
				$pcquota[$month] = 0;
				$total_quota += $quota[$month];
				$total_committed += $committed[$month];
				$total_bestcase += $bestcase[$month];
			} else {
				$quota[$month] = 0;
				$committed[$month] = 0;
				$bestcase[$month] = 0;
				$pcquota[$month] = 0;
			}
		}
		if ($total_quota==0) {
			$total_pcquota = 0;
		} else {
			$total_pcquota = $total_committed/$total_quota*100;
		}

		// Save new values for forecast
		$query = "update vtiger_forecasts set
			period='PERIOD_{$period}',
			quota_1={$quota[1]}, quota_2={$quota[2]}, quota_3={$quota[3]}, quota_4={$quota[4]}, quota_5={$quota[5]}, quota_6={$quota[6]},
			quota_7={$quota[7]}, quota_8={$quota[8]}, quota_9={$quota[9]}, quota_10={$quota[10]}, quota_11={$quota[11]}, quota_12={$quota[12]},
			committed_1={$committed[1]}, committed_2={$committed[2]}, committed_3={$committed[3]}, committed_4={$committed[4]}, committed_5={$committed[5]},
			committed_6={$committed[6]}, committed_7={$committed[7]}, committed_8={$committed[8]}, committed_9={$committed[9]}, committed_10={$committed[10]},
			committed_11={$committed[11]}, committed_12={$committed[12]},
			bestcase_1={$bestcase[1]}, bestcase_2={$bestcase[2]}, bestcase_3={$bestcase[3]},bestcase_4={$bestcase[4]},bestcase_5={$bestcase[5]},bestcase_6={$bestcase[6]},
			bestcase_7={$bestcase[7]},bestcase_8={$bestcase[8]},bestcase_9={$bestcase[9]},bestcase_10={$bestcase[10]},bestcase_11={$bestcase[11]},bestcase_12={$bestcase[12]},
			pcquota_1={$pcquota[1]}, pcquota_2={$pcquota[2]}, pcquota_3={$pcquota[3]}, pcquota_4={$pcquota[4]}, pcquota_5={$pcquota[5]}, pcquota_6={$pcquota[6]},
			pcquota_7={$pcquota[7]}, pcquota_8={$pcquota[8]}, pcquota_9={$pcquota[9]}, pcquota_10={$pcquota[10]}, pcquota_11={$pcquota[11]}, pcquota_12={$pcquota[12]},
			total_quota={$total_quota}, total_committed={$total_committed}, total_bestcase={$total_bestcase}, total_pcquota={$total_pcquota}
			where forecastsid={$this->id}";
		$adb->query($query);

		// Update Forecast fields with potentials
		$this->updateForecast();
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type) {
		global $adb;
		if ($event_type == 'module.postinstall') {
			// TODO Handle post installation actions
			$module = Vtiger_Module::getInstance($modulename);
			$modPotentials = Vtiger_Module::getInstance('Potentials');
			$block = Vtiger_Block::getInstance('LBL_OPPORTUNITY_INFORMATION', $modPotentials);
			$field = new Vtiger_Field();
			$field->name = 'productcategory';
			$field->label = 'Category';
			$field->uitype = 15;
			$block->addField($field);
			$adb->query("UPDATE vtiger_field SET fieldname = 'productcategory' WHERE tablename='vtiger_service' and columnname='servicecategory'");
			$this->setModuleSeqNumber('configure', $modulename, 'FC-', '000001');
			// picklist dependency Frequency > Period
			$sql = "INSERT INTO `vtiger_picklist_dependency` (`id`, `tabid`, `sourcefield`, `targetfield`, `sourcevalue`, `targetvalues`, `criteria`) VALUES";
			$tabid = $module->id;
			$nextid = $adb->getUniqueID("vtiger_picklist_dependency");
			$adb->pquery(
				"$sql (?,?,'frequency','period','BIMONTHLY','[".'"PERIOD_1","PERIOD_2","PERIOD_3","PERIOD_4","PERIOD_5","PERIOD_6"]'."',NULL)",
				array($nextid,$tabid)
			);
			$nextid = $adb->getUniqueID("vtiger_picklist_dependency");
			$adb->pquery("$sql (?, ?, 'frequency', 'period', 'QUARTERLY', '[".'"PERIOD_1","PERIOD_2","PERIOD_3","PERIOD_4"]'."', NULL)", array($nextid,$tabid));
			$nextid = $adb->getUniqueID("vtiger_picklist_dependency");
			$adb->pquery("$sql (?, ?, 'frequency', 'period', 'SIXMONTHLY', '[".'"PERIOD_1","PERIOD_2"]'."', NULL)", array($nextid,$tabid));
			$nextid = $adb->getUniqueID("vtiger_picklist_dependency");
			$adb->pquery("$sql (?, ?, 'frequency', 'period', 'YEARLY', '[".'"PERIOD_1"]'."', NULL)", array($nextid,$tabid));
		} elseif ($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} elseif ($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} elseif ($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} elseif ($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} elseif ($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
	}

	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// public function save_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	public function getMonthsForPeriod() {
		switch ($this->column_fields['frequency']) {
			case 'MONTHLY':
				$period_size = 1;
				break;
			case 'BIMONTHLY':
				$period_size = 2;
				break;
			case 'QUARTERLY':
				$period_size = 3;
				break;
			case 'SIXMONTHLY':
				$period_size = 6;
				break;
			case 'YEARLY':
				$period_size = 12;
				break;
		}
		return $period_size;
	}

	public function getPeriod() {
		switch ($this->column_fields['period']) {
			case 'PERIOD_1':
				$value = 1;
				break;
			case 'PERIOD_2':
				$value = 2;
				break;
			case 'PERIOD_3':
				$value = 3;
				break;
			case 'PERIOD_4':
				$value = 4;
				break;
			case 'PERIOD_5':
				$value = 5;
				break;
			case 'PERIOD_6':
				$value = 6;
				break;
			case 'PERIOD_7':
				$value = 7;
				break;
			case 'PERIOD_8':
				$value = 8;
				break;
			case 'PERIOD_9':
				$value = 9;
				break;
			case 'PERIOD_10':
				$value = 10;
				break;
			case 'PERIOD_11':
				$value = 11;
				break;
			case 'PERIOD_12':
				$value = 12;
				break;
			default:
				$value = null;
				break;
		}
		return $value;
	}

	public function getFirstMonth() {
		return ($this->getPeriod()-1)*$this->getMonthsForPeriod()+1;
	}

	public function getLastMonth() {
		return $this->getPeriod()*$this->getMonthsForPeriod();
	}

	public function updateForecast() {
		global $adb;
	  // Delete Forecasts-Potentials relations
		$query = "delete from vtiger_crmentityrel where crmid={$this->id}";
		$adb->query($query);
	  // Read forecast data
		$query = "select f.*, crm_f.smownerid
	  from vtiger_forecasts f
	  join vtiger_crmentity crm_f on crm_f.crmid=f.forecastsid
	  where f.forecastsid={$this->id}";
		$res = $adb->query($query);
		$forecastData = $adb->getNextRow($res, false);
	  // Prepare queries
		$year = $forecastData['year'];
		$first_month = $this->getFirstMonth();
		$last_month = $this->getLastMonth();
		$conditions = array( 1 );
		if ($forecastData['only_user']) {
			$conditions[] = "crm_p.smownerid={$forecastData['smownerid']}";
		}
		if ($forecastData['use_category']) {
			$conditions[] = "p.productcategory='{$forecastData['productcategory']}'";
		}
		if ($forecastData['only_related']) {
			$conditions[] = "(spr.productid={$forecastData['relatedto']} or cer.relcrmid={$forecastData['relatedto']})";
		}
		$conditions_str = implode(' and ', $conditions);
	  // Get closed potentials total amount
		$amount_closed = array();
		$amount_pipeline = array();
		for ($m=$first_month; $m<=$last_month; $m++) {
			$amount_closed[$m] = 0;
			$amount_pipeline[$m] = 0;
			$query = "select distinct(p.potentialid), p.amount, p.sales_stage
	    from vtiger_potential p
	    join vtiger_crmentity crm_p on crm_p.crmid=p.potentialid and crm_p.deleted=0
	    left join vtiger_seproductsrel spr on spr.crmid=p.potentialid
	    left join vtiger_crmentityrel cer on cer.crmid=p.potentialid
	    where year(p.closingdate)={$year}
	    and month(p.closingdate)={$m}
	    and {$conditions_str}";
			$res = $adb->query($query);
			while ($row = $adb->getNextRow($res, false)) {
				if ($row['sales_stage']=='Closed Won') {
					$amount_closed[$m] += $row['amount'];
				} elseif ($row['sales_stage']!='Closed Lost') {
					$amount_pipeline[$m] += $row['amount'];
				}
			  // Relate potential to forecast
				$query = "insert into vtiger_crmentityrel set crmid={$this->id}, module='Forecasts', relcrmid={$row['potentialid']}, relmodule='Potentials'";
				$adb->query($query);
			}
		}
	  // Recalculate totals
		$total_closed = 0;
		$total_pipeline = 0;
		$update_fields = array();
		for ($m=1; $m<=12; $m++) {
			$value = $forecastData['closed_'.$m];
			if ($m>=$first_month && $m<=$last_month) {
				$value = $amount_closed[$m];
				if ($forecastData["quota_{$m}"]!=0) {
					$pcquota = $value/$forecastData["quota_{$m}"]*100;
				} else {
					$pcquota = 0;
				}
				$update_fields[] = "closed_{$m}={$value}";
				$update_fields[] = "pcquota_{$m}=".$pcquota;
			}
			$total_closed += $value;
			$value = $forecastData['pipeline_'.$m];
			if ($m>=$first_month && $m<=$last_month) {
				$value = $amount_pipeline[$m];
				$update_fields[] = "pipeline_{$m}={$value}";
			}
			$total_pipeline += $value;
		}
		if (empty((float)$forecastData['total_quota'])) {
			$total_pcquota = 0;
		} else {
			$total_pcquota = $total_closed/$forecastData['total_quota']*100;
		}
		$update_fields_str = implode(', ', $update_fields);
		$query = "update vtiger_forecasts
			set total_closed={$total_closed}, total_pipeline={$total_pipeline}, total_pcquota={$total_pcquota}, {$update_fields_str}
			where forecastsid={$this->id}";
		$adb->query($query);
	}

	public function getFunnelValues() {
		global $log, $currentModule, $adb;
		$log->debug('> getFunnelValues');
		$this->retrieve_entity_info($this->id, $currentModule);
		$conditions = array( 1 );
		if ($this->column_fields['use_category']) {
			$conditions[] = "vtiger_potential.productcategory='{$this->column_fields['productcategory']}'";
		}
		if ($this->column_fields['only_related']) {
			$conditions[] = "(vtiger_seproductsrel.productid={$this->column_fields['relatedto']} or vtiger_crmentityrel.relcrmid={$this->column_fields['relatedto']})";
		}
		if ($this->column_fields['only_user']) {
			$conditions[] = "vtiger_crmentity.smownerid={$this->column_fields['assigned_user_id']}";
		}
		$conditions_str = implode(' and ', $conditions);
		$ini = 1;
		$fin = 1;
		$perd = substr($this->column_fields['period'], 7);
		switch ($this->column_fields['frequency']) {
			case 'MONTHLY':
				$ini = $perd;
				$fin = $perd;
				break;
			case 'BIMONTHLY':
				$ini = ($perd - 1) * 2 + 1;
				$fin = $ini + 1;
				break;
			case 'QUARTERLY':
				$ini = ($perd - 1) * 3 + 1;
				$fin = $ini + 2;
				break;
			case 'SIXMONTHLY':
				$ini = ($perd - 1) * 6 + 1;
				$fin = $ini + 5;
				break;
			case 'YEARLY':
			default:
				$ini = 1;
				$fin = 12;
				break;
		}
		$months = array();
		for ($mescnt = $ini; $mescnt <= $fin; $mescnt++) {
			$months[] = $mescnt;
		}
		$months = implode(',', $months);
		$query = "SELECT vtiger_potential.sales_stage,count(*) as sscnt, sum(amount) as ssamount
			FROM vtiger_potential
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_potential.potentialid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			LEFT JOIN vtiger_seproductsrel on vtiger_seproductsrel.crmid=vtiger_potential.potentialid
			LEFT JOIN vtiger_crmentityrel on vtiger_crmentityrel.crmid=vtiger_potential.potentialid
			WHERE vtiger_crmentity.deleted = 0 AND year(vtiger_potential.closingdate)={$this->column_fields['year']}
				and month(vtiger_potential.closingdate) in ({$months}) and {$conditions_str}
			GROUP BY vtiger_potential.sales_stage";

		// 1  => 'Prospecting'
		// 2  => 'Qualification'
		// 3  => 'Id. Decision Makers'
		// 4  => 'Needs Analysis'
		// 5  => 'Value Proposition'
		// 6  => 'Perception Analysis'
		// 7  => 'Proposal/Price Quote'
		// 8  => 'Negotiation/Review'
		$funnelrdo = array(	);
		// initialize array
		for ($fv = 1; $fv <= 8; $fv++) {
			$funnelrdo[$fv] = array('cnt'=>0,'sum'=>0);
		}
		$frdo = $adb->query($query);
		while ($ssval = $adb->fetch_array($frdo)) {
			switch ($ssval['sales_stage']) {
				case 'Prospecting':
					$funnelrdo[1] = array('cnt'=>$ssval['sscnt'],'sum'=>$ssval['ssamount']);
					break;
				case 'Qualification':
					$funnelrdo[2] = array('cnt'=>$ssval['sscnt'],'sum'=>$ssval['ssamount']);
					break;
				case 'Id. Decision Makers':
					$funnelrdo[3] = array('cnt'=>$ssval['sscnt'],'sum'=>$ssval['ssamount']);
					break;
				case 'Needs Analysis':
					$funnelrdo[4] = array('cnt'=>$ssval['sscnt'],'sum'=>$ssval['ssamount']);
					break;
				case 'Value Proposition':
					$funnelrdo[5] = array('cnt'=>$ssval['sscnt'],'sum'=>$ssval['ssamount']);
					break;
				case 'Perception Analysis':
					$funnelrdo[6] = array('cnt'=>$ssval['sscnt'],'sum'=>$ssval['ssamount']);
					break;
				case 'Proposal/Price Quote':
					$funnelrdo[7] = array('cnt'=>$ssval['sscnt'],'sum'=>$ssval['ssamount']);
					break;
				case 'Negotiation/Review':
					$funnelrdo[8] = array('cnt'=>$ssval['sscnt'],'sum'=>$ssval['ssamount']);
					break;
			}
		}

		$log->debug("Exiting getFunnelValues method ...");
		return $funnelrdo;
	}

	public function getPotentials($month, $id, $cur_tab_id, $rel_tab_id, $actions = false) {
		global $log, $currentModule;
		$log->debug("Entering getPotentials(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once "modules/$related_module/$related_module.php";
		$other = new $related_module();
		$singular_modname = vtlib_toSingular($related_module);

		$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;

		$button = '';
		if ($actions) {
			if (is_string($actions)) {
				$actions = explode(',', strtoupper($actions));
			}
			$i18nrm = getTranslatedString($related_module, $related_module);
			$i18nrms = getTranslatedString($singular_modname, $related_module);
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT').' '.$i18nrm. "' class='crmbutton small edit' type='button' "
					."onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable"
					."&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='"
					.getTranslatedString('LBL_SELECT'). ' ' . $i18nrm ."'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). ' '. $i18nrms ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). ' ' . $i18nrms ."'>&nbsp;";
			}
		}

		$this->retrieve_entity_info($id, $this_module);

		$conditions = array( 1 );
		if ($this->column_fields['use_category']) {
			$conditions[] = "vtiger_potential.productcategory='{$this->column_fields['productcategory']}'";
		}
		if ($this->column_fields['only_related']) {
			$conditions[] = "(vtiger_seproductsrel.productid={$this->column_fields['relatedto']} or vtiger_crmentityrel.relcrmid={$this->column_fields['relatedto']})";
		}
		if ($this->column_fields['only_user']) {
			$conditions[] = "vtiger_crmentity.smownerid={$this->column_fields['assigned_user_id']}";
		}
		$conditions_str = implode(' and ', $conditions);

		$query = "SELECT vtiger_potential.*, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
				case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name
			FROM vtiger_potential
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_potential.potentialid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			LEFT JOIN vtiger_seproductsrel on vtiger_seproductsrel.crmid=vtiger_potential.potentialid
			LEFT JOIN vtiger_crmentityrel on vtiger_crmentityrel.crmid=vtiger_potential.potentialid
			WHERE vtiger_crmentity.deleted = 0 AND year(vtiger_potential.closingdate)={$this->column_fields['year']}
			  and month(vtiger_potential.closingdate)={$month} and {$conditions_str}";
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null) {
			$return_value = array();
		}
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug('Exiting getPotentials method ...');
		return $return_value;
	}

	public function getPotentialsMonth_1($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(1, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_2($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(2, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_3($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(3, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_4($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(4, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_5($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(5, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_6($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(6, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_7($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(7, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_8($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(8, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_9($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(9, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_10($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(10, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_11($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(11, $id, $cur_tab_id, $rel_tab_id, $actions);
	}

	public function getPotentialsMonth_12($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		return $this->getPotentials(12, $id, $cur_tab_id, $rel_tab_id, $actions);
	}
}
?>
