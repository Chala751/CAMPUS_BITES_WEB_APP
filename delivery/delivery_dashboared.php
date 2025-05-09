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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivery Dashboard</title>
    <link rel="stylesheet" href="../signup.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f5e6cc;
        }

        .first-section {
            background-image: url('../images/grab-W_UiSLqthaU-unsplash.jpg');
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
        }

        .logo {
            height: 40px;
            filter: brightness(0) invert(1);
        }

        .nav-elements {
            display: flex;
            gap: 20px;
        }

        .nav-elements h1 a {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .nav-elements h1 a:hover {
            color: #10B249;
        }

        .order-button {
            background: #10B249;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .orders-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .orders-container {
            background: #b64c1c;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 1400px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .orders-title {
            font-size: 28px;
            font-weight: bold;
            color: #f5e6cc;
            margin-bottom: 20px;
            text-align: center;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: #f5e6cc;
            border-radius: 10px;
            overflow: hidden;
            max-height: 500px;
            overflow-y: auto;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #b64c1c;
        }

        .orders-table th {
            background: #8b3a0e;
            color: #f5e6cc;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .orders-table td {
            color: #333;
        }

        .orders-table tr:hover {
            background: #e6d4b5;
        }

        .no-orders {
            text-align: center;
            color: #f5e6cc;
            font-size: 18px;
            margin-top: 20px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        #error-modal.modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            overflow: auto;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #error-modal.modal.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .modal-content p {
            margin: 0;
            padding: 10px 0;
            font-size: 16px;
            color: #333;
        }

        .modal-content button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .modal-content button:hover {
            background-color: white;
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }

        @media (max-width: 768px) {
            .orders-container {
                width: 95%;
                padding: 20px;
            }

            .orders-table th,
            .orders-table td {
                font-size: 14px;
                padding: 8px;
            }

            .orders-title {
                font-size: 24px;
            }

            .modal-content {
                width: 90%;
                padding: 15px;
            }

            .modal-content p {
                font-size: 14px;
            }

            .modal-content button {
                padding: 8px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <section class="first-section">
        <header>
            <nav>
                <div>
                    <img class="logo" src="../images/icon.png" alt="Campus Bite Logo">
                </div>
                <div class="nav-elements" id="nav-elements">
                    <h1><a href="../home/home.php">Home</a></h1>
                    <h1><a href="./delivery_dashboard.php">Delivery Dashboard</a></h1>
                    <h1><a href="../contact/contact.html">Contact</a></h1>
                </div>
                <div>
                    <a href="./delivery_dashboard.php" class="order-button">Delivery Dashboard</a>
                </div>
            </nav>
        </header>
        <section class="orders-section">
            <div class="orders-container">
                <?php if (!empty($_SESSION['status'])): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['status']['type']); ?>">
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
                <h1 class="orders-title">Delivery Dashboard</h1>
                <?php if (empty($orders)): ?>
                    <p class="no-orders">No assigned orders found.</p>
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
                                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                                        <td><?php echo htmlspecialchars($order['dorm_block']); ?></td>
                                        <td><?php echo htmlspecialchars($order['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['food_title']); ?></td>
                                        <td><?php echo htmlspecialchars($order['food_description']); ?></td>
                                        <td><?php echo number_format($order['food_price'], 2); ?></td>
                                        <td><?php echo number_format($order['total'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['created_at']))); ?></td>
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
        };
    </script>
</body>
</html>
