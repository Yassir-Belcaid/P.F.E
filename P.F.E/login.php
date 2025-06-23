<?php
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prenom = trim($conn->real_escape_string($_POST["prenom"]));
    $nom = trim($conn->real_escape_string($_POST["nom"]));
    $password = $_POST["password"];

    $sql = "SELECT id_utilisateur, prenom, nom, mot_de_passe, type, photo 
            FROM Utilisateur 
            WHERE prenom = ? AND nom = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erreur de préparation: " . $conn->error);
    }

    $stmt->bind_param("ss", $prenom, $nom);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user["mot_de_passe"])) {
            session_start();
            
            $_SESSION = [
                "user_id" => $user["id_utilisateur"],
                "prenom" => $user["prenom"],
                "nom" => $user["nom"],
                "user_type" => $user["type"],
                "photo" => $user["photo"],
                "logged_in" => true
            ];

            if ($user["type"] === "Organisateur") {
                header("Location: dashboard_org.php");
            } else {
                header("Location: dashboard_joueur.php");
            }
            exit();
        } else {
            $error = "Mot de passe incorrect!";
        }
    } else {
        $error = "Utilisateur non trouvé! Vérifiez votre prénom et nom.";
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transition: all 0.3s ease;
        }
        .login-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #34495e;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .btn {
            width: 100%;
            padding: 0.8rem;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #c0392b;
        }
        .error {
            color: #e74c3c;
            background-color: #fdecea;
            padding: 0.8rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Connexion</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required
                       value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required
                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
        </form>
        
        <div class="register-link">
            Pas encore de compte? <a href="register.php">S'inscrire ici</a>
        </div>
    </div>
</body>
</html>