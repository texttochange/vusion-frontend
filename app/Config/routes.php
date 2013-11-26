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
	//Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'programHome'));

/**
*  get the local language form the subdomain such as fre.domain.com
*/
	$subdomain = substr(env("HTTP_HOST"), 0, strpos(env("HTTP_HOST"),"."));

	if (strlen($subdomain)>0 && in_array($subdomain, array('eng','fre','spa'))) {
		Configure::write('Config.language', $subdomain);
	}


	Router::mapResources('programDialogues', array('prefix' => '/:program/'));
	//Router::parseExtensions('json');
	Router::mapResources('programHistory', array('prefix' => '/:program/'));
	Router::mapResources('programRequests', array('prefix' => '/:program/'));
	Router::mapResources('programParticipants', array('prefix'=> '/:program/'));
	Router::mapResources('Programs');
	//Router::mapResources('users');
	Router::parseExtensions('json', 'csv');

	
/**
*  route for static controllers
*/
    Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

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
	
	Router::connect(
		'/shortCodes/index/*',
		array(
			'controller' => 'shortCodes',
			'action' => 'index'
			)
		);

	Router::connect(
		'/shortCodes/:action/:id',
		array(
			'controller' => 'shortCodes',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/shortCodes/:action/*',
		array(
			'controller' => 'shortCodes',
			'action' => 'index'
			)
		);

	Router::connect(
		'/shortCodes',
		array(
			'controller' => 'shortCodes',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/admin',
		array(
			'controller' => 'admin',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/unmatchableReply/:action/*',
		array(
			'controller' => 'unmatchableReply',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/unmatchableReply',
		array(
			'controller' => 'unmatchableReply',
			'action' => 'index'
			)
		);

	Router::connect(
		'/documentation',
		array(
			'controller' => 'documentation',
			'action' => 'view'
			)
		);
	
	Router::connect(
		'/templates/index/*',
		array(
			'controller' => 'templates',
			'action' => 'index'
			)
		);

	Router::connect(
		'/templates/:action/:id',
		array(
			'controller' => 'templates',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/templates/:action/*',
		array(
			'controller' => 'templates',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/templates/',
		array(
			'controller' => 'templates',
			'action' => 'index'
			)
		);
	
	Router::connect(
		'/',
		array(
			'controller' => 'programs',
			'action' => 'index'
			)
		);

	
/**
*  route for program specific controllers
*/
	Router::connect(
		'/:program/:controller/:action/:id',
		array(
			'controller' => 'programHome',
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
			'controller' => 'programHome',
			'action' => 'index'
			),
		array(
			'program' => '[a-zA-Z0-9]+'
			)
		);	

	Router::connect(
		'/:program/:controller',
		array(
			'controller' => 'programHome',
			'action' => 'index'
			),
		array(
			'program' => '[a-zA-Z0-9]+'
			)
		);
	
	Router::connect(
		'/:program',
		array(
			'controller' => 'programHome',
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
	

	
