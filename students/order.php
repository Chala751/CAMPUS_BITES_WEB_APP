<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please sign in to place an order.";
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT * FROM food_posts ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $selected_items = json_decode($_POST['selected_items'], true);
    $dorm_block = trim($_POST['dorm_block']);
    $room_number = trim($_POST['room_number']);
    $total = floatval($_POST['total']);

    if (empty($selected_items)) {
        $_SESSION['error'] = "Please select at least one food item.";
    } elseif (empty($dorm_block) || empty($room_number)) {
        $_SESSION['error'] = "Please provide dorm block and room number.";
    } else {
        try {
            $pdo->beginTransaction();

            foreach ($selected_items as $item) {
                $sql = "INSERT INTO orders (student_id, food_post_id, username, email, total, dorm_block, room_number, status, created_at) 
                        VALUES (:student_id, :food_post_id, :username, :email, :total, :dorm_block, :room_number, 'Pending', CURRENT_TIMESTAMP)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'student_id' => $user_id,
                    'food_post_id' => $item['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'total' => $item['price'],
                    'dorm_block' => $dorm_block,
                    'room_number' => $room_number
                ]);
            }

            $pdo->commit();

            $_SESSION['status'] = [
                'type' => 'success',
                'msg' => 'Order placed successfully! You will receive a confirmation soon.'
            ];
            header("Location: order.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Failed to place order: " . $e->getMessage());
            $_SESSION['error'] = "Failed to place order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Food</title>
    <link rel="stylesheet" href="../signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #10B249;
            --dark-green: #0E9A3F;
            --black: #121212;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --text-gray: #555;
            --container-width: 1200px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a3c34, #2e5b52);
            min-height: 100vh;
            color: var(--white);
        }

        .first-section {
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

        .order {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem;
        }

        .order-container {
            background: rgba(255, 255, 255, 0.95);
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

        .order-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .food-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .food-item {
            background: #A9745B;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .food-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .food-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .food-item h3 {
            font-size: 1.25rem;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .food-item .desc {
            font-size: 0.9rem;
            color: var(--light-gray);
            margin-bottom: 0.5rem;
        }

        .food-item .price {
            font-size: 1rem;
            font-weight: 600;
            color: var(--white);
        }

        .food-item.selected {
            border: 2px solid var(--primary-green);
            background: #8b5b3e;
        }

        .selected-items {
            font-size: 1rem;
            color: var(--black);
            margin-bottom: 1rem;
            min-height: 1.5rem;
            text-align: center;
        }

        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .unselect-btn {
            background: #dc2626;
            color: var(--white);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .unselect-btn:hover {
            background: #b91c1c;
        }

        .total-fee {
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--black);
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--black);
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: var(--white);
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: var(--primary-green);
            outline: none;
        }

        .place-order-btn {
            background: var(--primary-green);
            color: var(--white);
            padding: 1rem;
            width: 100%;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .place-order-btn:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
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
        @media (max-width: 1024px) {
            .order-container {
                padding: 1.5rem;
            }

            .order-title {
                font-size: 1.75rem;
            }

            .food-list {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }

            .food-item img {
                height: 100px;
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

            .order {
                padding: 1rem;
            }

            .order-container {
                width: 95%;
            }

            .food-list {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            }

            .food-item h3 {
                font-size: 1.1rem;
            }

            .food-item .desc {
                font-size: 0.85rem;
            }

            .order-summary {
                flex-direction: column;
                align-items: flex-start;
            }

            .total-fee {
                margin-top: 1rem;
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

            .order-title {
                font-size: 1.5rem;
            }

            .food-list {
                grid-template-columns: 1fr;
            }

            .food-item img {
                height: 80px;
            }

            .food-item h3 {
                font-size: 1rem;
            }

            .food-item .desc {
                font-size: 0.8rem;
            }

            .selected-items, .total-fee {
                font-size: 0.9rem;
            }

            .unselect-btn, .place-order-btn {
                font-size: 0.9rem;
                padding: 0.75rem;
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
                    <a href="student_home.php">Home</a>
                    <a href="order.php" class="active">Order</a>
                    <a href="../contact/contact.html">Contact</a>
                </div>
            </nav>
        </header>
        <section class="order">
            <div class="order-container">
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
                <h1 class="order-title"><i class="fas fa-utensils"></i> Select Food</h1>
                <div class="food-list" id="food-list">
                    <?php if (empty($foods)): ?>
                        <p>No food items available.</p>
                    <?php else: ?>
                        <?php foreach ($foods as $food): ?>
                            <div class="food-item" 
                                 data-id="<?php echo htmlspecialchars($food['id']); ?>" 
                                 data-title="<?php echo htmlspecialchars($food['title']); ?>" 
                                 data-price="<?php echo htmlspecialchars($food['price']); ?>">
                                <img src="/CAMPUS_BITES_WEB_APP/<?php echo htmlspecialchars($food['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($food['title']); ?>">
                                <h3><?php echo htmlspecialchars($food['title']); ?></h3>
                                <p class="desc"><?php echo htmlspecialchars($food['description']); ?></p>
                                <p class="price">ብር <?php echo number_format($food['price'], 2); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="selected-items" id="selected-items">No items selected</div>
                <div class="order-summary">
                    <button class="unselect-btn" id="unselect-btn">Unselect All</button>
                    <p class="total-fee" id="total-fee">Estimated Total: 0 ብር</p>
                </div>
                <form method="POST" id="order-form">
                    <input type="hidden" name="selected_items" id="selected-items-input">
                    <input type="hidden" name="total" id="total-input">
                    <div class="input-group">
                        <label for="dorm">Dorm Block</label>
                        <input type="text" id="dorm" name="dorm_block" placeholder="Enter dorm block" required />
                    </div>
                    <div class="input-group">
                        <label for="room">Room Number</label>
                        <input type="text" id="room" name="room_number" placeholder="Enter room number" required />
                    </div>
                    <button type="submit" name="place_order" class="place-order-btn" id="place-order-btn">Place Order</button>
                </form>
            </div>
        </section>
    </section>
    <script>
        const foodItems = document.querySelectorAll('.food-item');
        const selectedItemsDiv = document.getElementById('selected-items');
        const totalFee = document.getElementById('total-fee');
        const unselectBtn = document.getElementById('unselect-btn');
        const selectedItemsInput = document.getElementById('selected-items-input');
        const totalInput = document.getElementById('total-input');
        const hamburger = document.getElementById('hamburger');
        const navElements = document.getElementById('nav-elements');
        let selectedItems = [];

        foodItems.forEach(item => {
            item.addEventListener('click', () => {
                const id = item.getAttribute('data-id');
                const title = item.getAttribute('data-title');
                const price = parseFloat(item.getAttribute('data-price'));

                if (item.classList.contains('selected')) {
                    item.classList.remove('selected');
                    selectedItems = selectedItems.filter(selected => selected.id !== id);
                } else {
                    item.classList.add('selected');
                    selectedItems.push({ id, title, price });
                }

                updateSelectedItems();
            });
        });

        unselectBtn.addEventListener('click', () => {
            selectedItems = [];
            foodItems.forEach(item => item.classList.remove('selected'));
            updateSelectedItems();
        });

        function updateSelectedItems() {
            selectedItemsDiv.textContent = selectedItems.length === 0 
                ? 'No items selected' 
                : selectedItems.map(item => item.title).join(', ');

            const total = selectedItems.reduce((sum, item) => sum + item.price, 0);
            totalFee.textContent = `Estimated Total: ${total.toFixed(2)} ብር`;

            selectedItemsInput.value = JSON.stringify(selectedItems);
            totalInput.value = total.toFixed(2);
        }

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

        hamburger.addEventListener('click', () => {
            navElements.classList.toggle('active');
            hamburger.classList.toggle('fa-bars');
            hamburger.classList.toggle('fa-times');
        });
    </script>
</body>
</html>