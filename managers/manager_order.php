<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../php/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    $_SESSION['error'] = "You must be a manager to access this page.";
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT o.id, o.username, o.email, o.dorm_block, o.room_number, o.total, o.status, o.created_at, 
               f.title AS food_title, u.username AS delivery_person_name, o.delivery_person_id
        FROM orders o
        JOIN food_posts f ON o.food_post_id = f.id
        LEFT JOIN users u ON o.delivery_person_id = u.id
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT id, username AS name FROM users WHERE is_delivery_person = TRUE AND availability = TRUE";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$delivery_persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_delivery'])) {
    $order_id = $_POST['order_id'];
    $delivery_person_id = $_POST['delivery_person_id'];

    try {
        $pdo->beginTransaction();

        $sql = "UPDATE orders SET delivery_person_id = :delivery_person_id, status = 'Assigned' WHERE id = :order_id AND status = 'Pending'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['delivery_person_id' => $delivery_person_id, 'order_id' => $order_id]);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_delivered'])) {
    $order_id = $_POST['order_id'];
    $delivery_person_id = $_POST['delivery_person_id'];

    try {
        $pdo->beginTransaction();

        $sql = "UPDATE orders SET status = 'Delivered' WHERE id = :order_id AND status = 'Assigned'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['order_id' => $order_id]);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager - View Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #10B249;
            --dark-green: #0E9A3F;
            --cream: #f5e6cc;
            --brown: #b64c1c;
            --dark-brown: #8b3a0e;
            --black: #121212;
            --white: #ffffff;
            --light-cream: #e6d4b5;
            --container-width: 1400px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: var(--cream);
            min-height: 100vh;
        }

        .first-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../../images/grab-W_UiSLqthaU-unsplash.jpg') no-repeat center/cover fixed;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        nav {
            background: rgba(26, 60, 52, 0.95);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-text {
            font-family: 'Dancing Script', cursive;
            font-size: 2rem;
            color: var(--white);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .logo-text span {
            color: var(--primary-green);
        }

        .nav-elements {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-elements a {
            color: var(--white);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .nav-elements a:hover,
        .nav-elements a.active {
            background: var(--primary-green);
            color: var(--black);
        }

        .hamburger {
            display: none;
            font-size: 1.5rem;
            color: var(--white);
            cursor: pointer;
        }

        .orders-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem;
        }

        .orders-container {
            background: var(--brown);
            padding: 2rem;
            border-radius: 15px;
            width: 100%;
            max-width: var(--container-width);
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
            color: var(--cream);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .orders-table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
            background: var(--cream);
            border-radius: 10px;
            overflow: hidden;
        }

        .orders-table th,
        .orders-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--brown);
            min-width: 100px;
        }

        .orders-table th {
            background: var(--dark-brown);
            color: var(--cream);
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .orders-table td {
            color: var(--black);
        }

        .orders-table tr:hover {
            background: var(--light-cream);
        }

        .no-orders {
            text-align: center;
            color: var(--cream);
            font-size: 1.2rem;
            margin-top: 1.5rem;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .assign-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .assign-select {
            padding: 0.5rem;
            border: 1px solid var(--dark-brown);
            border-radius: 5px;
            background: var(--cream);
            color: var(--black);
            font-size: 0.9rem;
        }

        .assign-btn, .delivered-btn {
            background: var(--primary-green);
            color: var(--white);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s, transform 0.2s;
        }

        .assign-btn:hover, .delivered-btn:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
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
            background: var(--white);
            padding: 1.5rem;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
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
            padding: 0.5rem 0;
            font-size: 1rem;
            color: var(--black);
        }

        .modal-content button {
            background: var(--primary-green);
            border: none;
            color: var(--white);
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            margin-top: 1rem;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .modal-content button:hover {
            background: var(--dark-green);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .orders-container {
                max-width: 1000px;
            }

            .orders-table th,
            .orders-table td {
                font-size: 0.95rem;
                padding: 0.6rem;
            }
        }

        @media (max-width: 992px) {
            .assign-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .assign-select, .assign-btn, .delivered-btn {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            nav {
                flex-wrap: wrap;
                padding: 1rem;
            }

            .hamburger {
                display: block;
            }

            .nav-elements {
                display: none;
                flex-direction: column;
                width: 100%;
                gap: 1rem;
                text-align: center;
                padding: 1rem 0;
            }

            .nav-elements.active {
                display: flex;
                animation: slideDown 0.3s ease;
            }

            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .orders-section {
                padding: 1rem;
            }

            .orders-container {
                width: 95%;
                padding: 1.5rem;
            }

            .orders-title {
                font-size: 1.75rem;
            }

            .orders-table th,
            .orders-table td {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            .no-orders {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .logo-text {
                font-size: 1.5rem;
            }

            .nav-elements a {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            .orders-container {
                padding: 1rem;
            }

            .orders-title {
                font-size: 1.5rem;
            }

            .orders-table th,
            .orders-table td {
                font-size: 0.85rem;
                padding: 0.4rem;
                min-width: 80px;
            }

            .assign-select, .assign-btn, .delivered-btn {
                font-size: 0.85rem;
                padding: 0.4rem;
            }

            .modal-content {
                padding: 1rem;
                width: 95%;
            }

            .modal-content p {
                font-size: 0.9rem;
            }

            .modal-content button {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <section class="first-section">
        <header>
            <nav>
                <div class="logo-container">
                    <h1 class="logo-text">Campus<span>Bite</span></h1>
                </div>
                <i class="fas fa-bars hamburger" id="hamburger"></i>
                <div class="nav-elements" id="nav-elements">
                    <a href="../managers/home.php">Home</a>
                    <a href="../managers/food_post.php">Manage Food</a>
                    <a href="../managers/manager_order.php" class="active">Orders</a>
                    <a href="../managers/contact.php">Contact</a>
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
                <h1 class="orders-title">Manage Orders</h1>
                <?php if (empty($orders)): ?>
                    <p class="no-orders">No orders found.</p>
                <?php else: ?>
                    <div class="table-wrapper">
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
                                                <?php elseif ($order['status'] === 'Assigned' && $order['delivery_person_id']): ?>
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
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </section>
    <script>
        const hamburger = document.getElementById('hamburger');
        const navElements = document.getElementById('nav-elements');

        hamburger.addEventListener('click', () => {
            navElements.classList.toggle('active');
            hamburger.classList.toggle('fa-bars');
            hamburger.classList.toggle('fa-times');
        });

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