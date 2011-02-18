<?php
define("DB_ENGINE",'mysqli');
define("DB_ENCODING",'utf8');
define("DB_HOST",'localhost');
define("DB_PORT",'3306');
define("DB_NAME",'trunk2');
define("DB_USER",'root');
define("DB_PASS",'root');
define("DB_TABLE_PREFIX",'exponent');
define("SEF_URLS",'1');
define("TRACKING_COOKIE_EXPIRES",'30');
define("TRACKING_ARCHIVE_DELAY",'24');
define("TRACKING_ARCHIVE_TIME",'180');
define("SMTP_USE_PHP_MAIL",'0');
define("SMTP_SERVER",'localhost');
define("SMTP_PORT",'25');
define("SMTP_AUTHTYPE",'');
define("SMTP_USERNAME",'');
define("SMTP_PASSWORD",'');
define("SMTP_FROMADDRESS",'website@localhost');
define("SITE_TITLE",'My Test Exponent Site');
define("SITE_ALLOW_REGISTRATION",'0');
define("SITE_USE_CAPTCHA",'0');
define("SITE_404_TITLE",'Page Not Found');
define("SITE_404_HTML",exponent_unhtmlentities('The page you were looking for wasn&#039;t found.  It may have been moved or deleted.'));
define("SITE_403_REAL_HTML",exponent_unhtmlentities('&lt;h3&gt;Authorization Failed&lt;/h3&gt;You are not allowed to perform this operation.'));
define("SITE_KEYWORDS",'key,words,are,almost,useless now');
define("SITE_DESCRIPTION",'this is my site\'s description and should appear @ the top of my doc in the <head>');
define("SITE_DEFAULT_SECTION",'1');
define("SITE_WYSIWYG_EDITOR",'ckeditor');
define("SESSION_TIMEOUT_ENABLE",'1');
define("SESSION_TIMEOUT",7200);
define("SESSION_TIMEOUT_HTML",exponent_unhtmlentities('&lt;h3&gt;Expired Login Session&lt;/h3&gt;Your session has expired, because you were idle too long.  You will have to log back into the system to continue what you were doing.'));
define("ENABLE_SSL",'0');
define("SSL_URL",'https://my.domain/');
define("NONSSL_URL",'http://my.domain/');
define("FILE_DEFAULT_MODE_STR",'0600');
define("DIR_DEFAULT_MODE_STR",'0777');
define("USE_LANG",'eng_US');
define("ENABLE_WORKFLOW",'0');
define("ORGANIZATION_NAME",'Phillip\'s Site');
define("HELP_URL",'http://exponent-docs.org/');
define("USER_REGISTRATION_USE_EMAIL",'0');
define("USER_REGISTRATION_SEND_NOTIF",'0');
define("USER_REGISTRATION_NOTIF_SUBJECT",'New User Registration From Website');
define("USER_REGISTRATION_ADMIN_EMAIL",'');
define("USER_REGISTRATION_SEND_WELCOME",'0');
define("USER_REGISTRATION_WELCOME_SUBJECT",'Welcome to our website!');
define("USER_REGISTRATION_WELCOME_MSG",'');
define("MAINTENANCE_MODE",'0');
define("MAINTENANCE_MSG_HTML",exponent_unhtmlentities('&lt;p&gt;<br />	<br /><br />	<br /><br />	<br /><br />	<br /><br />	<br /><br />	<br /><br />	<br /><br />	stay out&lt;/p&gt;<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />'));
define("DEVELOPMENT",'1');
define("USE_LDAP",'0');
define("LDAP_SERVER",'not_configured');
define("LDAP_BASE_DN",'not_configured');
define("LDAP_BIND_USER",'not_configured');
define("LDAP_BIND_PASS",'not_configured');
define("SLINGBAR_TOP",'1');
define("DISPLAY_THEME_REAL",'basetheme');
define("DISPLAY_ATTRIBUTION",'lastfirst');
define("DISPLAY_DATETIME_FORMAT",'%D -- %T');
define("DISPLAY_DATE_FORMAT",'%d.%m.%y');
define("DISPLAY_TIME_FORMAT",'%T');
define("DISPLAY_START_OF_WEEK",'0');
define("COMMENTS_REQUIRE_LOGIN",'0');
define("COMMENTS_REQUIRE_APPROVAL",'1');
define("COMMENTS_REQUIRE_NOTIFICATION",'1');
define("COMMENTS_NOTIFICATION_EMAIL",'phillip@oicgroup.net');
define("ANTI_SPAM_CONTROL",'recaptcha');
define("RECAPTCHA_THEME",'red');
define("RECAPTCHA_PUB_KEY",'test');
define("RECAPTCHA_PRIVATE_KEY",'save me');
define("CURRENTCONFIGNAME",'Default');
define("HELP_ACTIVE",'1');
define("MINIFY",'0');
?>