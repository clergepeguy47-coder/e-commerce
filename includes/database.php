
<?php
/**
 * Fichier de connexion à la base de données
 * Utilise MySQLi en mode procédural
 */

//Empêcher l'accès direct au fichier
if (!defined('SITE_ACCESS')) {
    die('Accès direct interdit');
}
// Inclure la configuration
require_once __DIR__ . '/../config.php';

$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (!$conn) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        $error_details = mysqli_connect_error();
        die("
            <div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border-left: 5px solid #f44336; font-family: Arial, sans-serif;'>
                <h3>❌ Erreur de connexion à la base de données</h3>
                <p><strong>Détails :</strong> {$error_details}</p>
                <p><strong>Vérifiez :</strong></p>
                <ul>
                    <li>Que WAMP/XAMPP est démarré</li>
                    <li>Que la base de données \"" . DB_NAME . "\" existe</li>
                    <li>Les paramètres dans config.php</li>
                    <li>Que MySQL fonctionne</li>
                </ul>
            </div>
        ");
    } else {
        die("
            <div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border-left: 5px solid #f44336; font-family: Arial, sans-serif;'>
                <h3>Service temporairement indisponible</h3>
                <p>Nous rencontrons des difficultés techniques. Veuillez réessayer plus tard.</p>
            </div>
        ");
    }
}

// Définir l'encodage
mysqli_set_charset($conn, DB_CHARSET);

// ==========================================
// FONCTIONS UTILITAIRES DATABASE
// ==========================================

function secure_input($string) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($string));
}

function execute_query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);

    if (!$result && defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "<div style='background: #ffebee; color: #c62828; padding: 15px; margin: 10px; border-left: 3px solid #f44336;'>";
        echo "<strong>❌ Erreur SQL :</strong><br>";
        echo "<code>" . mysqli_error($conn) . "</code><br>";
        echo "<strong>Requête :</strong><br>";
        echo "<code>" . htmlspecialchars($query) . "</code>";
        echo "</div>";
    }

    return $result;
}

function fetch_assoc($result) {
    return $result ? mysqli_fetch_assoc($result) : null;
}

function fetch_all($result) {
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function count_rows($result) {
    return $result ? mysqli_num_rows($result) : 0;
}

function get_last_insert_id() {
    global $conn;
    return mysqli_insert_id($conn);
}

function get_affected_rows() {
    global $conn;
    return mysqli_affected_rows($conn);
}

function table_exists($table_name) {
    global $conn;
    $table_name = secure_input($table_name);
    $result = execute_query("SHOW TABLES LIKE '{$table_name}'");
    return $result && count_rows($result) > 0;
}

function check_database_health() {
    global $conn;

    if (!$conn || !execute_query("SELECT 1")) {
        return false;
    }

    $required_tables = ['users', 'products', 'categories', 'orders'];
    foreach ($required_tables as $table) {
        if (!table_exists($table)) {
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                echo "<div style='background: #fff3e0; color: #ef6c00; padding: 10px; margin: 10px;'>";
                echo "⚠ Table manquante : {$table}. Exécutez install.php";
                echo "</div>";
            }
            return false;
        }
    }

    return true;
}

function close_database_conn() {
    global $conn;
    if ($conn) {
        mysqli_close($conn);
        $conn = null;
    }
}
// ==========================================
// FERMETURE AUTOMATIQUE ET VÉRIFICATION
// ==========================================

// Enregistrer la fonction de fermeture pour qu'elle s'exécute automatiquement
register_shutdown_function('close_database_conn');

// Vérification de santé en mode debug
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    check_database_health();
}

// ==========================================
// FONCTIONS DE TRANSACTION
// ==========================================

function begin_transaction() {
    global $conn;
    return mysqli_autocommit($conn, false);
}

function commit_transaction() {
    global $conn;
    $result = mysqli_commit($conn);
    mysqli_autocommit($conn, true);
    return $result;
}

function rollback_transaction() {
    global $conn;
    $result = mysqli_rollback($conn);
    mysqli_autocommit($conn, true);
    return $result;
}

// ==========================================
// CONSTANTES UTILES
// ==========================================

define('DB_CONNECTED', isset($conn) && $conn !== null);
define('DB_ERROR_NONE', 0);
define('DB_ERROR_CONNECTION', 1);
define('DB_ERROR_QUERY', 2);

define('DB_MSG_CONNECTION_ERROR', 'Impossible de se connecter à la base de données');
define('DB_MSG_QUERY_ERROR', 'Erreur lors de l\'exécution de la requête');
define('DB_MSG_NO_DATA', 'Aucune donnée trouvée');
?>

