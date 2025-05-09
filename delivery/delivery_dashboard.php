<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../php/db.php';

error_log("Session: user_id=" . ($_SESSION['user_id'] ?? 'unset') . ", role=" . ($_SESSION['role'] ?? 'unset'));

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'delivery') {
    $_SESSION['error'] = "You must be a delivery person to access this page.";
    error_log("Access denied: Invalid session or role");
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT username, role, is_delivery_person FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

error_log("User query result for user_id=$user_id: " . ($user ? print_r($user, true) : 'No rows'));

if (!$user || $user['role'] !== 'delivery') {
    $_SESSION['error'] = "Delivery person profile not found for user_id: $user_id.";
    error_log("Error: User not found or not a delivery person for user_id=$user_id");
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT o.id, o.username, o.email, o.dorm_block, o.room_number, o.total, o.status, o.created_at, 
               f.title AS food_title, f.description AS food_description, f.price AS food_price
        FROM orders o
        JOIN food_posts f ON o.food_post_id = f.id
        WHERE o.delivery_person_id = :delivery_person_id
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['delivery_person_id' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

error_log("Orders for user_id=$user_id: " . count($orders) . " found");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard</title>
    <link rel="stylesheet" href="../signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a3c34, #2e5b52);
            min-height: 100vh;
            color: #fff;
        }

        .first-section {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        nav {
            background: rgba(26, 60, 52, 0.95);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .logo {
            height: 50px;
            filter: brightness(0) invert(1);
            transition: transform 0.3s;
            font-family: 'Great Vibes', cursive;
            font-size: 2.2rem;
            letter-spacing: 2px;
            color: #fff;
            background: none;
            border: none;
            padding: 0;
        }

        .logo:hover {
            transform: scale(1.1);
        }

        .nav-elements {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-elements a {
            color: #fff;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .nav-elements a:hover,
        .nav-elements a.active {
            background: #2ecc71;
            color: #1a3c34;
        }

        .hamburger {
            display: none;
            font-size: 1.5rem;
            color: #fff;
            cursor: pointer;
        }

        .orders-section {
            flex: 1;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .orders-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            width: 100%;
            max-width: 1200px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .orders-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a3c34;
            margin-bottom: 20px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .no-orders {
            text-align: center;
            color: #7f8c8d;
            font-size: 1.2rem;
            margin: 20px 0;
            font-style: italic;
        }

        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .alert-success {
            background: #e7f4e9;
            color: #2e7d32;
        }

        .alert-danger {
            background: #fce4e4;
            color: #c62828;
        }

        #error-modal.modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #error-modal.modal.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background: #fff;
            padding: 25px;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-content p {
            margin: 0;
            padding: 10px 0;
            font-size: 1.1rem;
            color: #333;
        }

        .modal-content button {
            background: #2ecc71;
            border: none;
            color: #fff;
            padding: 12px 25px;
            font-size: 1rem;
            margin-top: 15px;
            cursor: pointer;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .modal-content button:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }

        /* Desktop table styles */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .orders-table th {
            background: #1a3c34;
            color: #fff;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .orders-table td {
            color: #333;
        }

        .orders-table tr {
            transition: background 0.2s;
        }

        .orders-table tr:hover {
            background: #f1f8f5;
            cursor: pointer;
        }

        .status-pending {
            color: #e67e22;
            font-weight: 600;
        }

        .status-delivered {
            color: #2ecc71;
            font-weight: 600;
        }

        /* Mobile card styles */
        @media (max-width: 768px) {
            nav {
                flex-wrap: wrap;
                gap: 10px;
            }

            .hamburger {
                display: block;
            }

            .nav-elements {
                display: none;
                flex-direction: column;
                width: 100%;
                gap: 10px;
                text-align: center;
                padding: 10px 0;
            }

            .nav-elements.active {
                display: flex;
                animation: slideDown 0.3s ease;
            }

            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .orders-container {
                padding: 15px;
            }

            .orders-title {
                font-size: 1.6rem;
            }

            .orders-table {
                display: block;
            }

            .orders-table table,
            .orders-table thead,
            .orders-table tbody,
            .orders-table th,
            .orders-table td,
            .orders-table tr {
                display: block;
            }

            .orders-table thead {
                display: none;
            }

            .orders-table tr {
                margin-bottom: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s;
            }

            .orders-table tr:hover {
                transform: scale(1.02);
            }

            .orders-table td {
                display: flex;
                justify-content: space-between;
                padding: 12px;
                border-bottom: none;
                font-size: 0.95rem;
                position: relative;
                text-align: right;
            }

            .orders-table td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #1a3c34;
                text-align: left;
                flex: 1;
                white-space: nowrap;
            }

            .orders-table td:last-child {
                border-bottom: none;
            }
        }

        @media (max-width: 480px) {
        

            .nav-elements a {
                font-size: 0.95rem;
                padding: 6px 12px;
            }

            .orders-title {
                font-size: 1.4rem;
            }

            .orders-table td {
                font-size: 0.9rem;
                padding: 10px;
            }

            .modal-content {
                padding: 15px;
                width: 95%;
            }

            .modal-content p {
                font-size: 1rem;
            }

            .modal-content button {
                font-size: 0.95rem;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>
    <section class="first-section">
        <header>
            <nav>
                <div>
                   <span class="logo">
                        Campus <span style="color: #2ecc71;">Bites</span>
                    </span>
                </div>
                <i class="fas fa-bars hamburger" id="hamburger"></i>
                <div class="nav-elements" id="nav-elements">
                    <a href="./delivery_dashboard.php" class="active">Delivery Dashboard</a>
                </div>
            </nav>
        </header>
        <section class="orders-section">
            <div class="orders-container">
                <?php if (!empty($_SESSION['status'])): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['status']['type']); ?>">
                        <i class="fas fa-<?php echo $_SESSION['status']['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($_SESSION['status']['msg']); unset($_SESSION['status']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="modal active" id="error-modal">
                        <div class="modal-content">
                            <p><?php echo htmlspecialchars($_SESSION['error']); ?></p>
                            <button onclick="closeModal()">Close</button>
                        </div>
                    </div>
                <?php unset($_SESSION['error']); endif; ?>
                <h1 class="orders-title"><i class="fas fa-truck"></i> Delivery Dashboard</h1>
                <?php if (empty($orders)): ?>
                    <p class="no-orders"><i class="fas fa-box-open"></i> No assigned orders found.</p>
                <?php else: ?>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer Username</th>
                                    <th>Customer Email</th>
                                    <th>Dorm Block</th>
                                    <th>Room Number</th>
                                    <th>Food Title</th>
                                    <th>Food Description</th>
                                    <th>Price (ብር)</th>
                                    <th>Total (ብር)</th>
                                    <th>Status</th>
                                    <th>Order Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td data-label="Order ID"><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td data-label="Customer Username"><?php echo htmlspecialchars($order['username']); ?></td>
                                        <td data-label="Customer Email"><?php echo htmlspecialchars($order['email']); ?></td>
                                        <td data-label="Dorm Block"><?php echo htmlspecialchars($order['dorm_block']); ?></td>
                                        <td data-label="Room Number"><?php echo htmlspecialchars($order['room_number']); ?></td>
                                        <td data-label="Food Title"><?php echo htmlspecialchars($order['food_title']); ?></td>
                                        <td data-label="Food Description"><?php echo htmlspecialchars($order['food_description']); ?></td>
                                        <td data-label="Price (ብር)"><?php echo number_format($order['food_price'], 2); ?></td>
                                        <td data-label="Total (ብር)"><?php echo number_format($order['total'], 2); ?></td>
                                        <td data-label="Status" class="status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </td>
                                        <td data-label="Order Time"><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['created_at']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </section>
    <script>
        function closeModal() {
            const modal = document.getElementById('error-modal');
            if (modal) {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        }

        window.onload = function() {
            const modal = document.getElementById('error-modal');
            if (modal && modal.classList.contains('active')) {
                modal.style.display = 'flex';
            }

            const hamburger = document.getElementById('hamburger');
            const navElements = document.getElementById('nav-elements');

            hamburger.addEventListener('click', () => {
                navElements.classList.toggle('active');
                hamburger.classList.toggle('fa-bars');
                hamburger.classList.toggle('fa-times');
            });
        };
    </script>
</body>
</html>