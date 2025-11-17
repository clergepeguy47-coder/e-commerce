<?php
session_start();
$_SESSION = [];
session_destroy();
echo "Vous êtes déconnecté. <a href='login.php'>Se reconnecter</a>";
exit;
?>

