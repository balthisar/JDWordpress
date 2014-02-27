<?php
/*************************************************************************************************
 * @file           JDBlogSetting.php
 *
 * @brief
 *
 * Part of plugin `JDWordpress`
 *
 * @details
 *
 * This file represents the interface to Wordpress settings.
 *
 *
 * @date           2014-02-12
 * @author         Jim Derry
 * @copyright      ©2014 by Jim Derry and balthisar.com
 * @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)
 *************************************************************************************************/


App::uses('AppModel', 'Model');


/** This file represents the interface to Wordpress settings. */
class JDBlogSetting extends AppModel
{
	public $useTable     = 'options';       ///< CakePHP standard.
	public $primaryKey   = 'option_id';     ///< CakePHP standard.
	public $useDbConfig  = 'JDWordpressDB'; ///< CakePHP standard.
	public $displayField = 'option_name';   ///< CakePHP standard.
}
