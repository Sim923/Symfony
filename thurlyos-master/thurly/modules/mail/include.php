<?
##############################################
# Thurly: SiteManager                        #
# Copyright (c) 2002-2010 Thurly             #
# http://www.thurlysoft.com                  #
# mailto:sources@thurlysoft.com              #
##############################################

global $DB;
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/mail/constants.php");

\Thurly\Main\Loader::registerAutoloadClasses(
	'mail',
	array(
		'CMail'                => 'classes/general/mail.php',
		'CMailError'           => 'classes/general/mail.php',
		'CMailHeader'          => 'classes/general/mail.php',
		'CMailAttachment'      => 'classes/general/mail.php',
		'CMailFilter'          => 'classes/general/mail.php',
		'CMailFilterCondition' => 'classes/general/mail.php',
		'CMailLog'             => 'classes/general/mail.php',

		'CMailbox'             => 'classes/'.strtolower($DB->type).'/mail.php',
		'CMailUtil'            => 'classes/'.strtolower($DB->type).'/mail.php',
		'CMailMessage'         => 'classes/'.strtolower($DB->type).'/mail.php',

		'CSMTPServer'          => 'classes/general/smtp.php',
		'CSMTPServerHost'      => 'classes/general/smtp.php',
		'CSMTPConnection'      => 'classes/general/smtp.php',

		'CMailImap'            => 'classes/general/imap.php',

		'CMailRestService'     => 'classes/general/rest.php',

		'CMailDomain'          => 'classes/general/domain.php',
		'CMailYandex'          => 'classes/general/yandex.php',
		'CMailDomain2'         => 'classes/general/domain2.php',
		'CMailYandex2'         => 'classes/general/yandex2.php',

		'CMailDomainRegistrar' => 'classes/general/domain_registrar.php',
		'CMailRegru'           => 'classes/general/regru.php',

		'Thurly\Mail\BaseException'     => 'lib/exception.php',
		'Thurly\Mail\ReceiverException' => 'lib/exception.php',
	)
);