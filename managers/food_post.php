<?php
session_start();
require_once '../php/db.php';

// Check if user is a manager
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'manager') {
    $_SESSION['error'] = "You must be a manager to access this page.";
    header("Location: ../../login.php");
    exit();
}

// Fetch post for editing if ID is provided
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

        .form-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .form-container {
            background: #b64c1c; /* Brown */
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 28px;
            font-weight: bold;
            color: #f5e6cc; /* Cream */
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #f5e6cc; /* Cream */
            font-size: 16px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #8b3a0e; /* Darker brown */
            border-radius: 10px;
            background: #f5e6cc; /* Cream */
            color: #333;
            font-size: 16px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input[type="file"] {
            padding: 3px;
            background: none;
            border: none;
        }

        .form-group .current-image {
            margin-top: 10px;
            color: #f5e6cc; /* Cream */
        }

        .form-group .current-image img {
            max-width: 100px;
            border-radius: 8px;
            margin-top: 5px;
        }

        .submit-btn {
            background: #16a34a;
            color: white;
            padding: 15px;
            width: 100%;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #15803d;
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

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
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
            .form-container {
                width: 95%;
                padding: 20px;
            }

            .form-title {
                font-size: 24px;
            }

            .form-group label,
            .form-group input,
            .form-group textarea {
                font-size: 14px;
            }

            .submit-btn {
                font-size: 16px;
                padding: 12px;
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
                    <h1><a href="./food_post.php">Manage Food</a></h1>
                    <h1><a href="./manager_order.php">Orders</a></h1>
                    <h1><a href="../../contact/contact.html">Contact</a></h1>
                </div>
                <div>
                    <a href="./food_post.php" class="order-button">Manage Food</a>
                </div>
            </nav>
        </header>
        <section class="form-section">
            <div class="form-container">
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
                                <img src="/CAMPUS_BITES_WEB_AP/<?php echo htmlspecialchars($post['image_path']); ?>" alt="Current Image">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="submit-btn"><?php echo $post ? 'Update Post' : 'Create Post'; ?></button>
                </form>
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
