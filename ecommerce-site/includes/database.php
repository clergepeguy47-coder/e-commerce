
<?php
/**
 * Fichier de connexion à la base de données
 * Utilise MySQLi en mode procédural (conformément aux consignes)
 */
// Empêcher l'accès direct au fichier
if (!defined('SITE_ACCESS')) {
    die('Accès direct interdit');
}

// Connexion à la base de données
$conn = mysqli_connect('localhost', 'root', '', 'ecommerce_db');

if (!$conn) {
    die('Erreur de connexion à la base de données : ' . mysqli_connect_error());
}

// ==========================================
// CONNEXION À LA BASE DE DONNÉES
// ==========================================
 require_once __DIR__ . '/../config.php';
 
// Variable globale pour la connexion
$connection = null;

// Tentative de connexion
$connection = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Vérification de la connexion
if (!$connection) {
    // En mode debug, afficher l'erreur détaillée
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        $error_details = mysqli_connect_error();
        die("
            <div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border-left: 5px solid #f44336; font-family: Arial, sans-serif;'>
                <h3>❌ Erreur de connexion à la base de données</h3>
                <p><strong>Détails :</strong> {$error_details}</p>
                <p><strong>Vérifiez :</strong></p>
                <ul>
                    <li>Que WAMP/XAMPP est démarré</li>
                    <li>Que la base de données '{DB_NAME}' existe</li>
                    <li>Les paramètres dans config.php</li>
                    <li>Que MySQL fonctionne</li>
                </ul>
            </div>
        ");
    } else {
        // En production, message générique
        die("
            <div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border-left: 5px solid #f44336; font-family: Arial, sans-serif;'>
                <h3>Service temporairement indisponible</h3>
                <p>Nous rencontrons des difficultés techniques. Veuillez réessayer plus tard.</p>
            </div>
        ");
    }
}

// Définir l'encodage des caractères
if (!mysqli_set_charset($connection, DB_CHARSET)) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "<div style='background: #fff3e0; color: #ef6c00; padding: 10px; margin: 10px; border-left: 3px solid #ff9800;'>";
        echo "⚠ Attention : Impossible de définir l'encodage " . DB_CHARSET;
        echo "</div>";
    }
}

// ==========================================
// FONCTIONS UTILITAIRES DATABASE
// ==========================================

/**
 * Fonction pour sécuriser les requêtes (échapper les caractères spéciaux)
 * @param string $string - Chaîne à sécuriser
 * @return string - Chaîne sécurisée
 */
function secure_input($string) {
    global $connection;
    return mysqli_real_escape_string($connection, trim($string));
}

/**
 * Fonction pour exécuter une requête sécurisée
 * @param string $query - Requête SQL
 * @return resource|bool - Résultat de la requête
 */
function execute_query($query) {
    global $connection;
    
    $result = mysqli_query($connection, $query);
    
    // En cas d'erreur et mode debug activé
    if (!$result && defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "<div style='background: #ffebee; color: #c62828; padding: 15px; margin: 10px; border-left: 3px solid #f44336;'>";
        echo "<strong>❌ Erreur SQL :</strong><br>";
        echo "<code>" . mysqli_error($connection) . "</code><br>";
        echo "<strong>Requête :</strong><br>";
        echo "<code>" . htmlspecialchars($query) . "</code>";
        echo "</div>";
    }
    
    return $result;
}

/**
 * Fonction pour récupérer une ligne de résultat
 * @param resource $result - Résultat d'une requête
 * @return array|null - Ligne de données ou null
 */
function fetch_assoc($result) {
    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Fonction pour récupérer toutes les lignes d'un résultat
 * @param resource $result - Résultat d'une requête
 * @return array - Tableau de toutes les lignes
 */
function fetch_all($result) {
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

/**
 * Fonction pour compter le nombre de lignes
 * @param resource $result - Résultat d'une requête
 * @return int - Nombre de lignes
 */
function count_rows($result) {
    if ($result) {
        return mysqli_num_rows($result);
    }
    return 0;
}

/**
 * Fonction pour récupérer l'ID du dernier enregistrement inséré
 * @return int - Dernier ID inséré
 */
function get_last_insert_id() {
    global $connection;
    return mysqli_insert_id($connection);
}

/**
 * Fonction pour compter les lignes affectées par une requête
 * @return int - Nombre de lignes affectées
 */
function get_affected_rows() {
    global $connection;
    return mysqli_affected_rows($connection);
}

/**
 * Fonction pour vérifier si une table existe
 * @param string $table_name - Nom de la table
 * @return bool - True si la table existe
 */
function table_exists($table_name) {
    global $connection;
    $table_name = secure_input($table_name);
    $query = "SHOW TABLES LIKE '{$table_name}'";
    $result = execute_query($query);
    return $result && count_rows($result) > 0;
}

/**
 * Fonction pour vérifier si la base de données est opérationnelle
 * @return bool - True si tout fonctionne
 */
function check_database_health() {
    global $connection;
    
    // Test de connexion
    if (!$connection) {
        return false;
    }
    
    // Test d'une requête simple
    $result = execute_query("SELECT 1");
    if (!$result) {
        return false;
    }
    
    // Vérifier que les tables principales existent
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

/**
 * Fonction pour fermer proprement la connexion
 */
function close_database_connection() {
    global $connection;
    if ($connection && is_resource($connection)) {
        mysqli_close($connection);
        $connection = null;
    }
}

/**
 * Fonction de transaction - début
 */
function begin_transaction() {
    global $connection;
    return mysqli_autocommit($connection, false);
}

/**
 * Fonction de transaction - validation
 */
function commit_transaction() {
    global $connection;
    $result = mysqli_commit($connection);
    mysqli_autocommit($connection, true);
    return $result;
}

/**
 * Fonction de transaction - annulation
 */
function rollback_transaction() {
    global $connection;
    $result = mysqli_rollback($connection);
    mysqli_autocommit($connection, true);
    return $result;
}

// ==========================================
// INITIALISATION ET NETTOYAGE
// ==========================================

// Enregistrer la fonction de fermeture pour qu'elle s'exécute automatiquement
register_shutdown_function('close_database_connection');

// Test de santé de la base de données au démarrage (en mode debug uniquement)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    if (check_database_health()) {
        // En mode très verbeux, on peut décommenter cette ligne
        // echo "<div style='background: #e8f5e8; color: #2e7d32; padding: 5px; margin: 5px;'>✅ Base de données opérationnelle</div>";
    }
}

// ==========================================
// CONSTANTES UTILES
// ==========================================

// Statuts de connexion
define('DB_CONNECTED', $connection !== null);
define('DB_ERROR_NONE', 0);
define('DB_ERROR_CONNECTION', 1);
define('DB_ERROR_QUERY', 2);

// Messages d'erreur standard
define('DB_MSG_CONNECTION_ERROR', 'Impossible de se connecter à la base de données');
define('DB_MSG_QUERY_ERROR', 'Erreur lors de l\'exécution de la requête');
define('DB_MSG_NO_DATA', 'Aucune donnée trouvée');

?>