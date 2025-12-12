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
 * \file     core/triggers/interface_99_modMyodule_Upbuttonstrigger.class.php
 * \ingroup   upbuttons
 * \brief    Sample trigger
 */

/**
 * Trigger class
 */
class InterfaceUpbuttonstrigger
{
	/**
	 * @var DoliDB Database handler
	 */
	protected $db;

	/**
	 * @var string Name
	 */
	public $name;

	/**
	 * @var string Family
	 */
	public $family;

	/**
	 * @var string Description
	 */
	public $description;

	/**
	 * @var string Version
	 */
	public $version;

	/**
	 * @var string Picto
	 */
	public $picto;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "Triggers of this module are empty functions. They have no effect. They are provided for tutorial purpose only.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'upbuttons@upbuttons.png';
	}

	/**
	 * Return name of trigger
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Return description of trigger
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}

	/**
	 * Return version of trigger
	 *
	 * @return string Version of trigger file
	 */
	public function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') {
			return $langs->trans("Development");
		} elseif ($this->version == 'experimental') {
			return $langs->trans("Experimental");
		} elseif ($this->version == 'dolibarr') {
			return DOL_VERSION;
		} elseif ($this->version) {
			return $this->version;
		} else {
			return $langs->trans("Unknown");
		}
	}

	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "run_trigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param  string    $action   Event action code
	 * @param  Object    $object   Object
	 * @param  User      $user     Object user
	 * @param  Translate $langs    Object langs
	 * @param  conf      $conf     Object conf
	 * @return int                 <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, $user, $langs, $conf)
	{
		return 0;
	}
}
