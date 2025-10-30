<?php
/* Copyright (C) 2018 admin
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
 *
 * Library javascript to enable Browser notifications
 */

//if (!defined('NOREQUIREUSER'))  define('NOREQUIREUSER', '1');
//if (!defined('NOREQUIREDB'))    define('NOREQUIREDB','0');
//if (!defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '0');
//if (!defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (!defined('NOCSRFCHECK'))    define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);
//if (!defined('NOLOGIN'))        define('NOLOGIN', 1);
//if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', 1);
//if (!defined('NOREQUIREHTML'))  define('NOREQUIREHTML', 1);
//if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');

/**
 * \file    fixedtablehead/js/fixedtablehead.js.php
 * \ingroup fixedtablehead
 * \brief   JavaScript file for module FixedTableHead.
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/../main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/../main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Define js type
header('Content-Type: application/javascript');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');

?>

/* Javascript library of module FixedTableHead */
$( document ).ready(function() {
	var elem = $('#tablelines');
	if (elem.length) {
		if (elem.find('tbody').length === 0) {
			elem.prepend('<tbody></tbody>');
			elem.find('tr').each(function() {
				$(this).remove().appendTo('#tablelines tbody');
			});
		}

		if (elem.find('thead').length === 0) {
			elem.prepend('<thead></thead>');
			elem.find('tr:first').remove().appendTo('#tablelines thead');
		}

		elem.floatThead({
			position: 'fixed',
			top: <?php print !empty($conf->global->FIXEDTABLEHEAD_THEME_USE_FIXED_TOPBAR)?'$(\'#id-top\').height()':'0'; ?>,
			zIndex : 50
		});
	}

	var listelem = $('table.liste.listwithfilterbefore:not(.formdoc)');
	if (listelem.length) {
		if (listelem.find('tbody').length === 0) {
			listelem.prepend('<tbody></tbody>');
			listelem.find('tr').each(function() {
				$(this).remove().appendTo(listelem.find('tbody'));
			});
		}

		if (listelem.find('thead').length === 0) {
			listelem.prepend('<thead></thead>');
			listelem.find('tr.liste_titre').remove().appendTo(listelem.find('thead'));
			//listelem.find('tr.liste_titre_filter').remove().appendTo(listelem.find('thead'));
		}

		listelem.floatThead({
			position: 'fixed',
			top: <?php print !empty($conf->global->FIXEDTABLEHEAD_THEME_USE_FIXED_TOPBAR)?'$(\'#id-top\').height()':'0'; ?>,
			zIndex : 50
		});
		setTimeout(() => listelem.floatThead('reflow'), 0);
	}
});
