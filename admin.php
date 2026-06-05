<?php
session_start();
require_once 'config/config.php';

// Admin password is defined in config/config.php (see config/README.md)
$ADMIN_PASSWORD = ADMIN_PASSWORD;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle login
if (isset($_POST['login'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time'] = time();
        header('Location: admin.php');
        exit;
    } else {
        $loginError = 'Invalid password';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Check session timeout (30 minutes)
if ($isLoggedIn && isset($_SESSION['login_time'])) {
    if (time() - $_SESSION['login_time'] > 1800) {
        session_destroy();
        $sessionExpired = true;
        $isLoggedIn = false;
    }
}

// Handle JSON updates
if ($isLoggedIn && isset($_POST['update_json'])) {
    $jsonData = $_POST['json_data'];

    $decoded = json_decode($jsonData, true);
    if ($decoded === null) {
        $updateError = 'Invalid JSON format';
    } else {
        $backupFile = 'routes_backup_' . date('Y-m-d_H-i-s') . '.json';
        copy('routes.json', $backupFile);

        if (file_put_contents('routes.json', $jsonData)) {
            $updateSuccess = 'Routes updated successfully!';
        } else {
            $updateError = 'Failed to save routes.json';
        }
    }
}

// Load current routes data
$routesData = [];
if (file_exists('routes.json')) {
    $routesData = json_decode(file_get_contents('routes.json'), true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Direct Car Service</title>
    <link rel="icon" type="image/png" href="./images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .header h1 {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header i { color: #4CAF50; }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }

        /* Login Form */
        .login-container {
            max-width: 350px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .login-container h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-size: 1.3rem;
        }

        .login-container i { color: #4CAF50; font-size: 1.5rem; }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
        }

        .login-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        /* Navigation Tabs */
        .nav-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .nav-tab {
            background: white;
            color: #2c3e50;
            padding: 10px 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .nav-tab.active {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Route Cards */
        .routes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        .route-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            border-left: 4px solid #4CAF50;
        }

        .route-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .route-locations h4 {
            color: #2c3e50;
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .route-locations p {
            color: #666;
            font-size: 0.85rem;
        }

        .route-category {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .route-category.airports { background: #e3f2fd; color: #1565c0; }
        .route-category.long-distance { background: #fff3e0; color: #ef6c00; }

        .route-prices {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }

        .price-item {
            background: #f8f9fa;
            padding: 8px 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
        }

        .price-item .vehicle-name {
            color: #666;
            font-size: 0.75rem;
        }

        .price-item .price {
            color: #4CAF50;
            font-weight: 600;
        }

        .route-actions {
            display: flex;
            gap: 10px;
        }

        .edit-btn, .delete-btn, .save-btn, .cancel-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .edit-btn {
            background: #4CAF50;
            color: white;
        }

        .delete-btn {
            background: #ffebee;
            color: #c62828;
        }

        .save-btn {
            background: #4CAF50;
            color: white;
        }

        .cancel-btn {
            background: #f5f5f5;
            color: #666;
        }

        /* Edit Mode */
        .route-card.editing {
            border-left-color: #2196F3;
        }

        .route-card.editing .view-mode { display: none; }
        .route-card .edit-mode { display: none; }
        .route-card.editing .edit-mode { display: block; }

        .edit-field {
            margin-bottom: 12px;
        }

        .edit-field label {
            display: block;
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 4px;
        }

        .edit-field input, .edit-field select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .edit-prices-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .edit-price-item label {
            display: block;
            font-size: 0.7rem;
            color: #666;
            margin-bottom: 3px;
        }

        .edit-price-item input {
            width: 100%;
            padding: 8px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        /* Pricing Rules Cards */
        .rules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }

        .rule-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }

        .rule-card h4 {
            color: #2c3e50;
            font-size: 1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rule-card h4 i { color: #4CAF50; }

        .rule-item {
            margin-bottom: 15px;
        }

        .rule-item label {
            display: block;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
        }

        .rule-item input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .rule-item small {
            display: block;
            color: #999;
            font-size: 0.7rem;
            margin-top: 3px;
        }

        /* Add Route Button */
        .add-route-card {
            background: white;
            border-radius: 12px;
            padding: 40px 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            border: 2px dashed #4CAF50;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-route-card:hover {
            background: #f1f8e9;
        }

        .add-route-card i {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .add-route-card p {
            color: #666;
            font-weight: 500;
        }

        /* Featured Routes */
        .section-header-admin {
            margin-bottom: 25px;
        }

        .section-header-admin h3 {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header-admin h3 i {
            color: #4CAF50;
        }

        .section-header-admin p {
            color: #666;
            font-size: 0.9rem;
        }

        .featured-routes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .featured-route-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            border-left: 4px solid #4CAF50;
            position: relative;
        }

        .featured-route-number {
            position: absolute;
            top: -10px;
            left: -10px;
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .featured-route-card textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            font-family: inherit;
            resize: vertical;
        }

        .featured-preview {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            color: #666;
        }

        .featured-preview strong {
            color: #4CAF50;
        }

        /* Global Save Button */
        .global-save {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 5px 20px rgba(76, 175, 80, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
        }

        .global-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.5);
        }

        /* Search/Filter */
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-bar input {
            flex: 1;
            min-width: 200px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .search-bar select {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.9rem;
            background: white;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-overlay.active { display: flex; }

        .modal {
            background: white;
            border-radius: 15px;
            padding: 25px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .header h1 { font-size: 1rem; }
            .routes-grid { grid-template-columns: 1fr; }
            .route-prices { grid-template-columns: 1fr; }
            .edit-prices-grid { grid-template-columns: 1fr; }
            .rules-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!$isLoggedIn): ?>
            <div class="login-container">
                <h2><i class="fas fa-lock"></i> Admin Login</h2>

                <?php if (isset($loginError)): ?>
                    <div class="error-message"><?php echo $loginError; ?></div>
                <?php endif; ?>

                <?php if (isset($sessionExpired)): ?>
                    <div class="error-message">Session expired. Please login again.</div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="header">
                <h1><i class="fas fa-tools"></i> Admin Panel</h1>
                <a href="?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <?php if (isset($updateSuccess)): ?>
                <div class="success-message"><?php echo $updateSuccess; ?></div>
            <?php endif; ?>

            <?php if (isset($updateError)): ?>
                <div class="error-message"><?php echo $updateError; ?></div>
            <?php endif; ?>

            <div class="nav-tabs">
                <button class="nav-tab active" onclick="showTab('routes')">
                    <i class="fas fa-route"></i> Routes
                </button>
                <button class="nav-tab" onclick="showTab('homepage')">
                    <i class="fas fa-home"></i> Homepage
                </button>
                <button class="nav-tab" onclick="showTab('rules')">
                    <i class="fas fa-calculator"></i> Pricing Rules
                </button>
                <button class="nav-tab" onclick="showTab('vehicles')">
                    <i class="fas fa-car"></i> Vehicles
                </button>
            </div>

            <!-- Routes Tab -->
            <div id="routes-tab" class="tab-content active">
                <div class="search-bar">
                    <input type="text" id="searchRoutes" placeholder="Search routes..." oninput="filterRoutes()">
                    <select id="filterCategory" onchange="filterRoutes()">
                        <option value="">All Categories</option>
                        <option value="local">Local</option>
                        <option value="airports">Airports</option>
                        <option value="long-distance">Long Distance</option>
                    </select>
                </div>

                <div class="routes-grid" id="routesGrid">
                    <div class="add-route-card" onclick="addNewRoute()">
                        <i class="fas fa-plus-circle"></i>
                        <p>Add New Route</p>
                    </div>

                    <?php if (isset($routesData['routes'])): ?>
                        <?php foreach ($routesData['routes'] as $index => $route): ?>
                            <div class="route-card" data-index="<?php echo $index; ?>" data-from="<?php echo strtolower($route['from']); ?>" data-to="<?php echo strtolower($route['to']); ?>" data-category="<?php echo $route['category']; ?>">
                                <div class="view-mode">
                                    <div class="route-card-header">
                                        <div class="route-locations">
                                            <h4><?php echo htmlspecialchars($route['from']); ?></h4>
                                            <p><i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($route['to']); ?></p>
                                        </div>
                                        <span class="route-category <?php echo $route['category']; ?>"><?php echo ucfirst(str_replace('-', ' ', $route['category'])); ?></span>
                                    </div>
                                    <div class="route-prices">
                                        <div class="price-item">
                                            <span class="vehicle-name">Car</span>
                                            <span class="price">$<?php echo isset($route['pricing']['full_size_car']) ? number_format($route['pricing']['full_size_car'], 0) : '-'; ?></span>
                                        </div>
                                        <div class="price-item">
                                            <span class="vehicle-name">SUV</span>
                                            <span class="price">$<?php echo isset($route['pricing']['standard_suv']) ? number_format($route['pricing']['standard_suv'], 0) : '-'; ?></span>
                                        </div>
                                        <div class="price-item">
                                            <span class="vehicle-name">Full SUV</span>
                                            <span class="price">$<?php echo isset($route['pricing']['full_size_suv']) ? number_format($route['pricing']['full_size_suv'], 0) : '-'; ?></span>
                                        </div>
                                        <div class="price-item">
                                            <span class="vehicle-name">Minivan</span>
                                            <span class="price">$<?php echo isset($route['pricing']['minivan']) ? number_format($route['pricing']['minivan'], 0) : '-'; ?></span>
                                        </div>
                                        <div class="price-item">
                                            <span class="vehicle-name">Sprinter</span>
                                            <span class="price">$<?php echo isset($route['pricing']['sprinter']) ? number_format($route['pricing']['sprinter'], 0) : '-'; ?></span>
                                        </div>
                                    </div>
                                    <div class="route-actions">
                                        <button class="edit-btn" onclick="editRoute(<?php echo $index; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="delete-btn" onclick="deleteRoute(<?php echo $index; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="edit-mode">
                                    <div class="edit-field">
                                        <label>From</label>
                                        <input type="text" data-field="from" value="<?php echo htmlspecialchars($route['from']); ?>">
                                    </div>
                                    <div class="edit-field">
                                        <label>To</label>
                                        <input type="text" data-field="to" value="<?php echo htmlspecialchars($route['to']); ?>">
                                    </div>
                                    <div class="edit-field">
                                        <label>Category</label>
                                        <select data-field="category">
                                            <option value="local" <?php echo $route['category'] === 'local' ? 'selected' : ''; ?>>Local</option>
                                            <option value="airports" <?php echo $route['category'] === 'airports' ? 'selected' : ''; ?>>Airports</option>
                                            <option value="long-distance" <?php echo $route['category'] === 'long-distance' ? 'selected' : ''; ?>>Long Distance</option>
                                        </select>
                                    </div>
                                    <div class="edit-prices-grid">
                                        <div class="edit-price-item">
                                            <label>Full Size Car</label>
                                            <input type="number" data-field="pricing.full_size_car" value="<?php echo $route['pricing']['full_size_car'] ?? ''; ?>">
                                        </div>
                                        <div class="edit-price-item">
                                            <label>Standard SUV</label>
                                            <input type="number" data-field="pricing.standard_suv" value="<?php echo $route['pricing']['standard_suv'] ?? ''; ?>">
                                        </div>
                                        <div class="edit-price-item">
                                            <label>Full Size SUV</label>
                                            <input type="number" data-field="pricing.full_size_suv" value="<?php echo $route['pricing']['full_size_suv'] ?? ''; ?>">
                                        </div>
                                        <div class="edit-price-item">
                                            <label>Minivan</label>
                                            <input type="number" data-field="pricing.minivan" value="<?php echo $route['pricing']['minivan'] ?? ''; ?>">
                                        </div>
                                        <div class="edit-price-item">
                                            <label>Sprinter</label>
                                            <input type="number" data-field="pricing.sprinter" value="<?php echo $route['pricing']['sprinter'] ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="route-actions">
                                        <button class="save-btn" onclick="saveRoute(<?php echo $index; ?>)">
                                            <i class="fas fa-check"></i> Save
                                        </button>
                                        <button class="cancel-btn" onclick="cancelEdit(<?php echo $index; ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Homepage Tab -->
            <div id="homepage-tab" class="tab-content">
                <div class="section-header-admin">
                    <h3><i class="fas fa-star"></i> Featured Routes on Homepage</h3>
                    <p>Select which 3 routes appear on the homepage. Changes will update automatically when you save.</p>
                </div>

                <div class="featured-routes-grid">
                    <?php
                    $featuredRoutes = $routesData['featured_routes'] ?? [];
                    for ($i = 0; $i < 3; $i++):
                        $featured = $featuredRoutes[$i] ?? ['from' => '', 'to' => '', 'description' => ''];
                    ?>
                    <div class="featured-route-card">
                        <div class="featured-route-number"><?php echo $i + 1; ?></div>
                        <div class="edit-field">
                            <label>Select Route</label>
                            <select class="featured-route-select" data-index="<?php echo $i; ?>" onchange="updateFeaturedRoute(<?php echo $i; ?>)">
                                <option value="">-- Select a route --</option>
                                <?php if (isset($routesData['routes'])): ?>
                                    <?php foreach ($routesData['routes'] as $route): ?>
                                        <option value="<?php echo htmlspecialchars($route['from'] . '|' . $route['to']); ?>"
                                            <?php echo ($route['from'] == $featured['from'] && $route['to'] == $featured['to']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($route['from'] . ' → ' . $route['to']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label>Description (shown on homepage)</label>
                            <textarea class="featured-description" data-index="<?php echo $i; ?>" rows="2" placeholder="e.g., Long distance travel&#10;Comfortable highway journey"><?php echo htmlspecialchars($featured['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="featured-preview">
                            <small>Preview: <strong id="preview-<?php echo $i; ?>"><?php echo htmlspecialchars($featured['from'] . ' to ' . $featured['to']); ?></strong></small>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Pricing Rules Tab -->
            <div id="rules-tab" class="tab-content">
                <div class="rules-grid">
                    <div class="rule-card">
                        <h4><i class="fas fa-dollar-sign"></i> Base Pricing</h4>
                        <div class="rule-item">
                            <label>Rate per Mile (Custom Routes)</label>
                            <input type="number" step="0.01" id="customRatePerMile" value="<?php echo $routesData['pricing_rules']['custom_rate_per_mile'] ?? ''; ?>">
                            <small>Used for routes not in the list</small>
                        </div>
                        <div class="rule-item">
                            <label>Round Trip Multiplier</label>
                            <input type="number" step="0.01" id="roundTripMultiplier" value="<?php echo $routesData['pricing_rules']['round_trip_multiplier'] ?? ''; ?>">
                            <small>1.25 = 25% discount on return</small>
                        </div>
                    </div>

                    <div class="rule-card">
                        <h4><i class="fas fa-map-marker-alt"></i> Stop Fees</h4>
                        <div class="rule-item">
                            <label>Short Stop Fee</label>
                            <input type="number" step="0.01" id="shortStopFee" value="<?php echo $routesData['pricing_rules']['stop_fees']['short_stop'] ?? ''; ?>">
                            <small>Quick pickup (under 15 min)</small>
                        </div>
                        <div class="rule-item">
                            <label>One Hour Stop Fee</label>
                            <input type="number" step="0.01" id="oneHourStopFee" value="<?php echo $routesData['pricing_rules']['stop_fees']['one_hour_stop'] ?? ''; ?>">
                            <small>Extended waiting time</small>
                        </div>
                    </div>

                    <div class="rule-card">
                        <h4><i class="fas fa-clock"></i> Rush Hour</h4>
                        <div class="rule-item">
                            <label>Rush Hour Multiplier</label>
                            <input type="number" step="0.01" id="rushHourMultiplier" value="<?php echo $routesData['pricing_rules']['time_surcharges']['rush_hour']['multiplier'] ?? ''; ?>">
                            <small>7-9 AM & 5-7 PM weekdays</small>
                        </div>
                    </div>

                    <div class="rule-card">
                        <h4><i class="fas fa-moon"></i> Late Night</h4>
                        <div class="rule-item">
                            <label>Late Night Flat Fee</label>
                            <input type="number" step="0.01" id="lateNightFee" value="<?php echo $routesData['pricing_rules']['time_surcharges']['late_night']['flat_fee'] ?? ''; ?>">
                            <small>10 PM - 6 AM</small>
                        </div>
                    </div>

                    <div class="rule-card">
                        <h4><i class="fas fa-calendar-week"></i> Weekend</h4>
                        <div class="rule-item">
                            <label>Weekend Multiplier</label>
                            <input type="number" step="0.01" id="weekendMultiplier" value="<?php echo $routesData['pricing_rules']['time_surcharges']['weekend']['multiplier'] ?? ''; ?>">
                            <small>Saturday & Sunday</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicles Tab -->
            <div id="vehicles-tab" class="tab-content">
                <div class="rules-grid">
                    <?php if (isset($routesData['vehicle_types'])): ?>
                        <?php foreach ($routesData['vehicle_types'] as $key => $vehicle): ?>
                            <div class="rule-card" data-vehicle="<?php echo $key; ?>">
                                <h4><i class="fas fa-car"></i> <?php echo htmlspecialchars($vehicle['name']); ?></h4>
                                <div class="rule-item">
                                    <label>Vehicle Name</label>
                                    <input type="text" data-field="name" value="<?php echo htmlspecialchars($vehicle['name']); ?>">
                                </div>
                                <div class="rule-item">
                                    <label>Capacity</label>
                                    <input type="number" data-field="capacity" value="<?php echo $vehicle['capacity']; ?>">
                                </div>
                                <div class="rule-item">
                                    <label>Description</label>
                                    <input type="text" data-field="description" value="<?php echo htmlspecialchars($vehicle['description']); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <button class="global-save" onclick="saveAllChanges()">
                <i class="fas fa-save"></i> Save All
            </button>
        <?php endif; ?>
    </div>

    <script>
        let routesData = <?php echo json_encode($routesData); ?>;

        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        function filterRoutes() {
            const search = document.getElementById('searchRoutes').value.toLowerCase();
            const category = document.getElementById('filterCategory').value;

            document.querySelectorAll('.route-card[data-index]').forEach(card => {
                const from = card.dataset.from;
                const to = card.dataset.to;
                const cat = card.dataset.category;

                const matchesSearch = from.includes(search) || to.includes(search);
                const matchesCategory = !category || cat === category;

                card.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
            });
        }

        function editRoute(index) {
            document.querySelector(`.route-card[data-index="${index}"]`).classList.add('editing');
        }

        function cancelEdit(index) {
            document.querySelector(`.route-card[data-index="${index}"]`).classList.remove('editing');
        }

        function saveRoute(index) {
            const card = document.querySelector(`.route-card[data-index="${index}"]`);

            routesData.routes[index] = {
                from: card.querySelector('[data-field="from"]').value,
                to: card.querySelector('[data-field="to"]').value,
                category: card.querySelector('[data-field="category"]').value,
                pricing: {
                    full_size_car: parseFloat(card.querySelector('[data-field="pricing.full_size_car"]').value) || null,
                    standard_suv: parseFloat(card.querySelector('[data-field="pricing.standard_suv"]').value) || null,
                    full_size_suv: parseFloat(card.querySelector('[data-field="pricing.full_size_suv"]').value) || null,
                    minivan: parseFloat(card.querySelector('[data-field="pricing.minivan"]').value) || null,
                    sprinter: parseFloat(card.querySelector('[data-field="pricing.sprinter"]').value) || null
                }
            };

            card.classList.remove('editing');
            saveAllChanges();
        }

        function deleteRoute(index) {
            if (confirm('Delete this route?')) {
                routesData.routes.splice(index, 1);
                saveAllChanges();
            }
        }

        function addNewRoute() {
            // Ensure routes array exists
            if (!routesData.routes) {
                routesData.routes = [];
            }
            routesData.routes.unshift({
                from: 'New Location',
                to: 'Destination',
                category: 'local',
                pricing: { full_size_car: 0, standard_suv: 0, full_size_suv: 0, minivan: 0, sprinter: 0 }
            });
            saveAllChanges();
        }

        function updateFeaturedRoute(index) {
            const select = document.querySelector(`.featured-route-select[data-index="${index}"]`);
            const preview = document.getElementById(`preview-${index}`);

            if (select && preview) {
                const value = select.value;
                if (value) {
                    const [from, to] = value.split('|');
                    preview.textContent = `${from} to ${to}`;
                } else {
                    preview.textContent = 'Not selected';
                }
            }
        }

        function saveAllChanges() {
            // Update pricing rules
            if (document.getElementById('customRatePerMile')) {
                routesData.pricing_rules.custom_rate_per_mile = parseFloat(document.getElementById('customRatePerMile').value) || 0;
                routesData.pricing_rules.round_trip_multiplier = parseFloat(document.getElementById('roundTripMultiplier').value) || 0;
                routesData.pricing_rules.stop_fees.short_stop = parseFloat(document.getElementById('shortStopFee').value) || 0;
                routesData.pricing_rules.stop_fees.one_hour_stop = parseFloat(document.getElementById('oneHourStopFee').value) || 0;
                routesData.pricing_rules.time_surcharges.rush_hour.multiplier = parseFloat(document.getElementById('rushHourMultiplier').value) || 0;
                routesData.pricing_rules.time_surcharges.late_night.flat_fee = parseFloat(document.getElementById('lateNightFee').value) || 0;
                routesData.pricing_rules.time_surcharges.weekend.multiplier = parseFloat(document.getElementById('weekendMultiplier').value) || 0;
            }

            // Update featured routes
            const featuredSelects = document.querySelectorAll('.featured-route-select');
            const featuredDescriptions = document.querySelectorAll('.featured-description');
            if (featuredSelects.length > 0) {
                routesData.featured_routes = [];
                featuredSelects.forEach((select, index) => {
                    const value = select.value;
                    const description = featuredDescriptions[index]?.value || '';
                    if (value) {
                        const [from, to] = value.split('|');
                        routesData.featured_routes.push({ from, to, description });
                    }
                });
            }

            // Update vehicle types
            document.querySelectorAll('[data-vehicle]').forEach(card => {
                const key = card.dataset.vehicle;
                routesData.vehicle_types[key] = {
                    name: card.querySelector('[data-field="name"]').value,
                    capacity: parseInt(card.querySelector('[data-field="capacity"]').value),
                    description: card.querySelector('[data-field="description"]').value
                };
            });

            // Submit using textarea to handle special characters
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const jsonInput = document.createElement('textarea');
            jsonInput.name = 'json_data';
            jsonInput.value = JSON.stringify(routesData, null, 2);
            form.appendChild(jsonInput);

            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'update_json';
            submitInput.value = '1';
            form.appendChild(submitInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
