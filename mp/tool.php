<?php
/**
 * MP Tool バックエンド
 * ブラウザUIからのAjax呼び出しを処理してCLI版mp.phpを実行
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// POSTメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// JSON入力を取得
$input = json_decode(file_get_contents('php://input'), true);

// アクションの判定
if (isset($input['action']) && $input['action'] === 'read_file') {
    handleFileRead($input);
    exit;
}

if (!isset($input['command'])) {
    echo json_encode(['success' => false, 'error' => 'Command not specified']);
    exit;
}

/**
 * ファイル読み取り処理
 */
function handleFileRead($input) {
    if (!isset($input['filename'])) {
        echo json_encode(['success' => false, 'error' => 'Filename not specified']);
        return;
    }

    $filename = $input['filename'];

    // 許可されたファイルのみ読み取り
    $allowedFiles = ['modules.txt', 'export-config.json'];
    if (!in_array($filename, $allowedFiles)) {
        echo json_encode(['success' => false, 'error' => 'File not allowed']);
        return;
    }

    $filePath = __DIR__ . '/config/' . $filename;

    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        echo json_encode([
            'success' => true,
            'content' => $content,
            'filename' => $filename
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'content' => null,
            'filename' => $filename
        ]);
    }
}

$command = $input['command'];

// セキュリティチェック: 許可されたコマンドのみ実行
$allowedCommands = [
    'init --sample',
    'init',
    'export --init',
    'export',
    'export -d dist/',
    'version',
    'help'
];

// addコマンドの場合は特別な検証
if (str_starts_with($command, 'add ')) {
    $modulePath = substr($command, 4);

    // モジュールパスの検証
    if (!preg_match('/^[a-zA-Z0-9_\-]+\/[a-zA-Z0-9_\-]+$/', $modulePath)) {
        echo json_encode(['success' => false, 'error' => 'Invalid module path format']);
        exit;
    }

    // 危険な文字をチェック
    if (strpos($modulePath, '..') !== false ||
        strpos($modulePath, '/') === 0 ||
        strpos($modulePath, '\\') !== false) {
        echo json_encode(['success' => false, 'error' => 'Invalid characters in module path']);
        exit;
    }
} elseif (!in_array($command, $allowedCommands)) {
    echo json_encode(['success' => false, 'error' => 'Command not allowed']);
    exit;
}

try {
    // mp.phpの存在チェック
    $mpScript = __DIR__ . '/mp.php';
    if (!file_exists($mpScript)) {
        throw new Exception('mp.php not found');
    }

    // コマンド実行
    $fullCommand = "php " . escapeshellarg($mpScript) . " " . $command . " 2>&1";

    // 出力バッファリングを使用して実行
    ob_start();
    $output = [];
    $returnCode = 0;

    exec($fullCommand, $output, $returnCode);

    $outputString = implode("\n", $output);

    // 実行結果の判定
    if ($returnCode === 0) {
        echo json_encode([
            'success' => true,
            'output' => $outputString,
            'command' => $command
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $outputString,
            'command' => $command
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>