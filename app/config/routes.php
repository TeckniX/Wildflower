<?php
/**
 * Load my_routes.php first, so user routes have priority over Wildflower
 *
 * To add your custom routes, create file my_routes.php in this folder and add them there. When you update Wildflower you won't have to  merge this file with a new version.
 */
$myRoutesPath = dirname(__FILE__) . DS . 'my_routes.php';
if (file_exists($myRoutesPath)) {
	require_once($myRoutesPath);
}

/**
 * Wildflower routes
 *
 * Wildflower reservers these URL's:
 */
 
// Home page
Router::connect('/', array('controller' => 'pages', 'action' => 'view'));
Router::connect('/app/webroot/', array('controller' => 'pages', 'action' => 'view'));

// Contact form
Router::connect('/contact', array('controller' => 'messages', 'action' => 'index'));
Router::connect('/contact/create', array('controller' => 'messages', 'action' => 'create'));

// Sitemap related
Router::parseExtensions('xml');
Router::connect('/sitemap', array('controller' => 'pages', 'action' => 'sitemap'));
// Enforce the XML url param for the /sitemap link
//Router::connect('/sitemap', array('controller' => 'pages', 'action' => 'sitemap', 'url' => array('ext'  => 'xml')))

// Posts section
Router::connect('/rss', array('controller' => 'posts', 'action' => 'rss'));
Router::connect('/' . Configure::read('Wildflower.blogIndex'), array('controller' => 'posts', 'action' => 'index'));
Router::connect('/' . Configure::read('Wildflower.blogIndex') . '/*', array('controller' => 'posts', 'action' => 'index'));
Router::connect('/' . Configure::read('Wildflower.postsParent') . '/:slug', array('controller' => 'posts', 'action' => 'view'));
Router::connect('/c/:slug', array('controller' => 'posts', 'action' => 'category'));

// Wildflower admin routes
$prefix = Configure::read('Routing.admin');
//Router::connect("/$prefix", array('controller' => 'dashboards', 'action' => 'index', 'admin'=>true,'prefix'=>'admin'));
Router::connect("/$prefix", array('controller' => 'dashboards', 'action' => 'index', 'admin' => true));
Router::connect("/$prefix/:controller", array('admin' => true));
Router::connect("/$prefix/:controller/:action/*", array('admin' => true));

// Image thumbnails
// @TODO shorten to '/i/*'
$mprefix = Configure::read('Wildflower.mediaRoute');
Router::connect('/'.$mprefix.'/thumbnail/*', array('controller' => 'assets', 'action' => 'thumbnail'));
Router::connect('/'.$mprefix.'/thumbnail_by_id/*', array('controller' => 'assets', 'action' => 'thumbnail_by_id'));

// Search
Router::connect('/search', array('controller' => 'dashboards', 'action' => 'search'));

// Connect root pages slugs
App::import('Vendor', 'WfRootPagesCache', array('file' => 'WfRootPagesCache.php'));
WildflowerRootPagesCache::connect();
