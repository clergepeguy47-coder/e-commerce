<?php
/**
 * Fichier : user-functions.php
 * Description : Fonctions de gestion des utilisateurs (authentification, inscription, sessions)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Inscrit un nouvel utilisateur
 */
function registerUser($prenom, $nom, $email, $password, $conn, $telephone = '', $adresse = '', $ville = '', $code_postal = '') {
    $prenom = trim($prenom);
    $nom = trim($nom);
    $email = trim(strtolower($email));
    $telephone = trim($telephone);

    // Validation email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Adresse email invalide.'];
    }

    // Validation mot de passe
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.'];
    }

    // Vérifier si email existe déjà
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        return ['success' => false, 'message' => 'Cette adresse email est déjà utilisée.'];
    }
    mysqli_stmt_close($stmt);

    // Hasher le mot de passe
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insérer l'utilisateur
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (prenom, nom, email, password, telephone, adresse, ville, code_postal, role, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'client', NOW())"
    );

    mysqli_stmt_bind_param($stmt, "ssssssss", $prenom, $nom, $email, $password_hash, $telephone, $adresse, $ville, $code_postal);

    if (mysqli_stmt_execute($stmt)) {
        $user_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return ['success' => true, 'message' => 'Inscription réussie !', 'user_id' => $user_id];
    } else {
        $error = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        return ['success' => false, 'message' => "Erreur lors de l'inscription : $error"];
    }
}

/**
 * Connecte un utilisateur
 */
function login_user($email_or_username, $password): bool {
    global $conn;

    $email_or_username = trim($email_or_username);

    // Recherche par email ou username
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? OR username = ?");
    mysqli_stmt_bind_param($stmt, "ss", $email_or_username, $email_or_username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user && password_verify($password, $user['password'])) {
        // Créer la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['logged_in'] = true;

        // Mettre à jour la dernière connexion
        $update_stmt = mysqli_prepare($conn, "UPDATE users SET updated_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);

        return true;
    }

    return false;
}

/**
 * Déconnecte l'utilisateur
 */
function logoutUser() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
    session_start();
}

/**
 * Vérifie si un utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirige vers login si non connecté
 */
function requireLogin($redirect_url = '') {
    if (!isLoggedIn()) {
        if (!empty($redirect_url)) {
            $_SESSION['redirect_after_login'] = $redirect_url;
        }
        header('Location: /pages/login.php');
        exit();
    }
}

/**
 * Redirige vers la page d'accueil si non admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

/**
 * Récupère les informations de l'utilisateur connecté
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'prenom' => $_SESSION['prenom'],
        'nom' => $_SESSION['nom'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
        'fullname' => $_SESSION['prenom'] . ' ' . $_SESSION['nom']
    ];
}

/**
 * Récupère les informations complètes d'un utilisateur
 */
function getUserById($userId, $conn) {
    $stmt = mysqli_prepare($conn, 
        "SELECT id, username, prenom, nom, email, telephone, role, created_at, updated_at 
         FROM users WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $user;
}

/**
 * Met à jour le profil utilisateur
 */
function updateUserProfile($userId, $data, $conn) {
    $prenom = isset($data['prenom']) ? trim($data['prenom']) : '';
    $nom = isset($data['nom']) ? trim($data['nom']) : '';
    $telephone = isset($data['telephone']) ? trim($data['telephone']) : '';
    
    if (empty($prenom) || empty($nom)) {
        return ['success' => false, 'message' => 'Le prénom et le nom sont obligatoires.'];
    }
    
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET prenom = ?, nom = ?, telephone = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sssi", $prenom, $nom, $telephone, $userId);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        
        mysqli_stmt_close($stmt);
        return ['success' => true, 'message' => 'Profil mis à jour avec succès.'];
    } else {
        $error = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        return ['success' => false, 'message' => "Erreur lors de la mise à jour : $error"];
    }
}

/**
 * Récupère tous les utilisateurs
 */
function getAllUsers($conn, $search = '') {
    $sql = "SELECT id, username, prenom, nom, email, telephone, role, created_at, updated_at FROM users WHERE 1=1";
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (prenom LIKE '%$search%' OR nom LIKE '%$search%' OR email LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    $users = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    return $users;
}

/**
 * Change le rôle d'un utilisateur
 */
function changeUserRole($userId, $role, $conn) {
    if (!in_array($role, ['client', 'admin'])) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $role, $userId);
    
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Supprime un utilisateur
 */
function deleteUser($userId, $conn) {
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Formate l'affichage d'un rôle
 */
function formatRole($role) {
    $roles = [
        'client' => 'Client',
        'admin' => 'Administrateur'
    ];
    
    return isset($roles[$role]) ? $roles[$role] : $role;
}

/**
 * Formate une date
 */
function formatDate($date) {
    if (empty($date) || $date == '0000-00-00 00:00:00') {
        return 'Jamais';
    }
    
    return date('d/m/Y à H:i', strtotime($date));
}
