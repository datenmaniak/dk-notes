<?php

/**
 * Script de inicialización para PostgreSQL en Kubernetes
 * Diseñado para ser ejecutado mediante 'include' dentro de php artisan tinker
 */

// 1. Configuración de acceso como Superusuario para la primera vez
$host = 'postgres-service.postgres.svc.cluster.local'; // DNS interno del clúster
$port = '5432';
$superUser = 'postgres'; 
// Reemplaza 'TU_CONTRASENA_MAESTRA' por la clave real del superusuario postgres de tu clúster
$superPassword = 'admin123'; 

// 2. Datos que se van a crear para la aplicación
$dbName = 'dknotes';
$appUser = 'dkuser';
$appPassword = '7shogun'; // Tu clave real de producción

try {
    echo "🔌 Conectando al servidor PostgreSQL como superusuario...\n";
    
    // Conexión inicial a la base de datos por defecto 'postgres' para poder operar
    $dsn = "pgsql:host=$host;port=$port;dbname=postgres";
    $pdo = new PDO($dsn, $superUser, $superPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "✅ Conexión establecida con éxito.\n";

    // 3. Crear la Base de Datos si no existe
    echo "📦 Verificando base de datos '$dbName'...\n";
    $dbCheck = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '$dbName'")->fetch();
    
    if (!$dbCheck) {
        $pdo->exec("CREATE DATABASE $dbName");
        echo "   -> Base de datos '$dbName' creada correctamente.\n";
    } else {
        echo "   -> La base de datos '$dbName' ya existe. Omitiendo creación.\n";
    }

    // 4. Crear el Usuario de la Aplicación si no existe
    echo "👤 Verificando usuario '$appUser'...\n";
    $userCheck = $pdo->query("SELECT 1 FROM pg_roles WHERE rolname = '$appUser'")->fetch();
    
    if (!$userCheck) {
        $pdo->exec("CREATE USER $appUser WITH PASSWORD '$appPassword'");
        echo "   -> Usuario '$appUser' creado correctamente con su contraseña.\n";
    } else {
        // Si ya existe, actualizamos la contraseña por seguridad para asegurar que sea '7shogun'
        $pdo->exec("ALTER USER $appUser WITH PASSWORD '$appPassword'");
        echo "   -> El usuario ya existía. Contraseña actualizada a la clave real.\n";
    }

    // 5. Asignación de Privilegios y Propiedad
    echo "🔐 Asignando privilegios y propiedad del objeto...\n";
    $pdo->exec("GRANT ALL PRIVILEGES ON DATABASE $dbName TO $appUser");
    $pdo->exec("ALTER DATABASE $dbName OWNER TO $appUser");
    
    echo "\n🎉 ¡Proceso completado con éxito! El terreno está preparado para las migraciones.\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR DE BASE DE DATOS: " . $e->getMessage() . "\n";
}