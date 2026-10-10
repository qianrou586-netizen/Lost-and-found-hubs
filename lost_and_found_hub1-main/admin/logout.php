<?php
// Admin Logout - session အားလုံးကို ရှင်းလင်းပေးသည်
require_once __DIR__ . '/../includes/bootstrap.php';

unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_email'], $_SESSION['pending_admin_login']);
session_destroy();

redirect('login.php');