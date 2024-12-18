<?php
session_start();

// Script de nettoyage côté client
echo "<script>
    localStorage.removeItem('sessionStartTime');
    localStorage.removeItem('carbonSession');
</script>";

// Destruction de la session PHP
session_destroy();

// Redirection
header("Location: index.php");
exit();
?>