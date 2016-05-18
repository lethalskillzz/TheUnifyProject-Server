<?php

/**
 * Database configuration
 */
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_NAME', 'TheUnifyProject');
define('DB_INSTANCE', '/cloudsql/theunifyproject-1218:theunifyprojectdb');



define('PROJECT_URL', 'http://192.168.56.1/TheUnifyProject/');
//define('PROJECT_URL', 'https://theunifyproject-1218.appspot.com/');

define('PROFILE_PIC_DIR', 'pic\\');
define('FEED_IMG_DIR', 'img\\');
define('SHOP_IMG_DIR', 'shopping\\img\\');
define('REPO_IMG_DIR', 'repository\\img\\');
define('DIGEST_IMG_DIR', 'digest\\img\\');
define('PROFILE_PIC_URL', PROJECT_URL.'profile/pic/');
define('FEED_IMG_URL', PROJECT_URL.'feed/img/');
define('SHOP_IMG_URL', PROJECT_URL.'extra/shopping/img/');
define('REPO_IMG_URL', PROJECT_URL.'extra/repository/img/');
define('DIGEST_IMG_URL', PROJECT_URL.'extra/digest/img/');
define('PDF_JS_URL', PROJECT_URL.'pdf.js/web/viewer.html?url=');

define('GOOGLE_API_KEY', 'AIzaSyBbx7Xq_oUQ6Km3e_S-6KFDEuQTWeKPewI');

define('NOTIFICATION_FOLLOW', 0);
define('NOTIFICATION_MENTION', 1);
define('NOTIFICATION_COMMENT', 2);
define('NOTIFICATION_LIKE', 3);

define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USERNAME_ALREADY_EXIST', 2);
define('MOBILE_ALREADY_EXIST', 3);

define('LIST_FOLLOWING', 'list_following');
define('LIST_FOLLOWERS', 'list_followers');
define('LIST_LIKE', 'list_like');

define('OPERATION_FAILED', 0);
define('OPERATION_SUCCESSFULL', 1);

define('USER_FOLLOWING', 0);
define('USER_FOLLOWERS', 1);

define('CAMPUS_BOSSO', 'Bosso');
define('CAMPUS_GIDAN_KWANO', 'Gidan Kwano');

define('SCHOOL_BUS', 'School Bus');
define('TAXI', 'Taxi');



