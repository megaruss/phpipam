<?php

/**
 * Script to print devices
 ***************************/

# verify that user is logged in
$User->check_user_session();

# fetch all Devices
$devices = $Admin->fetch_all_objects("devices");


# fetch custom fields
$custom = $Tools->fetch_custom_fields('devices');

# get hidden fields
$hidden_custom_fields = json_decode($User->settings->hiddenCustomFields, true);
$hidden_custom_fields = is_array(@$hidden_custom_fields['devices']) ? $hidden_custom_fields['devices'] : array();
?>

<h4><?php print _('Device management'); ?></h4>
<hr>
<div class="btn-group">
	<button class='btn btn-sm btn-default editSwitch' data-action='add'   data-switchid='' style='margin-bottom:10px;'><i class='fa fa-plus'></i> <?php print _('Add device'); ?></button>
	<a href="<?php print create_link("administration", "device-types"); ?>" class="btn btn-sm btn-default"><i class="fa fa-tablet"></i> <?php print _('Manage device types'); ?></a>
</div>

<?php
/* first check if they exist! */
if($devices===false) {
	$Result->show("warn alert-absolute", _('No devices configured').'!', false);
}
/* Print them out */
else {

	print '<table id="switchManagement" class="table table-striped table-auto table-top table-td-top">';

	# headers
	print '<tr>';
	print '	<th>'._('Hostname').'</th>';
	print '	<th>'._('IP address').'</th>';
	print '	<th>'._('Type').'</th>';
	print '	<th>'._('Model').'</th>';
	print '	<th><i class="icon-gray icon-info-sign" rel="tooltip" title="'._('Shows in which sections device will be visible for selection').'"></i> '._('Sections').'</th>';
	print '	<th class="actions"></th>';
	print '</tr>';

	# loop through devices
	$types = $Admin->fetch_all_objects("deviceTypes", "tid");

	foreach ($devices as $device) {
		//cast
		$device = (array) $device;

		//print details
		print '<tr>'. "\n";

		print '	<td><a href="'.create_link("tools","devices","hosts",$device['id']).'">'. $device['hostname'] .'</a></td>'. "\n";
		print '	<td>'. $device['ip_addr'] .'</td>'. "\n";
		print '	<td>';
		foreach ($types as $type) { 
			if ($type->tid == $device['type']) { 
				print($type->tname);
			}
		}
		print '</td>'. "\n";
		print '	<td>'. $device['model'] .'</td>'. "\n";

		//sections
		print '	<td class="sections">';
			$temp = explode(";",$device['sections']);
			if( (sizeof($temp) > 0) && (!empty($temp[0])) ) {
			foreach($temp as $line) {
				$section = $Sections->fetch_section(null, $line);
				if(!empty($section)) {
				print '<div class="switchSections text-muted">'. $section->name .'</div>'. "\n";
				}
			}
			}

		print '	</td>'. "\n";

		print '	<td class="actions">'. "\n";
		print "	<div class='btn-group'>";
		print "		<button class='btn btn-xs btn-default editSwitch' data-action='edit'   data-switchid='$device[id]'><i class='fa fa-pencil'></i></button>";
		print "		<button class='btn btn-xs btn-default editSwitch' data-action='delete' data-switchid='$device[id]'><i class='fa fa-times'></i></button>";
		print "	</div>";
		print '	</td>'. "\n";

		print '</tr>'. "\n";

	}
	print '</table>';
}
?>

<!-- edit result holder -->
<div class="switchManagementEdit"></div>
