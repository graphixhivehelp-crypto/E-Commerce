<?php
$pageTitle = 'Admin Dashboard';
require_once dirname(__DIR__) . '/../includes/header.php';
require_once dirname(__DIR__) . '/../config/constants.php';
require_once dirname(__DIR__) . '/../includes/classes.php';

// Check admin access
requireAdmin();

$conn = getConnection();

// Get statistics
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products WHERE status = 'active'")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'completed'")->fetch_assoc()['revenue'] ?? 0;

$placedOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'placed'")->fetch_assoc()['count'];
$confirmedOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'confirmed'")->fetch_assoc()['count'];
$shippedOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'shipped'")->fetch_assoc()['count'];
$deliveredOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'delivered'")->fetch_assoc()['count'];

$conn->close();
?>

    <div class="container-fluid py-4">
        <h2 class="mb-4">
            <i class="fas fa-chart-line me-2" style="color: var(--primary-color);"></i>
            Dashboard
        </h2>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-0">Total Users</h6>
                                <h3 class="mb-0"><?php echo $totalUsers; ?></h3>
                            </div>
                            <i class="fas fa-users fa-3x" style="color: var(--primary-color); opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-0">Total Products</h6>
                                <h3 class="mb-0"><?php echo $totalProducts; ?></h3>
                            </div>
                            <i class="fas fa-box fa-3x" style="color: var(--primary-color); opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-0">Total Orders</h6>
                                <h3 class="mb-0"><?php echo $totalOrders; ?></h3>
                            </div>
                            <i class="fas fa-shopping-cart fa-3x" style="color: var(--primary-color); opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-0">Total Revenue</h6>
                                <h3 class="mb-0"><?php echo formatCurrency($totalRevenue); ?></h3>
                            </div>
                            <i class="fas fa-rupee-sign fa-3x" style="color: var(--primary-color); opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Order Status Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <h4 class="text-warning"><?php echo $placedOrders; ?></h4>
                                    <p class="text-muted small">Placed</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <h4 class="text-info"><?php echo $confirmedOrders; ?></h4>
                                    <p class="text-muted small">Confirmed</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <h4 class="text-primary"><?php echo $shippedOrders; ?></h4>
                                    <p class="text-muted small">Shipped</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <h4 class="text-success"><?php echo $deliveredOrders; ?></h4>
                                    <p class="text-muted small">Delivered</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo SITE_URL; ?>/admin/pages/products.php" class="btn btn-outline-primary">
                                <i class="fas fa-box me-2"></i> Manage Products
                            </a>
                            <a href="<?php echo SITE_URL; ?>/admin/pages/orders.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-cart me-2"></i> View Orders
                            </a>
                            <a href="<?php echo SITE_URL; ?>/admin/pages/users.php" class="btn btn-outline-primary">
                                <i class="fas fa-users me-2"></i> Manage Users
                            </a>
                            <a href="<?php echo SITE_URL; ?>/admin/pages/categories.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i> Manage Categories
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once dirname(__DIR__) . '/../includes/footer.php'; ?>
