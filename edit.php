<?php
session_start();
// Security check: must be logged in
if (!isset($_SESSION['loggedin'])) { header("Location: index.php"); exit; }

$conn = new mysqli("localhost", "root", "", "nasacloud_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$regions = ["Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan", "Kasserine", "Kebili", "Kef", "Mahdia", "Manouba", "Medenine", "Monastir", "Nabeul", "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"];

// --- 1. GET CURRENT DATA ---
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Using a prepared statement for security
    $stmt = $conn->prepare("SELECT * FROM coworking_spaces WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    if (!$data) { die("Space not found."); }
}

// --- 2. UPDATE DATA ON SUBMIT ---
if (isset($_POST['update_space'])) {
    $id_to_update = $_POST['id'];
    $stmt = $conn->prepare("UPDATE coworking_spaces SET name=?, region=?, address=?, email=?, tel=?, website=?, notes=? WHERE id=?");
    $stmt->bind_param("sssssssi", $_POST['name'], $_POST['region'], $_POST['address'], $_POST['email'], $_POST['tel'], $_POST['website'], $_POST['notes'], $id_to_update);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: dashboard.php?msg=updated");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Space | NASACloud</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { 
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }
        .edit-container { 
            background: white; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); 
            width: 100%; 
            max-width: 600px; 
            margin: 20px;
        }
        .edit-header { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            margin-bottom: 30px; 
            border-bottom: 2px solid var(--border); 
            padding-bottom: 20px; 
        }
        .edit-header i { font-size: 2rem; color: var(--primary); }
        .edit-header h2 { margin: 0; font-size: 1.8rem; font-weight: 700; color: var(--dark); }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 0.9rem; font-weight: 600; color: var(--text-main); display: flex; align-items: center; gap: 6px; }
        
        .full-width { grid-column: span 2; }
        
        .btn-save { 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 16px; 
            border-radius: 10px; 
            font-weight: 600; 
            font-size: 1.1rem;
            cursor: pointer; 
            transition: all 0.3s; 
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-save:hover { 
            background: var(--primary-hover); 
            transform: translateY(-2px); 
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3); 
        }
        
        .cancel-link { 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            gap: 6px;
            margin-top: 20px; 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.95rem; 
            font-weight: 500;
            transition: color 0.2s;
        }
        .cancel-link:hover { color: var(--dark); }
    </style>
</head>
<body>

<div class="edit-container">
    <div class="edit-header">
        <i class="ph-fill ph-pencil-line"></i>
        <h2>Edit Coworking Space</h2>
    </div>
    
    <form method="POST" class="form-grid">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($data['id']); ?>">

        <div class="input-group full-width">
            <label><i class="ph ph-buildings"></i> Business Name</label>
            <input type="text" name="name" class="modern-input" value="<?php echo htmlspecialchars($data['name']); ?>" required>
        </div>

        <div class="input-group">
            <label><i class="ph ph-map-pin"></i> Region</label>
            <select name="region" class="modern-input">
                <?php foreach($regions as $r): ?>
                    <option value="<?php echo htmlspecialchars($r); ?>" <?php if($data['region'] == $r) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($r); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="input-group">
            <label><i class="ph ph-phone"></i> Phone Number</label>
            <input type="text" name="tel" class="modern-input" value="<?php echo htmlspecialchars($data['tel']); ?>">
        </div>

        <div class="input-group">
            <label><i class="ph ph-envelope-simple"></i> Contact Email</label>
            <input type="email" name="email" class="modern-input" value="<?php echo htmlspecialchars($data['email']); ?>">
        </div>

        <div class="input-group">
            <label><i class="ph ph-globe"></i> Website URL</label>
            <input type="url" name="website" class="modern-input" value="<?php echo htmlspecialchars($data['website']); ?>">
        </div>

        <div class="input-group full-width">
            <label><i class="ph ph-map-pin-line"></i> Physical Address</label>
            <textarea name="address" class="modern-input" style="height: 80px;"><?php echo htmlspecialchars($data['address']); ?></textarea>
        </div>

        <div class="input-group full-width">
            <label><i class="ph ph-info"></i> Notes</label>
            <textarea name="notes" class="modern-input" style="height: 80px;"><?php echo htmlspecialchars($data['notes']); ?></textarea>
        </div>

        <div class="full-width">
            <button type="submit" name="update_space" class="btn-save"><i class="ph-bold ph-check"></i> Update Changes</button>
            <a href="dashboard.php" class="cancel-link"><i class="ph ph-arrow-left"></i> Cancel and return to dashboard</a>
        </div>
    </form>
</div>

</body>
</html>