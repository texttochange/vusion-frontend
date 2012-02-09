<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
	//Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
	//Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
/*
	Router::connect(
		'/:program/:controller/:action/*',
		array(
			'controller' => 'home',
			'action' => 'index'
			),
		array(
			'program' => '[a-zA-Z0-9]+'
			)
		);

	
	Router::connect(
		'/:program/:controller',
		array(
			'controller' => 'home',
			'action' => 'index'
			),
		array(
			'program' => '[a-zA-Z0-9]+'
			)
		);
*/

/**
*  get the local language form the subdomain such as fre.domain.com
*/
	$subdomain = substr(env("HTTP_HOST"), 0, strpos(env("HTTP_HOST"),"."));
	
	if (strlen($subdomain)>0) {
		Configure::write('Config.language', $subdomain);
	}


	Router::mapResources('Scripts', array('prefix' => '/:program/'));
	//Router::parseExtensions('json');
	Router::mapResources('Status', array('prefix' => '/:program/'));
	Router::mapResources('Programs');
	Router::parseExtensions('json');
	
/**
*  route for static controllers
*/
	Router::connect(
		'/programs/:action/*',
		array(
			'controller' => 'programs',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/programs',
		array(
			'controller' => 'programs',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/users/:action/*',
		array(
			'controller' => 'users',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/users',
		array(
			'controller' => 'users',
			'action' => 'index'
			)
		);

	Router::connect(
		'/programs_users/:action/*',
		array(
			'controller' => 'programs_users',
			'action' => 'index'
			)
		);

	Router::connect(
		'/programs_users',
		array(
			'controller' => 'programs_users',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/groups/:action/*',
		array(
			'controller' => 'groups',
			'action' => 'index'
			)
		);

	Router::connect(
		'/groups',
		array(
			'controller' => 'groups',
			'action' => 'index'
			)
		);
	
/**
*  route for program specific controllers
*/
	Router::connect(
		'/:program/:controller/:action/:id',
		array(
			'controller' => 'home',
			'action' => 'index'
			),
		array(
			'program' => '[a-zA-Z0-9]+',
			'id' => '[a-zA-Z0-9]+'
			)
		);

	Router::connect(
		'/:program/:controller/:action/*',
		array(
			'controller' => 'home',
			'action' => 'index'
			),
		array(
			'program' => '[a-zA-Z0-9]+'
			)
		);	

	Router::connect(
		'/:program/:controller',
		array(
			'controller' => 'home',
			'action' => 'index'
			),
		array(
			'program' => '[a-zA-Z0-9]+'
			)
		);
	
	Router::connect(
		'/:program',
		array(
			'controller' => 'home',
			'action' => 'index'
			),
		array(
			'program' => '[a-zA-Z0-9]+'
			)
		);



	
/**
 * Load all plugin routes.  See the CakePlugin documentation on 
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
	

	
