<?php
include 'config.php';


if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Organisateur") {
    header("Location: login.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $lieu = $_POST["lieu"];
    $date = $_POST["date"];
    $heure = $_POST["heure"];
    $nb_equipes = $_POST["nb_equipes"];
    $nb_joueurs = $_POST["nb_joueurs"];
    $genre = $_POST["genre"];
    $frais = $_POST["frais"];
    
    $date_heure = $date . " " . $heure . ":00";
    
    $sql = "INSERT INTO Tournoi (nom, lieu, date_heure, nombre_equipes, nombre_joueurs_par_equipe, genre_participants, participation_fee, id_organisateur) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiisii", $nom, $lieu, $date_heure, $nb_equipes, $nb_joueurs, $genre, $frais, $_SESSION["user_id"]);
    
    if ($stmt->execute()) {
        
        $new_tournoi_id = $conn->insert_id;
        
        
        $sql_equipe = "INSERT INTO Equipe (nom, id_tournoi) VALUES (?, ?)";
        $stmt_equipe = $conn->prepare($sql_equipe);
        
        
        for ($i = 0; $i < $nb_equipes; $i++) {
            $nom_equipe = "Team " . chr(65 + $i); 
            $stmt_equipe->bind_param("si", $nom_equipe, $new_tournoi_id);
            $stmt_equipe->execute();
        }
        
        header("Location: mes_tournois.php?success=1");
        exit();
    } else {
        $error = "Erreur: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Créer un tournoi</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background: #e74c3c; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .back-link { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Créer un nouveau tournoi</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="creer_tournoi.php" method="POST">
            <div class="form-group">
                <label for="nom">Nom du tournoi</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            
            <div class="form-group">
                <label for="lieu">Lieu</label>
                <select id="lieu" name="lieu" required>
                    <option value="">-- Choisir un terrain --</option>
                    <option value="Terrain de Hay Al Wahda">Terrain de Hay Al Wahda</option>
                    <option value="Terrain de Hay Al Qods">Terrain de Hay Al Qods</option>
                    <option value="Terrain de Hay Al Manar">Terrain de Hay Al Manar</option>
                    <option value="Terrain de Hay Al Massira">Terrain de Hay Al Massira</option>
                    <option value="Terrain de Hay Al Farah">Terrain de Hay Al Farah</option>
                    <option value="Terrain de Hay Al Boughaz">Terrain de Hay Al Boughaz</option>
                    <option value="Terrain de Hay Al Firdaous">Terrain de Hay Al Firdaous</option>
                    <option value="Terrain de Hay Al Oulfa">Terrain de Hay Al Oulfa</option>
                    <option value="Terrain de Hay Al Mansour">Terrain de Hay Al Mansour</option>
                    <option value="Terrain de Hay Al Nahda">Terrain de Hay Al Nahda</option>
                    <option value="Terrain de Hay Al Wafa">Terrain de Hay Al Wafa</option>
                    <option value="Terrain de Boukhalef">Terrain de Boukhalef</option>
                    <option value="Terrain de Dradeb">Terrain de Dradeb</option>
                    <option value="Terrain de Rmilat">Terrain de Rmilat</option>
                    <option value="Terrain de Sidi Bouknadel">Terrain de Sidi Bouknadel</option>
                    <option value="Terrain de Sidi Moumen">Terrain de Sidi Moumen</option>
                    <option value="Terrain de Hay Al Karam">Terrain de Hay Al Karam</option>
                    <option value="Terrain de Hay Al Andalous">Terrain de Hay Al Andalous</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            
            <div class="form-group">
                <label for="heure">Heure</label>
                <select id="heure" name="heure" required>
                    <option value="13:00">13:00</option>
                    <option value="14:00">14:00</option>
                    <option value="15:00">15:00</option>
                    <option value="16:00">16:00</option>
                    <option value="17:00">17:00</option>
                    <option value="18:00">18:00</option>
                    <option value="19:00">19:00</option>
                    <option value="20:00">20:00</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nb_equipes">Nombre d'équipes</label>
                <select id="nb_equipes" name="nb_equipes" required>
                    <option value="4">4</option>
                    <option value="8">8</option>
                    
                </select>
            </div>
            
            <div class="form-group">
                <label for="nb_joueurs">Nombre de joueurs par équipe</label>
                <select id="nb_joueurs" name="nb_joueurs" required>
                    <option value="6">6</option>
                    <option value="7">7</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Genre des participants</label>
                <div style="display: flex; gap: 15px;">
                    <label><input type="radio" name="genre" value="Masculin" checked> Masculin</label>
                    <label><input type="radio" name="genre" value="Féminin"> Féminin</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="frais">Frais de participation (DH)</label>
                <input type="number" id="frais" name="frais" min="0" step="1" required placeholder="Montant en DH">
            </div>
            
            <button type="submit" class="btn">Créer le tournoi</button>
            <a href="dashboard_org.php" class="back-link">← Retour au tableau de bord</a>
        </form>
    </div>

    <script>
        
        document.getElementById("date").min = new Date().toISOString().split("T")[0];
    </script>
</body>
</html>