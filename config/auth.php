<?php
/**
 * Funciones de autenticación exclusivas del área administrativa.
 * Este archivo no debe cargarse desde las páginas públicas.
 */

function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $segura = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('boutique_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $segura,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

iniciarSesionSegura();

function asegurarTablaAdministradores(PDO $conexion): void
{
    $conexion->exec("CREATE TABLE IF NOT EXISTS administradores (
        id_admin INT AUTO_INCREMENT PRIMARY KEY,
        nombre_admin VARCHAR(120) NOT NULL,
        email_admin VARCHAR(150) NOT NULL UNIQUE,
        password_admin VARCHAR(255) NOT NULL,
        rol_admin VARCHAR(40) NOT NULL DEFAULT 'Administrador',
        estado_admin TINYINT(1) NOT NULL DEFAULT 1,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $total = (int)$conexion->query("SELECT COUNT(*) FROM administradores")->fetchColumn();
    if ($total === 0) {
        $stmt = $conexion->prepare("INSERT INTO administradores
            (nombre_admin, email_admin, password_admin, rol_admin, estado_admin)
            VALUES (:nombre, :email, :password, 'Administrador', 1)");
        $stmt->execute([
            'nombre' => 'Administrador Boutique',
            'email' => 'admin@boutique.com',
            'password' => password_hash('Admin123', PASSWORD_DEFAULT),
        ]);
    }
}

function adminAutenticado(): bool
{
    return !empty($_SESSION['admin_boutique']['id']);
}

function adminActual(): array
{
    return $_SESSION['admin_boutique'] ?? [];
}

function protegerPanelAdmin(): void
{
    if (!adminAutenticado()) {
        header('Location: index.php');
        exit;
    }
}

function iniciarSesionAdmin(PDO $conexion, string $email, string $password): array
{
    asegurarTablaAdministradores($conexion);

    $stmt = $conexion->prepare("SELECT id_admin, nombre_admin, email_admin, password_admin, rol_admin
        FROM administradores
        WHERE email_admin = :email AND estado_admin = 1
        LIMIT 1");
    $stmt->execute(['email' => strtolower(trim($email))]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_admin'])) {
        return ['ok' => false, 'mensaje' => 'Correo o contraseña incorrectos.'];
    }

    session_regenerate_id(true);
    $_SESSION['admin_boutique'] = [
        'id' => (int)$admin['id_admin'],
        'nombre' => $admin['nombre_admin'],
        'email' => $admin['email_admin'],
        'rol' => $admin['rol_admin'],
    ];

    return ['ok' => true, 'mensaje' => 'Acceso correcto.'];
}

function asegurarRecuperacionPassword(PDO $conexion): void
{
    asegurarTablaAdministradores($conexion);
    $conexion->exec("CREATE TABLE IF NOT EXISTS recuperacion_password_admin (
        id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
        id_admin INT NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE,
        fecha_expiracion DATETIME NOT NULL,
        utilizado TINYINT(1) NOT NULL DEFAULT 0,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_recuperacion_admin (id_admin)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function crearTokenRecuperacion(PDO $conexion, string $email): ?string
{
    asegurarRecuperacionPassword($conexion);
    $stmt = $conexion->prepare("SELECT id_admin FROM administradores WHERE email_admin=:email AND estado_admin=1 LIMIT 1");
    $stmt->execute(['email' => strtolower(trim($email))]);
    $id = $stmt->fetchColumn();
    if (!$id) return null;
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $conexion->prepare("UPDATE recuperacion_password_admin SET utilizado=1 WHERE id_admin=:id AND utilizado=0")->execute(['id'=>$id]);
    $conexion->prepare("INSERT INTO recuperacion_password_admin (id_admin, token_hash, fecha_expiracion) VALUES (:id,:hash,DATE_ADD(NOW(), INTERVAL 30 MINUTE))")
        ->execute(['id'=>$id,'hash'=>$hash]);
    return $token;
}

function restablecerPasswordAdmin(PDO $conexion, string $token, string $password): bool
{
    asegurarRecuperacionPassword($conexion);
    $hash = hash('sha256', $token);
    $stmt = $conexion->prepare("SELECT id_recuperacion,id_admin FROM recuperacion_password_admin WHERE token_hash=:hash AND utilizado=0 AND fecha_expiracion>NOW() LIMIT 1");
    $stmt->execute(['hash'=>$hash]);
    $fila = $stmt->fetch();
    if (!$fila) return false;
    $conexion->beginTransaction();
    try {
        $conexion->prepare("UPDATE administradores SET password_admin=:password WHERE id_admin=:id")
            ->execute(['password'=>password_hash($password, PASSWORD_DEFAULT),'id'=>$fila['id_admin']]);
        $conexion->prepare("UPDATE recuperacion_password_admin SET utilizado=1 WHERE id_recuperacion=:id")
            ->execute(['id'=>$fila['id_recuperacion']]);
        $conexion->commit();
        return true;
    } catch (Throwable $e) {
        $conexion->rollBack();
        throw $e;
    }
}
