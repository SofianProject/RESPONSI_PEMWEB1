<?php
session_start();
session_unset();
session_destroy();
header('Location: /RESPONSI_PEMWEB1/PanenLokal/login.php');
exit;
?>
