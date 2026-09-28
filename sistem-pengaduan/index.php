<?php
require_once __DIR__ . '/config/app.php';

if (current_role() === 'admin') {
    redirect('admin/dashboard.php');
}

if (current_role() === 'user') {
    redirect('user/dashboard.php');
}

redirect('auth/login.php');