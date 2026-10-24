<?php
// API wspólnej listy zakupów. GET = pobierz listę, POST (JSON) = wykonaj operację.
// Operacje: add, toggle, delete, clear_done. Zawsze zwraca pełną listę.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$CONFIG = require __DIR__ . '/config.php';
$key = $_SERVER['HTTP_X_LIST_KEY'] ?? '';
if ($CONFIG['LIST_KEY'] === '' || !hash_equals($CONFIG['LIST_KEY'], $key)) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$dir = __DIR__ . '/data';
if (!is_dir($dir)) {
    mkdir($dir, 0755);
    file_put_contents($dir . '/.htaccess', "Require all denied\n");
}
$fh = fopen($dir . '/list.json', 'c+');
flock($fh, LOCK_EX);
$state = json_decode(stream_get_contents($fh), true);
if (!is_array($state) || !isset($state['items'])) {
    $state = ['rev' => 0, 'items' => []];
}

$clean = fn($s, $max) => mb_substr(trim(strip_tags((string)$s)), 0, $max);
$changed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    $ops = isset($in['ops']) && is_array($in['ops']) ? $in['ops'] : [$in];
    foreach (array_slice($ops, 0, 50) as $op) {
        $id = $clean($op['id'] ?? '', 40);
        switch ($op['op'] ?? '') {
            case 'add':
                $name = $clean($op['name'] ?? '', 120);
                if ($id === '' || $name === '') break;
                foreach ($state['items'] as $it) if ($it['id'] === $id) continue 2;
                $state['items'][] = [
                    'id' => $id, 'name' => $name,
                    'qty' => $clean($op['qty'] ?? '', 12),
                    'unit' => $clean($op['unit'] ?? '', 12),
                    'done' => false, 'added' => time(),
                ];
                $changed = true;
                break;
            case 'toggle':
                foreach ($state['items'] as &$it) {
                    if ($it['id'] === $id) { $it['done'] = !empty($op['done']); $changed = true; }
                }
                unset($it);
                break;
            case 'delete':
                $n = count($state['items']);
                $state['items'] = array_values(array_filter($state['items'], fn($it) => $it['id'] !== $id));
                $changed = $changed || $n !== count($state['items']);
                break;
            case 'clear_done':
                $state['items'] = array_values(array_filter($state['items'], fn($it) => empty($it['done'])));
                $changed = true;
                break;
        }
    }
    if ($changed) {
        $state['rev']++;
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($state, JSON_UNESCAPED_UNICODE));
    }
}

flock($fh, LOCK_UN);
fclose($fh);
echo json_encode($state, JSON_UNESCAPED_UNICODE);
