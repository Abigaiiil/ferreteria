<?php
echo "Sin zona horaria: " . date('Y-m-d H:i:s') . "<br>";

date_default_timezone_set('America/Mexico_City');
echo "Mexico City: " . date('Y-m-d H:i:s') . "<br>";

date_default_timezone_set('America/Monterrey');
echo "Monterrey: " . date('Y-m-d H:i:s') . "<br>";