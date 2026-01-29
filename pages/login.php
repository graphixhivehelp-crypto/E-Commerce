<?php
$pageTitle = 'Login';
require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/classes.php';

// Check if already logged in
if (isLoggedIn()) {
    header("Location: " . SITE_URL . "/pages/account.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill all fields';
    } else {
        $conn = getConnection();
        $auth = new Auth($conn);
        $result = $auth->login($email, $password);

        if ($result['success']) {
            $conn->close();
            header("Location: " . (isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : SITE_URL));
            exit();
        } else {
            $error = $result['error'];
        }
        $conn->close();
    }
}
?>

    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center my-5">
                <div class="col-md-5">
                    <div class="card border-0 shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">
                                <i class="fas fa-sign-in-alt me-2" style="color: var(--primary-color);"></i>
                                Login
                            </h2>

                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <form method="POST" data-validate="true" id="loginForm">
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                                    <label class="form-check-label" for="rememberMe">
                                        Remember me
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </form>

                            <hr>

                            <p class="text-center mb-0">
                                Don't have an account? 
                                <a href="<?php echo SITE_URL; ?>/pages/register.php" class="text-primary text-decoration-none fw-bold">
                                    Sign Up
                                </a>
                            </p>

                            <p class="text-center mt-3 mb-0">
                                <a href="<?php echo SITE_URL; ?>/pages/forgot-password.php" class="text-muted text-decoration-none small">
                                    Forgot Password?
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
