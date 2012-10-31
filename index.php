<html>
<head>
<title>Test</title>
<link rel="stylesheet" href="campaigner/style.css" type="text/css"  />
</head>
<body bgcolor="#EEEEEE">

<?php
  require('campaigner/libcampaigner.php');
  $response=CAMPAIGNER::listener();
  echo('<center><span class="notify">'.$response.'</span></center>');
?>

here goes some content


<?php

CAMPAIGNER::show();

?>


</body>
</html>
