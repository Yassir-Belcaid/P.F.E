<?php
include 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prenom = $_POST["prenom"];
    $nom = $_POST["nom"];
    
    $stmt = $conn->prepare("UPDATE Utilisateur SET prenom = ?, nom = ? WHERE id_utilisateur = ?");
    $stmt->bind_param("ssi", $prenom, $nom, $_SESSION["user_id"]);
    $stmt->execute();
    
    $_SESSION["prenom"] = $prenom;
    $_SESSION["nom"] = $nom;
    
    header("Location: profile.php?success=1");
    exit();
}

$stmt = $conn->prepare("SELECT prenom, nom, photo FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon Profil</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .profile-title {
            color: #2c3e50;
            font-size: 28px;
            font-weight: bold;
        }
        .logout-btn {
            color: white;
            text-decoration: none;
            background: #e74c3c;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        .profile-section {
            display: flex;
            gap: 30px;
            align-items: center;
            margin-bottom: 30px;
        }
        .profile-pic-container {
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 30%;
            object-fit: cover;
            border: 3px solid #ecf0f1;
        }
        .name-display h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .save-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .success-msg {
            color: #27ae60;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h1 class="profile-title">Mon Profil</h1>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
        
        <?php if (isset($_GET['success'])) { ?>
            <div class="success-msg">Profil mis à jour avec succès!</div>
        <?php } ?>

        <div class="profile-section">
            <div class="profile-pic-container">
                <img src="<?php echo isset($user['photo']) ? $user['photo'] : 'https://via.placeholder.com/150?text=PROFIL'; ?>" 
                     class="profile-pic">
            </div>
            <div class="name-display" style="margin-left: 60px;">
                <h2><?php echo htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']); ?></h2>
            </div>
        </div>

        <form action="profile.php" method="POST">
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
            </div>

            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
            </div>

            <button type="submit" class="save-btn">Enregistrer les modifications</button>
        </form>
    </div>
</body>
</html>