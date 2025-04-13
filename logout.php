<?php
require_once 'config/config.php';

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman utama
redirect('');
