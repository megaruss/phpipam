<?php

/**
 *	Edit powerDNS record
 ************************************************/

/* functions */
require( dirname(__FILE__) . '/../../../functions/functions.php');

# initialize user object
$Database 	= new Database_PDO;
$User 		= new User ($Database);
$Admin	 	= new Admin ($Database);
$Tools	 	= new Tools ($Database);
$Result 	= new Result ();
$PowerDNS 	= new PowerDNS ($Database);

# verify that user is logged in
$User->check_user_session();


// save settings for powerDNS default
$pdns = $PowerDNS->db_settings;

// default post
$post = $_POST;

# get record
if($_POST['action']!="add") {
	$record = $PowerDNS->fetch_record ($_POST['id']);
	$record!==false ? : $Result->show("danger", _("Invalid ID"), true, true);
}
# new record
else {
	// from IP table
	// we provide record hostname and strip domain from it
	if (!is_numeric($_POST['domain_id']) && !is_numeric($_POST['id'])) {
		// fetch all domains
		foreach($PowerDNS->fetch_all_domains () as $domain_s) {
			if (strpos($_POST['domain_id'],$domain_s->name) !== false) {
				$_POST['domain_id'] = $domain_s->id;
				break;
			}
		}
		// die if not existing
		if (!is_numeric($_POST['domain_id'])) {
			$Result->show("danger", _("Domain does not exist"), true, true);
		}
		else {
			$record = new StdClass ();
			$record->ttl = 3600;
			$record->name = $post['domain_id'];
			$record->content = $_POST['id'];
		}
	}
}

// get domain
$domain = $PowerDNS->fetch_domain ($_POST['domain_id']);
$domain!==false ? : $Result->show("danger", _("Invalid ID"), true, true);

// default
if (!isset($record)) {
	$record = new StdClass ();
	$record->ttl = 3600;
	$record->name = $domain->name;
}


# disable edit on delete
$readonly = $_POST['action']=="delete" ? "readonly" : "";
?>


<!-- header -->
<div class="pHeader"><?php print ucwords(_("$_POST[action]")); ?> <?php print _('DNS record'); ?> <?php print _('for domain'); ?> <strong><?php print $domain->name; ?></strong></div>

<!-- content -->
<div class="pContent">

	<form id="recordEdit">
	<table class="table table-noborder table-condensed">

	<!-- name  -->
	<tr>
		<td style="width:150px;"><?php print _('Name'); ?></td>
		<td>
			<input type="text" class="name form-control input-sm" name="name" placeholder="<?php print _('www.example.com'); ?>" value="<?php print $record->name; ?>" <?php print $readonly; ?>>
			<input type="hidden" name="action" value="<?php print $_POST['action']; ?>">
			<input type="hidden" name="id" value="<?php print @$_POST['id']; ?>">
			<input type="hidden" name="domain_id" value="<?php print @$_POST['domain_id']; ?>">

		</td>
	</tr>

	<!-- type -->
	<tr>
		<td><?php print _('Record type'); ?></td>
		<td>
			<select name="type" class="form-control input-w-auto input-sm" <?php print $readonly; ?>>
			<?php
			// loop
			foreach($PowerDNS->record_types as $type) {
				// active
				if ($type == @$record->type)	{ $selected = "selected"; }
				else							{ $selected = ""; }
				// print
				print "<option value='$type' $selected>$type</option>";
			}
			?>
			</select>
		</td>
	</tr>

	<!-- Content  -->
	<tr>
		<td><?php print _('Content'); ?></td>
		<td>
			<input type="text" class="name form-control input-sm" name="content" placeholder="<?php print _('10.10.10.1'); ?>" value="<?php print $record->content; ?>" <?php print $readonly; ?>>

		</td>
	</tr>

	<!-- TTL  -->
	<tr>
		<td><?php print _('TTL'); ?></td>
		<td>
			<select name="ttl" class="form-control input-w-auto input-sm" <?php print $readonly; ?>>
			<?php
			// loop
			foreach($PowerDNS->ttl as $k=>$type) {
				// active
				if ($k == @$record->ttl)		{ $selected = "selected"; }
				else							{ $selected = ""; }
				// print
				print "<option value='$k' $selected>$type</option>";
			}
			?>
			</select>
		</td>
	</tr>

	<!-- Prio  -->
	<tr>
		<td><?php print _('Priority'); ?></td>
		<td>
			<input type="text" class="name form-control input-sm input-w-100" name="prio" placeholder="<?php print _('Priority'); ?>" value="<?php print $record->prio; ?>" <?php print $readonly; ?>>

		</td>
	</tr>

	<!-- Disabled  -->
	<tr>
		<td><?php print _('Disabled'); ?></td>
		<td>
			<select name="disabled" class="form-control input-w-auto input-sm" <?php print $readonly; ?>>
				<option value="0"><?php print _('No'); ?></option>
				<option value="1" <?php if($record->disabled==1) print "selected='selected'"; ?>><?php print _('Yes'); ?></option>
			</select>
		</td>
	</tr>

	</table>
	</form>

</div>


<!-- footer -->
<div class="pFooter">
	<div class="btn-group">
		<button class="btn btn-sm btn-default hidePopups"><?php print _('Cancel'); ?></button>
		<button class="btn btn-sm btn-default <?php if($_POST['action']=="delete") { print "btn-danger"; } else { print "btn-success"; } ?>" id="editRecordSubmit"><i class="fa <?php if($_POST['action']=="add") { print "fa-plus"; } else if ($_POST['action']=="delete") { print "fa-trash-o"; } else { print "fa-check"; } ?>"></i> <?php print ucwords(_($_POST['action'])); ?></button>
	</div>
	<!-- result -->
	<div class="record-edit-result"></div>
</div>