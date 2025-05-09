<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'student_controller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Please sign in to place an order.";
        header("Location: ../login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>CampusBite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-green: #10B249;
            --dark-green: #0E9A3F;
            --black: #121212;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --text-gray: #555;
            --section-padding: 4rem 1rem;
            --container-width: 1200px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: borderbox;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background-color: var(--white);
            line-height: 1.6;
        }

        html {
            scroll-behavior: smooth;
        }

        .section-0 {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../images/grab-W_UiSLqthaU-unsplash.jpg') no-repeat center/cover fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: var(--white);
        }

        /* Navigation */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: rgba(26, 60, 52, 0.9);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo1 {
            height: 2.5rem;
            filter: brightness(0) invert(1);
            transition: transform 0.3s;
        }

        .logo1:hover {
            transform: scale(1.1);
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .brand-name span {
            color: var(--primary-green);
        }

        .nav-elements ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
        }

        .nav-elements a {
            color: var(--white);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-elements a:hover {
            color: var(--primary-green);
        }

        .hamburger {
            display: none;
            font-size: 1.5rem;
            color: var(--white);
            cursor: pointer;
        }

        /* Hero Section */
        .section-1 {
            text-align: center;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .header-content h1 {
            font-size: 3rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .header-content h1 span {
            color: var(--primary-green);
        }

         p {
            font-size: 1.2rem;
            color: var(--text-gray);
            margin: 1rem 0;
        }

        .header-buttons {
            display: flex;
            gap: 1rem;
        }

        .order-button2, .learn-more-button {
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .order-button2 {
            background: var(--primary-green);
            color: var(--white);
            border: none;
        }

        .order-button2:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
        }

        .learn-more-button {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .learn-more-button:hover {
            background: var(--white);
            color: var(--black);
            transform: translateY(-2px);
        }

        /* Slider */
        .slider-section {
            padding: var(--section-padding);
            display: flex;
            justify-content: center;
        }

        .slider {
            width: 100%;
            max-width: 800px;
            height: 400px;
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        .slides {
            display: flex;
            width: 300%;
            transition: transform 0.6s ease;
        }

        .slide img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .navigation-manual {
            position: absolute;
            bottom: 1rem;
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 0.75rem;
        }

        .manual-btn {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 0.3s;
        }

        #img1:checked ~ .s1 { margin-left: 0; }
        #img2:checked ~ .s1 { margin-left: -100%; }
        #img3:checked ~ .s1 { margin-left: -200%; }

        #img1:checked ~ .navigation-manual label:nth-child(1),
        #img2:checked ~ .navigation-manual label:nth-child(2),
        #img3:checked ~ .navigation-manual label:nth-child(3) {
            background: var(--white);
        }

        /* Features Section */
        .section-2 {
            padding: var(--section-padding);
            text-align: center;
            background: var(--light-gray);
        }

        .features-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 1rem;
        }

        .features-heading {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .features-heading span {
            color: var(--primary-green);
        }

        .features-para {
            font-size: 1.1rem;
            color: var(--text-gray);
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            max-width: var(--container-width);
            margin: 0 auto;
        }

        .feat-item {
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .feat-item h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .feat-item p {
            font-size: 0.95rem;
            color: var(--text-gray);
        }

        /* Menu Section */
        .posts-section {
            padding: var(--section-padding);
            background: var(--white);
            text-align: center;
        }

        .posts-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 1rem;
        }

        .posts-heading {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .posts-heading span {
            color: var(--primary-green);
        }

        .posts-para {
            font-size: 1.1rem;
            color: var(--text-gray);
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            max-width: var(--container-width);
            margin: 0 auto;
        }

        .menu-item {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .menu-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .menu-item h3 {
            font-size: 1.25rem;
            margin: 1rem 0 0.5rem;
            color: var(--black);
        }

        .menu-item .desc {
            font-size: 0.9rem;
            color: var(--text-gray);
            padding: 0 1rem;
        }

        .menu-item .price {
            font-size: 1.1rem;
            color: var(--primary-green);
            font-weight: 600;
            margin: 1rem 0;
        }

        .btn-order {
            background: var(--primary-green);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s, transform 0.2s;
            margin-bottom: 1rem;
        }

        .btn-order:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            margin: 1rem auto;
            border-radius: 8px;
            max-width: var(--container-width);
            text-align: center;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        /* Pricing Section */
        .pricing {
            padding: var(--section-padding);
            background: var(--light-gray);
            text-align: center;
        }

        .pricing-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 1rem;
        }

        .pricing-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .pricing-header h1 span {
            color: var(--primary-green);
        }

        .pricing-header p {
            font-size: 1.1rem;
            color: var(--text-gray);
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .pricing-plans {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            max-width: var(--container-width);
            margin: 0 auto;
        }

        .plan {
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .plan.highlight {
            background: var(--primary-green);
            color: var(--white);
            transform: scale(1.05);
        }

        .plan h4 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .plan ul {
            list-style: none;
            text-align: left;
            margin: 1rem 0;
        }

        .plan ul li {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .plan button {
            background: var(--primary-green);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s, transform 0.2s;
        }

        .plan.highlight button {
            background: var(--white);
            color: var(--black);
        }

        .plan button:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
        }

        .plan.highlight button:hover {
            background: var(--light-gray);
        }

        /* Newsletter */
        .newsletter {
            padding: 2rem;
            margin: 2rem auto;
            background: var(--primary-green);
            border-radius: 12px;
            max-width: var(--container-width);
            color: var(--white);
            text-align: center;
        }

        .newsletter p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .newsletter form {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .newsletter input {
            padding: 0.75rem;
            border: none;
            border-radius: 25px 0 0 25px;
            flex: 1;
            font-size: 1rem;
        }

        .newsletter button {
            padding: 0.75rem 1.5rem;
            background: var(--black);
            color: var(--white);
            border: none;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .newsletter button:hover {
            background: var(--dark-green);
        }

        /* Extras */
        .extras {
            padding: var(--section-padding);
            text-align: center;
        }

        .extras h3 {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .extras-items {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            max-width: var(--container-width);
            margin: 0 auto;
        }

        .extras-items img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
        }

        .extras-items p {
            font-size: 1.1rem;
            margin-top: 0.5rem;
            color: var(--black);
        }

        /* Footer */
        .footer {
            background: linear-gradient(180deg, var(--primary-green), var(--white));
            padding: var(--section-padding);
            text-align: center;
            color: var(--black);
        }

        .footer-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: var(--container-width);
            margin: 0 auto 2rem;
        }

        .footer-col h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--black);
        }

        .footer-col p, .footer-col ul li a {
            font-size: 0.95rem;
            color: var(--text-gray);
            margin-bottom: 0.5rem;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li a:hover {
            color: var(--primary-green);
        }

        .social-icons a {
            margin: 0 0.5rem;
        }

        .social-icons img {
            width: 24px;
            filter: brightness(0) invert(1);
            transition: transform 0.3s;
        }

        .social-icons img:hover {
            transform: scale(1.2);
        }

        .footer-bottom {
            font-size: 0.9rem;
            color: var(--text-gray);
            margin-top: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .header-content h1 {
                font-size: 2.5rem;
            }

            .features-heading, .posts-heading, .pricing-header h1 {
                font-size: 2rem;
            }

            .slider {
                height: 350px;
            }

            .slide img {
                height: 350px;
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
                width: 100%;
                text-align: center;
            }

            .nav-elements.active {
                display: block;
                animation: slideDown 0.3s ease;
            }

            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .nav-elements ul {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem 0;
            }

            .section-1 {
                padding: 1rem;
            }

            .header-content h1 {
                font-size: 2rem;
            }

            .header-content p {
                font-size: 1rem;
            }

            .slider {
                height: 300px;
            }

            .slide img {
                height: 300px;
            }

            .features, .pricing-plans, .extras-items {
                grid-template-columns: 1fr;
            }

            .plan {
                width: 100%;
            }

            .newsletter form {
                flex-direction: column;
                gap: 1rem;
            }

            .newsletter input {
                border-radius: 25px;
            }

            .newsletter button {
                border-radius: 25px;
            }
        }

        @media (max-width: 480px) {
            .logo1 {
                height: 2rem;
            }

            .brand-name {
                font-size: 1.25rem;
            }

            .header-content h1 {
                font-size: 1.75rem;
            }

            .order-button2, .learn-more-button {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .features-heading, .posts-heading, .pricing-header h1 {
                font-size: 1.75rem;
            }

            .features-para, .posts-para, .pricing-header p {
                font-size: 1rem;
            }

            .feat-item h1 {
                font-size: 1.25rem;
            }

            .slider {
                height: 250px;
            }

            .slide img {
                height: 250px;
            }

            .menu-item img {
                height: 150px;
            }

            .menu-item h3 {
                font-size: 1.1rem;
            }

            .menu-item .desc {
                font-size: 0.85rem;
            }

            .extras-items img {
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <section class="section-0">
        <header>
            <nav>
                <div class="logo-container">
                    <img class="logo1" src="../images/icon.png" alt="CampusBite Logo">
                    <h1 class="brand-name">Campus<span>Bite</span></h1>
                </div>
                <i class="fas fa-bars hamburger" id="hamburger"></i>
                <div class="nav-elements" id="nav-elements">
                    <ul>
                        <li><a href="student_home.php">Home</a></li>
                        <li><a href="order.php">Order</a></li>
                    </ul>
                </div>
            </nav>
        </header>
        <section class="section-1">
            <div class="header-content">
                <h1>Order Fresh Hot <span>Meals</span><br>Delivered To Your Door</h1>
                <p>Enjoy delicious, chef-prepared meals without lifting a finger. Hot, ready, and right on time.</p>
            </div>
            <div class="header-buttons">
                <button class="order-button2">Order Now</button>
                <button class="learn-more-button">See Menu</button>
            </div>
        </section>
    </section>
    <section class="slider-section">
        <div class="slider">
            <div class="slides">
                <input type="radio" name="radio-btn" id="img1" checked>
                <input type="radio" name="radio-btn" id="img2">
                <input type="radio" name="radio-btn" id="img3">
                <div class="slide s1">
                    <img src="../images/Recipes/ella-olsson-kKLRvcjQNqM-unsplash.jpg" alt="Meal 1">
                </div>
                <div class="slide">
                    <img src="../images/Recipes/gebiya-putri-IzdLRdXcNT8-unsplash.jpg" alt="Meal 2">
                </div>
                <div class="slide">
                    <img src="../images/Recipes/s-o-c-i-a-l-c-u-t-hwy3W3qFjgM-unsplash.jpg" alt="Meal 3">
                </div>
                <div class="navigation-manual">
                    <label for="img1" class="manual-btn"></label>
                    <label for="img2" class="manual-btn"></label>
                    <label for="img3" class="manual-btn"></label>
                </div>
            </div>
        </section>
    </section>
    <section class="section-2">
        <h1 class="features-title">Features</h1>
        <h1 class="features-heading">Why Choose <span>CampusBite</span>?</h1>
        <p class="features-para">From customizable plans to expert lifestyle support, discover the features that make healthy eating easy and enjoyable.</p>
        <div class="features">
            <div class="feat-item">
                <h1>Fast & Fresh <span>Delivery</span></h1>
                <p>Skip the cafeteria lines. Get your favorite campus meals delivered hot and fresh in minutes.</p>
            </div>
            <div class="feat-item">
                <h1>Student-Friendly <span>Prices</span></h1>
                <p>Enjoy delicious meals without breaking your budget. Exclusive student discounts every day!</p>
            </div>
            <div class="feat-item">
                <h1>Variety of <span>Choices</span></h1>
                <p>From local favorites to international dishes—CampusBite brings a wide range of cuisines right to your dorm.</p>
            </div>
            <div class="feat-item">
                <h1>Order <span>Tracking</span></h1>
                <p>Stay in the loop! Track your food in real-time, from kitchen prep to doorstep delivery.</p>
            </div>
        </div>
    </section>
    <section class="posts-section">
        <h1 class="posts-title">CampusBite Menu</h1>
        <h1 class="posts-heading">Available <span>Meals</span></h1>
        <p class="posts-para">Browse and order from a variety of delicious meals posted by our vendors.</p>
        <?php if (!empty($_SESSION['status'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_SESSION['status']['type']); ?>">
                <?php echo htmlspecialchars($_SESSION['status']['msg']); unset($_SESSION['status']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (empty($posts)): ?>
            <p>No meals available yet. Check back later!</p>
        <?php else: ?>
            <div class="menu-grid">
                <?php foreach ($posts as $post): ?>
                    <div class="menu-item">
                        <img src="/CAMPUS_BITES_WEB_APP/<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p class="desc"><?php echo htmlspecialchars($post['description'] ? $post['description'] : 'No description provided.'); ?></p>
                        <p class="price">$<?php echo number_format($post['price'] ?? 15.99, 2); ?></p>
                        <form method="POST">
                            <input type="hidden" name="food_post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="order" class="btn-order">Order Now</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <section class="pricing">
        <h1 class="pricing-title">Pricing</h1>
        <div class="pricing-header">
            <h1>Flexible Plans <span>For Every Student</span></h1>
            <p>Choose the plan that fits your lifestyle—whether you're a light snacker or a daily foodie, CampusBite has flexible options made for your campus routine.</p>
        </div>
        <div class="pricing-plans">
            <div class="plan">
                <h4>Basic<br>749 birr</h4>
                <ul>
                    <li>1 week package</li>
                    <li>3 meals per day</li>
                    <li>Choose preferred menu</li>
                    <li>1 day detox Juice cleanse</li>
                    <li>Lifestyle advice</li>
                </ul>
                <button>Get Plan</button>
            </div>
            <div class="plan highlight">
                <h4>Starter<br>1540 birr</h4>
                <ul>
                    <li>2 week package</li>
                    <li>3 meals per day</li>
                    <li>Choose preferred menu</li>
                    <li>1 day detox Juice cleanse</li>
                    <li>Lifestyle advice</li>
                </ul>
                <button>Get Plan</button>
            </div>
            <div class="plan">
                <h4>Pro<br>3000 birr</h4>
                <ul>
                    <li>1 month package</li>
                    <li>3 meals per day</li>
                    <li>Choose preferred menu</li>
                    <li>1 day detox Juice cleanse</li>
                    <li>Lifestyle advice</li>
                </ul>
                <button>Get Plan</button>
            </div>
        </div>
    </section>
    <section class="newsletter">
        <div>
            <p>Subscribe to our newsletters and get 10% discount on your first week ration</p>
            <form>
                <input type="email" placeholder="Your email" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </section>
    <section class="extras">
        <h3>What Else We Can Offer You</h3>
        <div class="extras-items">
            <div>
                <img src="../images/Recipes/ella-olsson-kKLRvcjQNqM-unsplash.jpg" alt="Recipes">
                <p>Recipes</p>
            </div>
            <div>
                <img src="../images/Recipes/gebiya-putri-IzdLRdXcNT8-unsplash.jpg" alt="Home Workouts">
                <p>Home Workouts</p>
            </div>
            <div>
                <img src="../images/Recipes/s-o-c-i-a-l-c-u-t-hwy3W3qFjgM-unsplash.jpg" alt="Accessories">
                <p>Accessories</p>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-col">
                <h2 class="footer-logo">CampusBite</h2>
                <p>Your go-to meal partner on campus. Fast, affordable, and delicious Ethiopian food delivered to students.</p>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#menu">Menu</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contact Us</h3>
                <p>Email: campusbite@gmail.com</p>
                <p>Phone: +251 912 345 678</p>
                <p>Location: Adama, Ethiopia</p>
            </div>
            <div class="footer-col">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><img src="../icons/socials/facebook (1).png" alt="Facebook"></a>
                    <a href="#"><img src="../icons/socials/twitter.png" alt="Twitter"></a>
                    <a href="#"><img src="../icons/socials/instagram.png" alt="Instagram"></a>
                </div>
            </div>
        </div>
        <p class="footer-bottom">© 2025 CampusBite. All rights reserved.</p>
    </footer>
    <script>
        const hamburger = document.getElementById('hamburger');
        const navElements = document.getElementById('nav-elements');

        hamburger.addEventListener('click', () => {
            navElements.classList.toggle('active');
            hamburger.classList.toggle('fa-bars');
            hamburger.classList.toggle('fa-times');
        });
    </script>
</body>
</html>