<?php
/*************************************************************************************************
 * @file           JDBlogAuthor.php
 *
 * @brief
 *
 * Part of plugin `JDWordpress`
 *
 * @details
 *
 * This file represents the interface to post authors.
 *
 *
 * @date           2014-02-12
 * @author         Jim Derry
 * @copyright      ©2014 by Jim Derry and balthisar.com
 * @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)
 *************************************************************************************************/


App::uses('AppModel', 'Model');


/** This class represents the interface to post authors. */
class JDBlogAuthor extends AppModel
{
	public $useTable     = 'users';                     ///< CakePHP standard.
	public $useDbConfig  = 'JDWordpressDB';             ///< CakePHP standard.
	public $primaryKey   = 'ID';                        ///< CakePHP standard.
	public $displayField = 'display_name';              ///< CakePHP standard.
	public $order        = ['display_name' => 'ASC'];   ///< CakePHP standard.
	/// CakePHP standard.
	public $hasMany      = [
								'JDBlogPost' => [
									'className'  => 'JDWordpress.JDBlogPost',
									'conditions' => [
													'JDBlogPost.post_status' => 'publish',
													'JDBlogPost.post_type'   => 'post',
									],
									'order'      => 'JDBlogPost.post_date_gmt DESC',
									'foreignKey' => 'post_author',
								]
							];

}
