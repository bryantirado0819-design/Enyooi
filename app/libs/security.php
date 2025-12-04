<?php

class Security {

    public static function init() {
        self::setSecurityHeaders();
        self::cleanIncomingData();
        self::validateSession();
    }

    private static function setSecurityHeaders() {
        // Prevenir Clickjacking
        header("X-Frame-Options: SAMEORIGIN");
        // Protección XSS del navegador
        header("X-XSS-Protection: 1; mode=block");
        // Evitar sniffing de MIME types
        header("X-Content-Type-Options: nosniff");
        // Referrer Policy
        header("Referrer-Policy: strict-origin-when-cross-origin");
        // Forzar HTTPS (Si está disponible SSL)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        }
        // Content Security Policy (Básico para permitir scripts locales y CDNs confiables)
        header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'; frame-ancestors 'self';");
    }

    private static function cleanIncomingData() {
        $_GET  = self::sanitizeRecursivo($_GET);
        $_POST = self::sanitizeRecursivo($_POST);
        $_COOKIE = self::sanitizeRecursivo($_COOKIE);
        
        // Verificar JSON input
        $inputJSON = file_get_contents('php://input');
        if (!empty($inputJSON)) {
            $decoded = json_decode($inputJSON, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Reemplazar el input raw con datos saneados si es necesario, 
                // aunque PHP no permite modificar php://input directamente, 
                // validamos aquí patrones maliciosos.
                self::scanForMaliciousPatterns(json_encode($decoded));
            }
        }
    }

    private static function sanitizeRecursivo($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeRecursivo($value);
            }
        } else {
            // Filtro básico de SQL Injection y XSS para strings
            // Nota: No usar htmlspecialchars aquí si los datos van a DB (usar PDO bindings),
            // pero sí eliminamos nulos y etiquetas script obvias.
            $data = str_replace(chr(0), '', $data); // Null byte injection
            
            // Detectar patrones peligrosos
            self::scanForMaliciousPatterns($data);
            
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }

    private static function scanForMaliciousPatterns($value) {
        $patterns = [
            '/union\s+select/i',
            '/information_schema/i',
            '/<script.*?>/i',
            '/javascript:/i',
            '/base64_decode/i',
            '/GLOBALS/i',
            '/_REQUEST/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                self::logAttack("Patrón malicioso detectado: " . $pattern);
                http_response_code(403);
                die("Acceso Denegado por Enyooi Security Shield.");
            }
        }
    }

    private static function validateSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configuración segura de cookies de sesión antes de iniciar
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
        }
        
        // Protección contra Session Hijacking
        if (isset($_SESSION['last_ip']) && $_SESSION['last_ip'] !== $_SERVER['REMOTE_ADDR']) {
            session_unset();
            session_destroy();
            // Opcional: redirigir a login
        }
        $_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR'];

        // Protección contra Session Fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Regenerar ID cada 30 mins
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }

    private static function logAttack($message) {
        $logFile = __DIR__ . '/../../security/logs/waf_incidents.log';
        $entry = date('Y-m-d H:i:s') . " - IP: " . $_SERVER['REMOTE_ADDR'] . " - " . $message . PHP_EOL;
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        file_put_contents($logFile, $entry, FILE_APPEND);
    }
}