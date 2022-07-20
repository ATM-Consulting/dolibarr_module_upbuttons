<?php
/* Copyright (C) 2022 John BOTELLA    <john.botella@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */


/**
 * Function to get the value of global confs
 * For PHP8 : Avoid warnings when conf are not set
 *
 * @param string $constName Constant name to get
 * @param mixed  $default Default value if constant is unset
 * @return mixed return the value of const if exists or the default value
 */
function getGlobalConst($constName, $default = null)
{
	global $conf;

	return isset($conf->global->{$constName}) ? $conf->global->{$constName} : $default;
}


