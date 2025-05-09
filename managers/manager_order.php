<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../php/db.php';

// Check if user is signed in and is a manager
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    $_SESSION['error'] = "You must be a manager to access this page.";
    header("Location: ../login.php");
    exit();
}

// Fetch all orders with user, food, and delivery details
$sql = "SELECT o.id, o.username, o.email, o.dorm_block, o.room_number, o.total, o.status, o.created_at, 
               f.title AS food_title, u.username AS delivery_person_name
        FROM orders o
        JOIN food_posts f ON o.food_post_id = f.id
        LEFT JOIN users u ON o.delivery_person_id = u.id
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available delivery persons
$sql = "SELECT id, username AS name FROM users WHERE is_delivery_person = TRUE AND availability = TRUE";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$delivery_persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delivery assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_delivery'])) {
    $order_id = $_POST['order_id'];
    $delivery_person_id = $_POST['delivery_person_id'];

    try {
        $pdo->beginTransaction();

        // Update order with delivery person and status
        $sql = "UPDATE orders SET delivery_person_id = :delivery_person_id, status = 'Assigned' WHERE id = :order_id AND status = 'Pending'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['delivery_person_id' => $delivery_person_id, 'order_id' => $order_id]);

        // Set delivery person availability to false
        $sql = "UPDATE users SET availability = FALSE WHERE id = :delivery_person_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['delivery_person_id' => $delivery_person_id]);

        $pdo->commit();
        $_SESSION['status'] = ['type' => 'success', 'msg' => 'Delivery assigned successfully!'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Failed to assign delivery: " . $e->getMessage());
        $_SESSION['error'] = "Failed to assign delivery. Please try again.";
    }
    header("Location: manager_order.php");
    exit();
}

// Handle mark as delivered
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_delivered'])) {
    $order_id = $_POST['order_id'];
    $delivery_person_id = $_POST['delivery_person_id'];

    try {
        $pdo->beginTransaction();

        // Update order status to Delivered
        $sql = "UPDATE orders SET status = 'Delivered' WHERE id = :order_id AND status = 'Assigned'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['order_id' => $order_id]);

        // Set delivery person availability to true
        $sql = "UPDATE users SET availability = TRUE WHERE id = :delivery_person_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['delivery_person_id' => $delivery_person_id]);

        $pdo->commit();
        $_SESSION['status'] = ['type' => 'success', 'msg' => 'Order marked as delivered!'];
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Failed to mark as delivered: " . $e->getMessage());
        $_SESSION['error'] = "Failed to mark as delivered. Please try again.";
    }
    header("Location: manager_order.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manager - View Orders</title>
    <link rel="stylesheet" href="../../signup.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f5e6cc; /* Cream background */
        }

        .first-section {
            background-image: url('../../images/grab-W_UiSLqthaU-unsplash.jpg');
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
            background: #b64c1c; /* Brown */
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 1400px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .orders-title {
            font-size: 28px;
            font-weight: bold;
            color: #f5e6cc; /* Cream */
            margin-bottom: 20px;
            text-align: center;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background: #f5e6cc; /* Cream */
            border-radius: 10px;
            overflow: hidden;
            max-height: 500px;
            overflow-y: auto;
        }

        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #b64c1c; /* Brown */
        }

        .orders-table th {
            background: #8b3a0e; /* Darker brown */
            color: #f5e6cc; /* Cream */
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .orders-table td {
            color: #333;
        }

        .orders-table tr:hover {
            background: #e6d4b5; /* Lighter cream */
        }

        .no-orders {
            text-align: center;
            color: #f5e6cc; /* Cream */
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

        .assign-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .assign-select {
            padding: 8px;
            border: 1px solid #8b3a0e; /* Darker brown */
            border-radius: 5px;
            background: #f5e6cc; /* Cream */
            color: #333;
        }

        .assign-btn, .delivered-btn {
            background: #16a34a;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .assign-btn:hover, .delivered-btn:hover {
            background: #15803d;
        }

        /* Modal styles */
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

        /* Responsive design */
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

            .assign-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .assign-select, .assign-btn, .delivered-btn {
                width: 100%;
                font-size: 12px;
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
                    <img class="logo" src="../../images/icon.png" alt="Campus Bite Logo">
                </div>
                <div class="nav-elements" id="nav-elements">
                    <h1><a href="../../home/home.php">Home</a></h1>
                    <h1><a href="../managers/food_post.php">Manage Food</a></h1>
                    <h1><a href="../managers/manager_order.php">Orders</a></h1>
                    <h1><a href="../../contact/contact.html">Contact</a></h1>
                </div>
                <div>
                    <a href="../managers/food_post.php" class="order-button">Manage Food</a>
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
                <h1 class="orders-title">Manage Orders</h1>
                <?php if (empty($orders)): ?>
                    <p class="no-orders">No orders found.</p>
                <?php else: ?>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Food</th>
                                    <th>Total (ብር)</th>
                                    <th>Dorm Block</th>
                                    <th>Room Number</th>
                                    <th>Status</th>
                                    <th>Order Time</th>
                                    <th>Delivery Person</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                                        <td><?php echo htmlspecialchars($order['food_title']); ?></td>
                                        <td><?php echo number_format($order['total'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['dorm_block']); ?></td>
                                        <td><?php echo htmlspecialchars($order['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($order['created_at']))); ?></td>
                                        <td><?php echo $order['delivery_person_name'] ? htmlspecialchars($order['delivery_person_name']) : 'Unassigned'; ?></td>
                                        <td>
                                            <?php if ($order['status'] === 'Pending'): ?>
                                                <form class="assign-form" method="POST" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                                                    <select name="delivery_person_id" class="assign-select" required>
                                                        <option value="" disabled selected>Select Delivery Person</option>
                                                        <?php foreach ($delivery_persons as $dp): ?>
                                                            <option value="<?php echo htmlspecialchars($dp['id']); ?>">
                                                                <?php echo htmlspecialchars($dp['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" name="assign_delivery" class="assign-btn">Assign</button>
                                                </form>
                                            <?php elseif ($order['status'] === 'Assigned'): ?>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                                                    <input type="hidden" name="delivery_person_id" value="<?php echo htmlspecialchars($order['delivery_person_id']); ?>">
                                                    <button type="submit" name="mark_delivered" class="delivered-btn">Mark as Delivered</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
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