<?php
session_start();
require_once '../php/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    $_SESSION['error'] = "You must be a manager to access this page.";
    header("Location: ../../login.php");
    exit();
}

$post = null;
if (isset($_GET['edit'])) {
    $postId = $_GET['edit'];
    $sql = "SELECT * FROM food_posts WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? 'Edit Food Post' : 'Create Food Post'; ?></title>
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

        .form-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .form-container {
            background: var(--brown);
            padding: 2rem;
            border-radius: 15px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--cream);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--cream);
            font-size: 1rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--dark-brown);
            border-radius: 10px;
            background: var(--cream);
            color: var(--black);
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary-green);
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input[type="file"] {
            padding: 0.2rem;
            background: none;
            border: none;
        }

        .form-group .current-image {
            margin-top: 0.5rem;
            color: var(--cream);
        }

        .form-group .current-image img {
            max-width: 100px;
            border-radius: 8px;
            margin-top: 0.5rem;
        }

        .submit-btn {
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

        .submit-btn:hover {
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

        .alert-error {
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
            .form-container {
                padding: 1.5rem;
                max-width: 500px;
            }

            .form-title {
                font-size: 1.75rem;
            }

            .form-group label,
            .form-group input,
            .form-group textarea {
                font-size: 0.95rem;
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

            .form-section {
                padding: 1rem;
            }

            .form-container {
                width: 95%;
                padding: 1.5rem;
            }

            .form-title {
                font-size: 1.5rem;
            }

            .form-group label,
            .form-group input,
            .form-group textarea {
                font-size: 0.9rem;
            }

            .submit-btn {
                font-size: 1rem;
                padding: 0.75rem;
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

            .form-container {
                padding: 1rem;
            }

            .form-title {
                font-size: 1.25rem;
            }

            .form-group label,
            .form-group input,
            .form-group textarea {
                font-size: 0.85rem;
            }

            .form-group .current-image img {
                max-width: 80px;
            }

            .submit-btn {
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
                    <a href="./home.php">Home</a>
                    <a href="./food_post.php" class="active">Manage Food</a>
                    <a href="./manager_order.php">Orders</a>
                    <a href="../managers/contact.php">Contact</a>
                </div>
            </nav>
        </header>
        <section class="form-section">
            <div class="form-container">
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
                <h1 class="form-title"><?php echo $post ? 'Edit Food Post' : 'Create Food Post'; ?></h1>
                <form action="../php/food_posts.php" method="POST" enctype="multipart/form-data">
                    <?php if ($post): ?>
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo $post ? htmlspecialchars($post['title']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php echo $post ? htmlspecialchars($post['description']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (ብር)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $post ? htmlspecialchars($post['price']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif" <?php echo $post ? '' : 'required'; ?>>
                        <?php if ($post && $post['image_path']): ?>
                            <div class="current-image">
                                <p>Current Image:</p>
                                <img src="/CAMPUS_BITES_WEB_APP/<?php echo htmlspecialchars($post['image_path']); ?>" alt="Current Image">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="submit-btn"><?php echo $post ? 'Update Post' : 'Create Post'; ?></button>
                </form>
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