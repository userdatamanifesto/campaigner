<?php

  // Configfile

  // Send the email via the local sendmail or an smtp server.
  define('CAMPAIGNER_SMTPMODE','sendmail');

  // The SMTP Server to use
  define('CAMPAIGNER_SMTPHOST','127.0.0.1');

  // Authenticate against the SMTP server
  define('CAMPAIGNER_SMTPAUTH', false);

  // SMTP server login
  define('CAMPAIGNER_SMTPUSERNAME','');

  // SMTP server login
  define('CAMPAIGNER_SMTPPASSWORD', '');

  // Sender email address
  define('CAMPAIGNER_SMTPFROMEMAIL', 'info@example.com');

  // Sender Name
  define('CAMPAIGNER_SMTPFROMNAME', 'Example Website');

  // Email subject
  define('CAMPAIGNER_SMTPFROMSUBJECT', 'Confirm Support');

  // Email mailtext
  define('CAMPAIGNER_SMTPMAILTEXT', 'Thanks a lot for supporting this website. Please click on this link to confirm your email. Your name will be listed on the website to show your support. Thanks a lot.');

  // Confirmation text
  define('CAMPAIGNER_ADDTEXT', 'We sent you a confirmation email. Please click on the link in the email to confirm your support.<br /><br />');

  // 2. Confirmation text
  define('CAMPAIGNER_CONFIRMTEXT', 'Thanks a lot for confirming your email address. Your name is now listed on the website. Thanks for supporting us.<br /><br />');

  // MySQL DB name
  define('CAMPAIGNER_DB_NAME', 'databasename');

  // MySQL login
  define('CAMPAIGNER_DB_LOGIN', 'dblogin');

  // MySQL password
  define('CAMPAIGNER_DB_PASSWD', '');

  // MySQL hostname
  define('CAMPAIGNER_DB_HOST', 'localhost');

  // MySQL port
  define('CAMPAIGNER_DB_PORT', '3306');

  // MySQL socket
  define('CAMPAIGNER_DB_SOCKET', '/tmp/mysql.sock');



?>
