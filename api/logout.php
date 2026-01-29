<?php
/**
 * Logout API
 */
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/classes.php';

SessionManager::startSession();
SessionManager::destroy();

header("Location: " . SITE_URL);
exit();
?>
