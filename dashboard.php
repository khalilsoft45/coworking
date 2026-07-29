<?php
session_start();
$secret_password = "0000"; 

// --- 1. AUTHENTICATION CHECK ---
if (!isset($_SESSION['loggedin'])) {
    if (isset($_POST['password']) && $_POST['password'] === $secret_password) {
        $_SESSION['loggedin'] = true;
    } else {
        header("Location: index.php");
        exit;
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// --- 2. DATABASE CONNECTION ---
// Parse the Render URL into a PDO-friendly format
$dbUrl = getenv("DATABASE_URL") ?: "postgresql://coworking_space_user:COkaIq65PtNcfNvOa1FeZqepgqYNONQY@dpg-d9l6djvavr4c73a4g6f0-a/coworking_space";
$dbopts = parse_url($dbUrl);

$host = $dbopts["host"];
$port = $dbopts["port"] ?? 5432;
$user = $dbopts["user"];
$password = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], '/');

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$regions = ["Ariana", "Béja", "Ben Arous", "Bizerte", "Gabès", "Gafsa", "Jendouba", "Kairouan", "Kasserine", "Kebili", "Kef", "Mahdia", "Manouba", "Medenine", "Monastir", "Nabeul", "Sfax", "Sidi Bouzid", "Siliana", "Sousse", "Tataouine", "Tozeur", "Tunis", "Zaghouan"];

// --- 3. ACTIONS: ADD & DELETE ---
// Handle Adding New Space
if (isset($_POST['add_space'])) {
    $stmt = $conn->prepare("INSERT INTO coworking_spaces (name, region, address, email, tel, website, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'], $_POST['region'], $_POST['address'], 
        $_POST['email'], $_POST['tel'], $_POST['website'], $_POST['notes']
    ]);
    header("Location: dashboard.php?msg=added");
    exit;
}

// Handle Deleting a Space
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM coworking_spaces WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_id']]);
    header("Location: dashboard.php?msg=deleted");
    exit;
}

// Fetch all spaces
$result = $conn->query("SELECT * FROM coworking_spaces ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NASACloud | Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* Additional page-specific styles building on style.css */
        body { 
            display: flex; 
            height: 100vh; 
            overflow: hidden; 
            background: #f1f5f9; /* Slate 50 */
        }
        
        /* Sidebar */
        .sidebar { 
            width: 280px; 
            background: #0f172a; 
            color: white; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 4px 0 15px rgba(0,0,0,0.05); 
            z-index: 10;
        }
        .sidebar-header { 
            padding: 35px 25px 25px; 
            font-size: 1.4rem; 
            font-weight: 700; 
            font-family: 'Outfit', sans-serif;
            letter-spacing: 1px; 
            color: white; 
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header i { color: var(--primary); font-size: 1.8rem; }
        .region-list { list-style: none; padding: 0 15px; margin: 0; overflow-y: auto; flex: 1; }
        .region-list::-webkit-scrollbar { width: 5px; }
        .region-list::-webkit-scrollbar-thumb { background: #334155; border-radius: 5px; }
        .region-item { 
            padding: 12px 18px; 
            cursor: pointer; 
            border-radius: 8px;
            transition: all 0.2s ease; 
            font-size: 14px; 
            color: #94a3b8; 
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .region-item:hover { background: #1e293b; color: white; }
        .region-item.active { background: var(--primary); color: white; font-weight: 500; }
        
        /* Main Area */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-nav { 
            background: rgba(255, 255, 255, 0.8); 
            backdrop-filter: blur(10px);
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid var(--border); 
            z-index: 5;
        }
        .top-nav h2 { margin: 0; font-weight: 600; font-size: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .logout-btn {
            color: var(--danger);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            background: var(--danger-light);
        }
        .logout-btn:hover { background: var(--danger); color: white; }
        .scroll-area { padding: 40px; overflow-y: auto; flex: 1; }

        /* UI Elements */
        .form-box { 
            background: white; 
            padding: 30px; 
            border-radius: 16px; 
            margin-bottom: 40px; 
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .form-box summary { 
            cursor: pointer; 
            font-weight: 600; 
            color: var(--primary); 
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }
        .form-box summary::-webkit-details-marker { display: none; }
        
        .form-grid {
            margin-top: 25px; 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px;
        }
        .submit-btn { 
            background: var(--success); 
            color: white; 
            border: none; 
            padding: 14px; 
            border-radius: 8px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.2s;
            grid-column: span 2;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }
        .submit-btn:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3); }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 25px; }
        .card { 
            background: white; 
            border-radius: 16px; 
            padding: 25px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); 
            border: 1px solid var(--border); 
            transition: all 0.3s ease; 
            display: flex;
            flex-direction: column;
        }
        .card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #cbd5e1;
        }
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .card h3 { margin: 0; font-size: 1.25rem; font-weight: 600; line-height: 1.3; }
        .region-tag { 
            font-size: 11px; 
            text-transform: uppercase; 
            background: #e0e7ff; 
            color: var(--primary); 
            padding: 5px 12px; 
            border-radius: 20px; 
            font-weight: 700;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .info-row { display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 8px; }
        .info-row i { font-size: 16px; color: var(--text-main); margin-top: 2px; }
        
        .notes-box {
            font-size: 12px; 
            background: #f8fafc; 
            padding: 12px; 
            border-radius: 8px; 
            color: #475569;
            margin-top: 15px;
            border: 1px dashed #cbd5e1;
            flex-grow: 1;
        }

        /* Buttons */
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .btn { 
            flex: 1; 
            padding: 10px; 
            font-size: 13px; 
            border-radius: 8px; 
            cursor: pointer; 
            text-decoration: none; 
            text-align: center; 
            font-weight: 600; 
            border: 1px solid transparent; 
            transition: all 0.2s; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-edit { color: #d97706; background: #fef3c7; border-color: #fde68a; }
        .btn-edit:hover { background: #fde68a; }
        .btn-del { color: #dc2626; background: #fee2e2; border-color: #fecaca; }
        .btn-del:hover { background: #fecaca; }
        .btn-visit { background: #eff6ff; color: var(--primary); border-color: #bfdbfe; }
        .btn-visit:hover { background: #dbeafe; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header"><i class="ph-fill ph-cloud-moon"></i> NASACLOUD</div>
        <ul class="region-list">
            <li class="region-item active" onclick="filterRegion('all', this)"><i class="ph ph-map-pin"></i> All Regions</li>
            <?php foreach($regions as $r): ?>
                <li class="region-item" onclick="filterRegion('<?php echo $r; ?>', this)"><i class="ph ph-map-pin"></i> <?php echo htmlspecialchars($r); ?></li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-nav">
            <h2 id="view-title"><i class="ph ph-buildings"></i> All Coworking Spaces</h2>
            <a href="?logout=1" class="logout-btn"><i class="ph ph-sign-out"></i> Logout</a>
        </header>

        <div class="scroll-area">
            <?php if(isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
                <div style="background: var(--success); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                    <i class="ph ph-check-circle"></i> Space successfully registered!
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div style="background: var(--danger); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                    <i class="ph ph-trash"></i> Space removed from database.
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                <div style="background: var(--primary); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                    <i class="ph ph-pencil-simple"></i> Space details updated!
                </div>
            <?php endif; ?>

            <details class="form-box">
                <summary><i class="ph-fill ph-plus-circle"></i> Register a New Coworking Space</summary>
                <form method="POST" class="form-grid">
                    <input type="text" name="name" class="modern-input" placeholder="Business Name" required>
                    <select name="region" class="modern-input" required>
                        <option value="" disabled selected>Select Region...</option>
                        <?php foreach($regions as $r): ?> <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option> <?php endforeach; ?>
                    </select>
                    <input type="email" name="email" class="modern-input" placeholder="Contact Email">
                    <input type="text" name="tel" class="modern-input" placeholder="Phone Number">
                    <input type="url" name="website" class="modern-input" placeholder="Website URL (https://...)" style="grid-column: span 2;">
                    <textarea name="address" class="modern-input" placeholder="Physical Address" style="grid-column: span 2; height: 80px;"></textarea>
                    <textarea name="notes" class="modern-input" placeholder="Notes (e.g. 24/7 access, fiber internet...)" style="grid-column: span 2; height: 80px;"></textarea>
                    <button type="submit" name="add_space" class="submit-btn"><i class="ph-bold ph-floppy-disk"></i> Save Space to Database</button>
                </form>
            </details>

            <div class="grid" id="container">
                <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="card" data-region="<?php echo htmlspecialchars($row['region']); ?>">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <span class="region-tag"><?php echo htmlspecialchars($row['region']); ?></span>
                        </div>
                        
                        <div class="info-row"><i class="ph ph-map-pin-line"></i> <?php echo htmlspecialchars($row['address']); ?></div>
                        <div class="info-row"><i class="ph ph-phone"></i> <?php echo htmlspecialchars($row['tel']); ?></div>
                        <div class="info-row"><i class="ph ph-envelope-simple"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                        
                        <?php if(!empty($row['notes'])): ?>
                            <div class="notes-box">
                                <strong><i class="ph ph-info"></i> Notes:</strong><br>
                                <?php echo nl2br(htmlspecialchars($row['notes'])); ?>
                            </div>
                        <?php else: ?>
                            <div style="flex-grow: 1;"></div>
                        <?php endif; ?>

                        <div class="btn-group">
                            <?php if($row['website']): ?>
                                <a href="<?php echo htmlspecialchars($row['website']); ?>" target="_blank" class="btn btn-visit"><i class="ph ph-globe"></i> Web</a>
                            <?php endif; ?>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-edit"><i class="ph ph-pencil-simple"></i> Edit</a>
                            <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-del" onclick="return confirm('Are you sure you want to delete this space?')"><i class="ph ph-trash"></i> Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <script>
    function filterRegion(region, el) {
        // Active Tab UI
        document.querySelectorAll('.region-item').forEach(item => item.classList.remove('active'));
        el.classList.add('active');

        // Update Title
        const titleEl = document.getElementById('view-title');
        titleEl.innerHTML = (region === 'all') 
            ? '<i class="ph ph-buildings"></i> All Coworking Spaces' 
            : '<i class="ph ph-map-pin"></i> Spaces in ' + region;

        // Filtering Logic
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            if (region === 'all' || card.getAttribute('data-region') === region) {
                card.style.display = 'flex';
                // Slight animation reset trick
                card.style.animation = 'none';
                card.offsetHeight; /* trigger reflow */
                card.style.animation = null; 
            } else {
                card.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>