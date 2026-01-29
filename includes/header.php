<?php
// Start session and include necessary files
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/classes.php';

SessionManager::startSession();
$currentUser = getCurrentUser();
$cartCount = getCartCount();
$siteName = getSetting('site_name', SITE_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo getSetting('site_description', SITE_DESCRIPTION); ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . $siteName : $siteName; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    
    <style>
        :root {
            --primary-color: <?php echo getSetting('primary_color', PRIMARY_COLOR); ?>;
            --secondary-color: <?php echo getSetting('secondary_color', SECONDARY_COLOR); ?>;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container-fluid px-4">
                <!-- Logo -->
                <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>">
                    <i class="fas fa-shopping-bag me-2" style="color: var(--primary-color);"></i>
                    <?php echo $siteName; ?>
                </a>
                
                <!-- Search Bar -->
                <div class="search-container mx-auto d-none d-md-block">
                    <form method="GET" action="<?php echo SITE_URL; ?>/pages/products.php" class="search-form">
                        <input type="text" class="form-control search-input" name="search" placeholder="Search products...">
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Right Side Menu -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/categories.php">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/about.php">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/contact.php">Contact</a>
                        </li>
                        
                        <!-- User Menu -->
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <?php echo htmlspecialchars($currentUser['name']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/account.php">My Account</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/orders.php">My Orders</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/wishlist.php">Wishlist</a></li>
                                    <?php if (isAdmin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/dashboard.php">Admin Panel</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/api/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/register.php">Sign Up</a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Cart Icon -->
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="<?php echo SITE_URL; ?>/pages/cart.php">
                                <i class="fas fa-shopping-cart fs-5"></i>
                                <?php if ($cartCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $cartCount; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Search Bar for Mobile -->
    <div class="mobile-search d-md-none bg-light py-2">
        <div class="container-fluid px-2">
            <form method="GET" action="<?php echo SITE_URL; ?>/pages/products.php" class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search products...">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
