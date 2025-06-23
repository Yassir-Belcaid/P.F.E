<?php
include 'config.php';

$error = "";
$preservedValues = [
    'type' => $_POST['type'] ?? '',
    'genre' => $_POST['genre'] ?? '',
    'prenom' => $_POST['prenom'] ?? '',
    'nom' => $_POST['nom'] ?? ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["type"])) {
        $error = "Vous devez choisir un type: Joueur ou Organisateur!";
    } else {
        $prenom = $_POST["prenom"];
        $nom = $_POST["nom"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $type = $_POST["type"];
        $genre = $_POST["genre"] ?? null;
        
        if ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas!";
        } else {
            if (empty($prenom) || empty($nom)) {
                $error = "Le prénom et le nom sont obligatoires!";
            } elseif (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères!";
            } elseif ($type === 'Participant' && empty($genre)) {
                $error = "Vous devez choisir votre genre!";
            } else {
                $photoPath = null;
                
                if (!isset($_FILES["photo"]) || $_FILES["photo"]["error"] != 0) {
                    $error = "Vous devez choisir une photo de profil!";
                } else {
                    $targetDir = "uploads/";
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    
                    $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                    if (!in_array($_FILES["photo"]["type"], $allowedTypes)) {
                        $error = "Seuls les fichiers JPG, PNG et GIF sont autorisés!";
                    } else {
                        if ($_FILES["photo"]["size"] > 5 * 1024 * 1024) {
                            $error = "La taille de l'image ne doit pas dépasser 5MB!";
                        } else {
                            $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
                            $photoPath = $targetDir . $photoName;
                            
                            if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath)) {
                                $error = "Erreur lors du téléchargement de l'image!";
                            }
                        }
                    }
                }
                
                if (empty($error)) {
                    try {
                        $checkSql = "SELECT id_utilisateur FROM Utilisateur WHERE prenom = ? AND nom = ?";
                        $checkStmt = $conn->prepare($checkSql);
                        $checkStmt->bind_param("ss", $prenom, $nom);
                        $checkStmt->execute();
                        $checkResult = $checkStmt->get_result();
                        
                        if ($checkResult->num_rows > 0) {
                            $error = "Un utilisateur avec ce prénom et nom existe déjà!";
                        } else {
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            
                            $sql = "INSERT INTO Utilisateur (prenom, nom, mot_de_passe, type, photo, genre) VALUES (?, ?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ssssss", $prenom, $nom, $hashedPassword, $type, $photoPath, $genre);
                            
                            if ($stmt->execute()) {
                                header("Location: login.php?success=1");
                                exit();
                            } else {
                                $error = "Erreur lors de l'inscription: " . $conn->error;
                            }
                        }
                    } catch (Exception $e) {
                        $error = "Erreur de base de données: " . $e->getMessage();
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .container { max-width: 400px; margin: 50px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .type-selection { margin-bottom: 10px; }
        .type-buttons { display: flex; gap: 10px; margin-bottom: 5px; }
        .type-btn { 
            flex: 1; 
            padding: 12px; 
            border: 2px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
            text-align: center;
            font-weight: bold;
            transition: all 0.3s;
        }
        .type-btn.selected {
            border-color: #2ecc71;
            background: #eafaf1;
        }
        .error-message { 
            color: red; 
            text-align: center; 
            margin: 5px 0 15px;
            font-size: 14px;
            min-height: 20px;
        }
        .btn-submit { 
            background: #e74c3c; 
            color: white; 
            width: 100%; 
            padding: 12px; 
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .login-link { text-align: center; margin-top: 20px; }
        .photo-upload { text-align: center; margin: 15px 0; }
        .photo-preview { 
            width: 120px; 
            height: 120px; 
            border-radius: 50%; 
            object-fit: cover;
            border: 3px solid #eee;
            margin: 0 auto 10px;
            display: none;
        }
        .upload-label {
            display: inline-block;
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-label:hover {
            background: #2980b9;
        }
        #photoInput { display: none; }
        #genderSection { display: none; }
        .loading { opacity: 0.7; pointer-events: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Créer un compte</h2>
        
        <form action="register.php" method="POST" enctype="multipart/form-data" id="registrationForm">
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Vous êtes:</label>
                <div class="type-buttons">
                    <button type="button" class="type-btn <?php echo ($preservedValues['type'] === 'Participant') ? 'selected' : '' ?>" data-type="Participant">Joueur</button>
                    <button type="button" class="type-btn <?php echo ($preservedValues['type'] === 'Organisateur') ? 'selected' : '' ?>" data-type="Organisateur">Organisateur</button>
                </div>
                <input type="hidden" name="type" id="userType" value="<?php echo htmlspecialchars($preservedValues['type']); ?>" required>
                <div class="error-message" id="typeError">
                    <?php if ($error && !isset($_POST["type"])) echo $error; ?>
                </div>
            </div>
            
            <div class="form-group" id="genderSection" style="<?php echo ($preservedValues['type'] === 'Participant') ? 'display: block;' : 'display: none;' ?>">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Votre genre:</label>
                <div class="type-buttons">
                    <button type="button" class="type-btn <?php echo ($preservedValues['genre'] === 'Masculin') ? 'selected' : '' ?>" data-gender="Masculin">Masculin</button>
                    <button type="button" class="type-btn <?php echo ($preservedValues['genre'] === 'Féminin') ? 'selected' : '' ?>" data-gender="Féminin">Féminin</button>
                </div>
                <input type="hidden" name="genre" id="userGender" value="<?php echo htmlspecialchars($preservedValues['genre']); ?>" <?php echo ($preservedValues['type'] === 'Participant') ? 'required' : '' ?>>
                <div class="error-message" id="genderError">
                    <?php if ($error && strpos($error, 'genre') !== false) echo $error; ?>
                </div>
            </div>
            
            <div class="photo-upload">
                <img id="photoPreview" class="photo-preview" alt="Aperçu photo">
                <label for="photoInput" class="upload-label">Choisir une photo *</label>
                <input type="file" name="photo" id="photoInput" accept="image/*" required>
                <div class="error-message" id="photoError"></div>
            </div>
            
            <div class="form-group">
                <input type="text" name="prenom" placeholder="Prénom" required value="<?php echo htmlspecialchars($preservedValues['prenom']); ?>">
            </div>
            
            <div class="form-group">
                <input type="text" name="nom" placeholder="Nom" required value="<?php echo htmlspecialchars($preservedValues['nom']); ?>">
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirmer mot de passe" required>
                <div class="error-message" id="passwordError">
                    <?php if ($error && $error !== "Vous devez choisir un type: Joueur ou Organisateur!") echo $error; ?>
                </div>
            </div>
            
            <button type="submit" class="btn-submit" id="submitBtn">S'inscrire</button>
        </form>
        
        <div class="login-link">
            Déjà un compte? <a href="login.php">Se connecter</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeButtons = document.querySelectorAll('.type-btn[data-type]');
            const genderButtons = document.querySelectorAll('.type-btn[data-gender]');
            const userTypeInput = document.getElementById('userType');
            const userGenderInput = document.getElementById('userGender');
            const typeError = document.getElementById('typeError');
            const genderError = document.getElementById('genderError');
            const passwordError = document.getElementById('passwordError');
            const photoError = document.getElementById('photoError');
            const photoInput = document.getElementById('photoInput');
            const photoPreview = document.getElementById('photoPreview');
            const genderSection = document.getElementById('genderSection');
            const form = document.getElementById('registrationForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (userTypeInput.value) {
                document.querySelector(`.type-btn[data-type="${userTypeInput.value}"]`).classList.add('selected');
                if (userTypeInput.value === 'Participant') {
                    genderSection.style.display = 'block';
                }
            }
            
            if (userGenderInput.value) {
                document.querySelector(`.type-btn[data-gender="${userGenderInput.value}"]`).classList.add('selected');
            }
            
            typeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    typeButtons.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    userTypeInput.value = this.getAttribute('data-type');
                    typeError.textContent = '';
                    
                    if (userTypeInput.value === 'Participant') {
                        genderSection.style.display = 'block';
                        userGenderInput.required = true;
                    } else {
                        genderSection.style.display = 'none';
                        userGenderInput.value = '';
                        userGenderInput.required = false;
                        genderButtons.forEach(btn => btn.classList.remove('selected'));
                    }
                });
            });
            
            genderButtons.forEach(button => {
                button.addEventListener('click', function() {
                    genderButtons.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    userGenderInput.value = this.getAttribute('data-gender');
                    genderError.textContent = '';
                });
            });
            
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        photoPreview.src = event.target.result;
                        photoPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                    photoError.textContent = '';
                }
            });
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                if (photoInput.files.length === 0) {
                    photoError.textContent = 'Veuillez choisir une photo de profil.';
                    isValid = false;
                } else {
                    const file = photoInput.files[0];
                    const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                    if (!allowedTypes.includes(file.type)) {
                        photoError.textContent = 'Seuls les fichiers JPG, PNG et GIF sont autorisés';
                        isValid = false;
                    } else {
                        photoError.textContent = '';
                    }
                }
                
                if (!userTypeInput.value) {
                    typeError.textContent = 'Vous devez choisir un type (Joueur ou Organisateur) !';
                    isValid = false;
                }
                
                if (userTypeInput.value === 'Participant' && !userGenderInput.value) {
                    genderError.textContent = 'Vous devez choisir votre genre !';
                    isValid = false;
                }
                
                const password = document.querySelector('input[name="password"]').value;
                const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                
                if (password !== confirmPassword) {
                    passwordError.textContent = 'Les mots de passe ne correspondent pas!';
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                } else {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Enregistrement...';
                    submitBtn.classList.add('loading');
                }
            });
        });
    </script>
</body>
</html>