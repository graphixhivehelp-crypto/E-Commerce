<?php
$pageTitle = 'Register';
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
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $conn = getConnection();
        $auth = new Auth($conn);
        $result = $auth->register($name, $email, $phone, $password);

        if ($result['success']) {
            $success = $result['message'];
            $_SESSION['registration_email'] = $email;
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
                <div class="col-md-6">
                    <div class="card border-0 shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">
                                <i class="fas fa-user-plus me-2" style="color: var(--primary-color);"></i>
                                Create Account
                            </h2>

                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php endif; ?>

                            <form method="POST" data-validate="true" id="registerForm">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           placeholder="10-digit number" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" 
                                           placeholder="Minimum 6 characters" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-primary">Terms & Conditions</a>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </form>

                            <hr>

                            <p class="text-center mb-0">
                                Already have an account? 
                                <a href="<?php echo SITE_URL; ?>/pages/login.php" class="text-primary text-decoration-none fw-bold">
                                    Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
