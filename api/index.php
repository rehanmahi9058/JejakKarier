<?php

// Vercel hanya mengizinkan tulis di /tmp, jadi folder storage dibuat di sana
foreach (['framework/views', 'framework/cache', 'framework/sessions', 'logs'] as $dir) {
    @mkdir('/tmp/storage/' . $dir, 0777, true);
}

require __DIR__ . '/../public/index.php';