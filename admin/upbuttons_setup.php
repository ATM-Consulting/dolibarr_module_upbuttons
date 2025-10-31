<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it is useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file     admin/upbuttons.php
 * \ingroup   upbuttons
 * \brief    Module setup page for UpButtons
 */
// Dolibarr environment
$res = @include '../../main.inc.php'; // From htdocs directory
if (! $res) {
	$res = @include '../../../main.inc.php'; // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php'; // Include the FormSetup class
require_once '../lib/upbuttons.lib.php';
require_once __DIR__.'/../backport/v17/core/lib/functions.lib.php';

// Translations
$langs->load('upbuttons@upbuttons');
$langs->load('admin'); // For Save, Modify, Cancel buttons

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$page_name = 'UpbuttonsSetup';

$formSetup = new FormSetup($db);
$item = $formSetup->newItem('UPBUTTON_HIDE_AVAILABLE_ACTION')->setAsYesNo();
$item = $formSetup->newItem('UPBUTTON_DISPLAY_FLOATING_MENU')->setAsYesNo()->helpText = $langs->trans('UPBUTTON_DISPLAY_FLOATING_MENU_HELP');
$item = $formSetup->newItem('UPBUTTON_DISPLAY_FLOATING_MENU_TYPE')->setAsSelect(['vertical' => 'Vertical', 'horizontal' => 'Horizontal'])->cssClass = 'minwidth200';

// sticky / fixed features
$item = $formSetup->newItem('UPBUTTON_STICKY_TAB')->setAsYesNo()->helpText = $langs->trans('UPBUTTON_STICKY_TAB_HELP');
$item = $formSetup->newItem('UPBUTTON_FTH_ENABLE')->setAsYesNo();
$item = $formSetup->newItem('UPBUTTON_FTH_THEME_USE_FIXED_TOPBAR')->setAsYesNo();
$item = $formSetup->newItem('UPBUTTON_FTH_STICKY_FILTERS')->setAsYesNo();

/*
 * Actions
 */
include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

/*
 * View
 */
llxHeader('', $langs->trans($page_name));
$varsForJs = [
	'conf' => [
		'UPBUTTON_DISPLAY_FLOATING_MENU' => getDolGlobalInt('UPBUTTON_DISPLAY_FLOATING_MENU')
	]
];
?>
<script>
	document.addEventListener(
		'DOMContentLoaded',
		() => ATM_MODULE_UPBUTTONS.initSetupPage(<?php echo json_encode($varsForJs) ?>)
	);
</script>
<?php

// Subheader
$linkback = '<a href="'.DOL_URL_ROOT.'/admin/modules.php">'
	.$langs->trans('BackToModuleList').'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = upbuttonsAdminPrepareHead();

print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, "upbuttons@upbuttons");
// Generate rows
print $formSetup->generateOutput(true);

print dol_get_fiche_end();
llxFooter();
$db->close();
