<?php
echo "<h2>🔐 Generador de Hash para PHP</h2>";

$password = '1234';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<p><strong>Contraseña:</strong> " . $password . "</p>";
echo "<p><strong>Hash generado:</strong> <code style='background:#f0f0f0;padding:10px;display:block;word-break:break-all;'>" . $hash . "</code></p>";

echo "<h3>📝 Copia este hash y ejecuta en phpMyAdmin:</h3>";
echo "<pre style='background:#f0f0f0;padding:15px;border-radius:5px;'>";
echo "UPDATE usuarios SET password_hash = '" . $hash . "' WHERE email = 'admin@ddp.com';";
echo "</pre>";

echo "<br><a href='login.php' style='background:#c0392b;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Ir al Login</a>";
?>