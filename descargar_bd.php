<?php
// descargar_bd.php
// Permite descargar la base de datos SQLite desde Render

$archivo = __DIR__ . '/gorilla_tools.db';

if (file_exists($archivo)) {
    // Configurar cabeceras para forzar la descarga
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="gorilla_tools.db"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($archivo));
    
    // Leer el archivo y enviarlo al navegador
    readfile($archivo);
    exit;
} else {
    echo "❌ El archivo de base de datos no existe en: " . __DIR__;
    echo "<br><br>";
    echo "📁 Archivos en este directorio:<br>";
    echo "<ul>";
    foreach (scandir(__DIR__) as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
}
?>