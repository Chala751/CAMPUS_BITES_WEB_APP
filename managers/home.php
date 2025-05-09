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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager - Campus Bites</title>
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
            --container-width: 1200px;
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

        .home-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .home-container {
            background: var(--brown);
            padding: 2rem;
            border-radius: 15px;
            width: 100%;
            max-width: var(--container-width);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.5s ease-in;
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .home-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--cream);
            margin-bottom: 1rem;
        }

        .home-subtitle {
            font-size: 1.2rem;
            color: var(--light-cream);
            margin-bottom: 2rem;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .link-card {
            background: var(--dark-brown);
            padding: 1.5rem;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .link-card i {
            font-size: 2rem;
            color: var(--primary-green);
            margin-bottom: 0.5rem;
        }

        .link-card h3 {
            font-size: 1.25rem;
            color: var(--cream);
            margin-bottom: 0.5rem;
        }

        .link-card p {
            font-size: 0.9rem;
            color: var(--light-cream);
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
            .home-container {
                padding: 1.5rem;
            }

            .home-title {
                font-size: 2rem;
            }

            .home-subtitle {
                font-size: 1.1rem;
            }

            .quick-links {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

            .home-section {
                padding: 1rem;
            }

            .home-container {
                width: 95%;
            }

            .home-title {
                font-size: 1.75rem;
            }

            .home-subtitle {
                font-size: 1rem;
            }

            .quick-links {
                grid-template-columns: 1fr;
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

            .home-title {
                font-size: 1.5rem;
            }

            .home-subtitle {
                font-size: 0.9rem;
            }

            .link-card h3 {
                font-size: 1.1rem;
            }

            .link-card p {
                font-size: 0.85rem;
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
                    <a href="home.php" class="active">Home</a>
                    <a href="../managers/food_post.php">Manage Food</a>
                    <a href="../managers/manager_order.php">Orders</a>
                    <a href="../managers/contact.php">Contact</a>
                </div>
            </nav>
        </header>
        <section class="home-section">
            <div class="home-container">
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
                <h1 class="home-title">Welcome, Manager!</h1>
                <p class="home-subtitle">Manage your food posts and orders efficiently with Campus Bites.</p>
                <div class="quick-links">
                    <a href="../managers/food_post.php" class="link-card">
                        <i class="fas fa-utensils"></i>
                        <h3>Manage Food</h3>
                        <p>Create or edit food posts to keep your menu updated.</p>
                    </a>
                    <a href="../managers/manager_order.php" class="link-card">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>View Orders</h3>
                        <p>Assign deliveries and track order statuses.</p>
                    </a>
                    <a href="contact.php" class="link-card">
                        <i class="fas fa-envelope"></i>
                        <h3>Contact Support</h3>
                        <p>Reach out for any assistance or feedback.</p>
                    </a>
                </div>
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