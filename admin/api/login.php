<?php
require_once '../../config/database.php';
header('Content-Type: application/json');

define('LOCK_FILE', __DIR__ . '/../../session_locks.txt');

// Fungsi baca lock (BISA BACA FORMAT RAPI)
function isIPLocked($ip) {
    if (!file_exists(LOCK_FILE)) return false;
    
    $content = file_get_contents(LOCK_FILE);
    
    // Cek format [LOCK]
    $pattern = '/\[LOCK\]\s+IP:\s*([0-9.]+)\s+Lock Time:.*?Expires:\s*([0-9-: ]+)/s';
    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            if (trim($match[1]) === $ip) {
                $expireTime = strtotime($match[2]);
                if (time() < $expireTime) {
                    return $expireTime;
                } else {
                    // Hapus yang expired (opsional)
                }
            }
        }
    }
    
    return false;
}

// Fungsi simpan lock
function lockIP($ip, $minutes = 15) {
    $expires = time() + ($minutes * 60);
    
    $newLock = "[LOCK]\n";
    $newLock .= "IP: $ip\n";
    $newLock .= "Lock Time: " . date('Y-m-d H:i:s') . "\n";
    $newLock .= "Expires: " . date('Y-m-d H:i:s', $expires) . "\n";
    $newLock .= "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
    $newLock .= "---\n\n";
    
    $oldContent = file_exists(LOCK_FILE) ? file_get_contents(LOCK_FILE) : '';
    file_put_contents(LOCK_FILE, $newLock . $oldContent);
}

// ==================== CEK LOCK ====================
$ip = $_SERVER['REMOTE_ADDR'];
$locked = isIPLocked($ip);

if ($locked !== false) {
    $remaining = ceil(($locked - time()) / 60);
    echo json_encode([
        'success' => false,
        'error' => "⚠️ Terlalu banyak percobaan. Coba lagi $remaining menit lagi."
    ]);
    exit;
}

// ==================== PROSES LOGIN ====================
$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// Validasi CSRF (kalo ada)
if (isset($input['csrf_token']) && !verifyCSRFToken($input['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD)) {
    // Login sukses
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    $_SESSION['login_time'] = time();
    
    // Hapus percobaan gagal
    unset($_SESSION['login_attempts']);
    
    echo json_encode(['success' => true]);
    
} else {
    // Login gagal
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 1;
    } else {
        $_SESSION['login_attempts']++;
    }
    
    $attempts = $_SESSION['login_attempts'];
    $maxAttempts = 5;
    
    if ($attempts >= $maxAttempts) {
        // Lock IP
        lockIP($ip);
        
        // Reset attempts
        unset($_SESSION['login_attempts']);
        
        echo json_encode([
            'success' => false,
            'error' => "⛔ Terlalu banyak percobaan. Coba lagi 15 menit lagi."
        ]);
    } else {
        $remaining = $maxAttempts - $attempts;
        echo json_encode([
            'success' => false,
            'error' => "❌ Username atau password salah. Sisa percobaan: $remaining"
        ]);
    }
}
?>