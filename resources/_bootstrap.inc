<?php
/**
 * Require specific files
 */
require_once('config/_config.inc');
$sys_error = FALSE;
/**
 * Settings and configurations
 */
ini_set( 'register_globals', 0 );
/**
 * Common file paths
 */

/* CLASS PATH */
defined("CLASSES")
	or define("CLASSES", realpath(dirname(__FILE__) . '/classes'));
/* LIBRARY PATH */
	 defined("LIBRARIES")
		 or define("LIBRARIES", realpath(dirname(__FILE__) . '/libraries'));
/* GLOBAL OBJECTS PATH */
defined("OBJECTS")
	or define("OBJECTS", realpath(dirname(__FILE__) . '/objects'));
/* GLOBAL CONTENT PATH */
defined("CONTENT")
	or define("CONTENT", realpath(dirname(__FILE__) . '/content'));
/* PAGE TEMPLATE PATH */
defined("TEMPLATES")
	or define("TEMPLATES", realpath(dirname(__FILE__) . '/templates'));
/* SITE ROOT PATH */
defined("SITEROOT")
	or define("SITEROOT", realpath(dirname(dirname(__FILE__)) . '/public_html'));

/**
 * LIBRARIES
 */
require_once(LIBRARIES . '/stripe-php-master/init.php');
require_once(LIBRARIES . '/php-mailer/phpmail.php');
require_once(LIBRARIES . '/google-api-php-client-v2.18.3-PHP8.3/vendor/autoload.php');

/**
 * CLASSES
 */
require_once(CLASSES . '/api.class.php');
require_once(CLASSES . '/customer.class.php');
require_once(CLASSES . '/event.class.php');
require_once(CLASSES . '/form_processor.class.php');
require_once(CLASSES . '/invoice.class.php');
require_once(CLASSES . '/log.class.php');
require_once(CLASSES . '/login.class.php');
require_once(CLASSES . '/messages.class.php');
require_once(CLASSES . '/mysql.class.php');
require_once(CLASSES . '/page.class.php');
require_once(CLASSES . '/price.class.php');
require_once(CLASSES . '/product.class.php');
require_once(CLASSES . '/request.class.php');
require_once(CLASSES . '/settings.class.php');
require_once(CLASSES . '/stripe_interface.class.php');
require_once(CLASSES . '/user.class.php');
require_once(CLASSES . '/utility.class.php');
require_once(CLASSES . '/webhook.class.php');

/**
 * GLOBAL OBJECTS
 */
require_once(OBJECTS . '/mysql_dbo.php');
require_once(OBJECTS . '/settings.php');
require_once(OBJECTS . '/api_keys.php');
require_once(OBJECTS . '/phpmail.php');
