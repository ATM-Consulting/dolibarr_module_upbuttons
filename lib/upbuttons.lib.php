<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/upbuttons.lib.php
 *	\ingroup	upbuttons
 *	\brief		This file is an example module library
 *				Put some comments here
 */

/**
 * @return array
 */
function upbuttonsAdminPrepareHead(): array
{
	global $langs, $conf;

	$langs->load("upbuttons@upbuttons");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/upbuttons/admin/upbuttons_setup.php", 1);
	$head[$h][1] = $langs->trans("Parameters");
	$head[$h][2] = 'settings';
	$h++;
	$head[$h][0] = dol_buildpath("/upbuttons/admin/upbuttons_about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@upbuttons:/upbuttons/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@upbuttons:/upbuttons/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'upbuttons');

	return $head;
}

/**
 * E.g. 'UPBUTTONS_OPTION_ENABLE' -> 'upbuttonsOptionEnable'
 * @param string $snakeCaseIdentifier  e.g. 'UPBUTTONS_OPTION'
 * @return string
 */
function snakeToCamel(string $snakeCaseIdentifier): string
{
	return preg_replace_callback(
		'/_(.)/',
		function ($m) {
			return strtoupper($m[1]);
		},
		strtolower($snakeCaseIdentifier)
	);
}

/**
 * Generic function to prepare initial context data for javascript.
 * The returned array should be encoded in JSON and echoed (either
 * as a variable assignment or as a parameter to a function call).
 *
 * @param array $exports What you want to export. Expected format:
 *                       [
 *                       'conf' => ['MY_CONF' => 'bool', 'XYZ' => 'string'],
 *                       'langs' => ['MyTranslationKey', ...],
 *                       'permissions' => ['thismodule.read', ...],
 *                       ]
 * @return array[]  An array with the same keys as the input array,
 *                  but the sub-arrays are different: they contain
 *                  the actual value of the confs, translations and
 *                  user permissions.
 */
function prepareJsContext(array $exports): array
{
	global $user, $langs, $dolibarr_main_prod;

	$exportLangs = [];
	$exportConfs = [];
	$exportPermissions = [];

	foreach ($exports['conf'] ?? [] as $confName => $confType) {
		//      $shortName = snakeToCamel($confName);
		if ($confType === 'bool') {
			$confValue = boolval(getDolGlobalInt($confName));
		} elseif ($confType === 'int') {
			$confValue = getDolGlobalInt($confName);
		} elseif ($confType === 'string') {
			$confValue = getDolGlobalString($confName);
		}
		$exportConfs[$confName] = $confValue;
		$exportLangs[$confName] = $langs->trans($confName);
	}

	foreach ($exports['langs'] ?? [] as $langKey) {
		$exportLangs[$langKey] = $langs->trans($langKey);
	}

	foreach ($exports['permissions'] ?? [] as $permissionStr) {
		$permissionDef = explode('.', $permissionStr);
		$permissionValue = $user->hasRight(...$permissionDef);
		$exportPermissions[$permissionStr] = $permissionValue;
	}

	return [
		'conf'        => $exportConfs,
		'langs'       => $exportLangs,
		'permissions' => $exportPermissions,
		'dolibarr_main_prod' => boolval($dolibarr_main_prod ?? true),
	];
}

/**
 * The initial version of UpButtons mixed PHP and javascript.
 * This function helps accomplish a better separation by
 * gathering the data needed by the javascript front-end in
 * one place.
 *
 * @return array[]
 */
function prepareJsContextUpButtons(): array
{
	global $langs;
	$langs->load('upbuttons@upbuttons');
	$exports = [
		'conf'        => [
			'UPBUTTON_STICKY_TAB'                 => 'bool',
			'UPBUTTON_HIDE_AVAILABLE_ACTION'      => 'bool',
			'UPBUTTON_DISPLAY_FLOATING_MENU'      => 'bool',
			'UPBUTTON_DISPLAY_FLOATING_MENU_TYPE' => 'string',
			'UPBUTTON_FTH_ENABLE'                 => 'bool',
			'UPBUTTON_USE_FIXED_TOPBAR'           => 'bool',
		],
		'langs'       => ['LinksActions'],
		'permissions' => [
			'upbuttons.UseAllButton',
			'upbuttons.UseSingleButton',
		],
	];

	$ret = prepareJsContext($exports);

	// HTML snippets used by UpButtons:

	// HTML skeletton for the sliding side-drawer menu
	ob_start();
	?>
	<div id="upbuttons-floating-menu" class="--closed">
		<div class="upbuttons-floating-menu__flex-container">
			<div class="upbuttons-close-button">
				<span></span>
				<span></span>
				<span></span>
			</div>
			<div class="upbuttons-container"></div>
		</div>
	</div>
	<?php
	$ret['html']['upbuttonsFloatingMenu'] = ob_get_clean();

	// HTML snippet for the (empty) dropdown list of action buttons
	ob_start();
	?>
	<div id="nav-dropdown">
		<nav id="upbuttons-nav">
			<a href="#" class="butAction">
				<?php echo $langs->trans('LinksActions') ?>
			</a>
			<ul class="upbuttons-list-of-action-buttons"></ul>
		</nav>
	</div>
	<?php
	$ret['html']['upbuttonsNavDropdown'] = ob_get_clean();

	// HTML snippet for the "single button" mode
	ob_start();
	?>
	<a href="javascript:_=>0;" id="upbuttons-single-button" style="display: none">
		<?php echo img_picto('', 'all@upbuttons') ?>
	</a>
	<?php
	$ret['html']['singleButton'] = ob_get_clean();


	$ret['picto']['all'] = img_picto('', 'all@upbuttons');
	return $ret;
}
