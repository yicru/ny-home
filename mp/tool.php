<?php
/**
 * MP Tool バックエンド
 * ブラウザUIからのAjax呼び出しを処理してCLI版mp.phpを実行
 */

// エラー出力を抑制（JSON出力を守るため）
error_reporting(0);
ini_set('display_errors', 0);

// メモリとタイムアウト設定
ini_set('memory_limit', '256M');
set_time_limit(30);

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

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

// デバッグログ
error_log("Received input: " . print_r($input, true));

// アクションの判定
if (isset($input['action'])) {
    $action = $input['action'];
    error_log("Action detected: " . $action);

    // SCSS関連のアクション
    if (str_starts_with($action, 'scss_')) {
        error_log("Processing SCSS action");

        // SCSS Module関連のアクション
        if (str_starts_with($action, 'scss_module_')) {
            error_log("Processing SCSS Module action");
            echo json_encode(handleScssModuleAction($input));
            exit;
        }

        // 通常のSCSS関連のアクション
        echo json_encode(handleScssAction($input));
        exit;
    }

    // ファイル読み取り処理
    if ($action === 'read_file') {
        handleFileRead($input);
        exit;
    }
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
    'scss list',
    'scss backup',
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

/**
 * SCSS関連のアクション処理
 */
function handleScssAction($input) {
    $action = $input['action'];

    try {
        // Web APIからの呼び出しであることを示すフラグを設定
        define('MP_WEB_API', true);

        require_once __DIR__ . '/scss/color.php';
        $manager = new SCSSColorManager();

        switch ($action) {
            case 'scss_list_colors':
                return handleScssListColors($manager);

            case 'scss_search_color':
                return handleScssSearchColor($manager, $input);

            case 'scss_add_color':
                return handleScssAddColor($manager, $input);

            case 'scss_update_color':
                return handleScssUpdateColor($manager, $input);

            case 'scss_delete_color':
                return handleScssDeleteColor($manager, $input);

            case 'scss_backup':
                return handleScssBackup($manager);

            case 'scss_list_backups':
                return handleScssListBackups();

            case 'scss_restore':
                return handleScssRestore($manager, $input);

            default:
                return ['success' => false, 'error' => 'Unknown SCSS action'];
        }

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * 色一覧取得
 */
function handleScssListColors($manager) {
    $colorsByCategory = $manager->getColorsByCategory();
    $categories = $manager->getCategories();

    $result = [];
    foreach ($categories as $key => $label) {
        $result[$key] = [
            'label' => $label,
            'colors' => array_values($colorsByCategory[$key] ?? [])
        ];
    }

    return [
        'success' => true,
        'data' => $result,
        'total' => count($manager->getAllColors())
    ];
}

/**
 * 色検索
 */
function handleScssSearchColor($manager, $input) {
    // 詳細ログ用
    $debugLog = [];

    try {
        $debugLog[] = "Start search function";

        if (!isset($input['color_value'])) {
            return ['success' => false, 'error' => 'Color value not specified'];
        }

        $colorValue = $input['color_value'];
        $debugLog[] = "Color value: " . $colorValue;

        // 色値の基本検証を追加
        if (empty(trim($colorValue))) {
            return ['success' => false, 'error' => 'Empty color value'];
        }

        // 正規化のテスト
        $debugLog[] = "Testing color normalization";
        $normalizedColor = $manager->normalizeColor($colorValue);
        $debugLog[] = "Normalized color: " . $normalizedColor;

        // 既存色を検索
        $debugLog[] = "Starting existing color search";
        $existing = $manager->searchByColor($colorValue);
        $debugLog[] = "Existing colors found: " . count($existing);

        // 段階的に機能を有効化
        $similar = []; // 一旦類似色検索をスキップ
        $suggestions = ['test']; // 一旦色名生成をスキップ

        // カテゴリー別の既存チェック
        $debugLog[] = "Starting category status check";
        $categories = $manager->getCategories();
        $categoryStatus = [];

        foreach ($categories as $key => $label) {
            $found = array_filter($existing, fn($color) => $color['category'] === $key);
            $categoryStatus[$key] = [
                'label' => $label,
                'exists' => !empty($found),
                'colors' => array_values($found)
            ];
        }
        $debugLog[] = "Category status check completed";

        return [
            'success' => true,
            'data' => [
                'color_value' => $colorValue,
                'existing' => $existing,
                'similar' => $similar,
                'suggestions' => $suggestions,
                'category_status' => $categoryStatus,
                'debug_log' => $debugLog // デバッグログを含める
            ]
        ];

    } catch (Exception $e) {
        return [
            'success' => false, 
            'error' => $e->getMessage(),
            'debug_log' => $debugLog,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    } catch (Error $e) {
        return [
            'success' => false, 
            'error' => 'Fatal error: ' . $e->getMessage(),
            'debug_log' => $debugLog,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
}

/**
 * 色追加
 */
function handleScssAddColor($manager, $input) {
    $required = ['category', 'color_value'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            return ['success' => false, 'error' => "Field '{$field}' is required"];
        }
    }

    $category = $input['category'];
    $colorValue = $input['color_value'];
    $customName = $input['custom_name'] ?? '';
    $comment = $input['comment'] ?? '';

    // 色名の決定
    if (!empty($customName)) {
        $colorName = $customName;
    } else {
        $suggestions = $manager->generateColorName($colorValue);
        $colorName = $suggestions[0] ?? 'unknown';
    }

    // カテゴリープレフィックスを追加
    $prefixes = [
        'background' => 'bg-',
        'border' => 'border-',
        'font' => 'font-',
        'hover' => 'img-hover-',
        'shadow' => 'shadow-'
    ];

    $fullName = $prefixes[$category] . $colorName;

    // 重複チェック
    if ($manager->checkDuplicate($category, $fullName)) {
        $fullName = $manager->generateNumberedName($category, $fullName);
    }

    // 追加実行
    if ($manager->addColor($category, $fullName, $colorValue, $comment)) {
        return [
            'success' => true,
            'data' => [
                'variable_name' => "--color-{$fullName}",
                'full_name' => $fullName,
                'color_value' => $colorValue,
                'category' => $category,
                'comment' => $comment
            ]
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to add color'];
    }
}

/**
 * 色更新
 */
function handleScssUpdateColor($manager, $input) {
    $required = ['old_name', 'color_value'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            return ['success' => false, 'error' => "Field '{$field}' is required"];
        }
    }

    $oldName = $input['old_name'];
    $newName = $input['new_name'] ?? $oldName;
    $colorValue = $input['color_value'];
    $comment = $input['comment'] ?? '';

    if ($manager->updateColor($oldName, $newName, $colorValue, $comment)) {
        return [
            'success' => true,
            'data' => [
                'variable_name' => "--color-{$newName}",
                'old_name' => $oldName,
                'new_name' => $newName,
                'color_value' => $colorValue,
                'comment' => $comment
            ]
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to update color'];
    }
}

/**
 * 色削除
 */
function handleScssDeleteColor($manager, $input) {
    if (!isset($input['name'])) {
        return ['success' => false, 'error' => 'Color name not specified'];
    }

    $name = $input['name'];

    if ($manager->deleteColor($name)) {
        return [
            'success' => true,
            'data' => [
                'deleted_name' => $name
            ]
        ];
    } else {
        return ['success' => false, 'error' => 'Failed to delete color'];
    }
}

/**
 * バックアップ作成
 */
function handleScssBackup($manager) {
    $backupFile = $manager->createBackup();

    return [
        'success' => true,
        'data' => [
            'backup_file' => basename($backupFile),
            'full_path' => $backupFile
        ]
    ];
}

/**
 * バックアップ一覧取得
 */
function handleScssListBackups() {
    $backupDir = __DIR__ . '/backup/color/';

    if (!is_dir($backupDir)) {
        return [
            'success' => true,
            'data' => []
        ];
    }

    $files = glob($backupDir . '*_color.scss');

    if (empty($files)) {
        return [
            'success' => true,
            'data' => []
        ];
    }

    // ファイル名でソート（新しい順）
    usort($files, function($a, $b) {
        return filemtime($b) <=> filemtime($a);
    });

    $backups = [];
    foreach ($files as $file) {
        $backups[] = [
            'filename' => basename($file),
            'timestamp' => date('Y-m-d H:i:s', filemtime($file)),
            'size' => round(filesize($file) / 1024, 1) . ' KB',
            'full_path' => $file
        ];
    }

    return [
        'success' => true,
        'data' => $backups
    ];
}

/**
 * バックアップ復元
 */
function handleScssRestore($manager, $input) {
    if (!isset($input['filename'])) {
        return ['success' => false, 'error' => 'Backup filename not specified'];
    }

    $filename = $input['filename'];
    $backupDir = __DIR__ . '/backup/color/';
    $backupFile = $backupDir . $filename;

    if (!file_exists($backupFile)) {
        return ['success' => false, 'error' => 'Backup file not found'];
    }

    $scssFile = __DIR__ . '/../scss/global/_color.scss';

    try {
        // 現在のファイルをバックアップしてから復元
        $currentBackup = $manager->createBackup();

        if (copy($backupFile, $scssFile)) {
            return [
                'success' => true,
                'data' => [
                    'restored_from' => $filename,
                    'current_backup' => basename($currentBackup)
                ]
            ];
        } else {
            return ['success' => false, 'error' => 'Failed to restore from backup'];
        }

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// セキュリティ: 許可されたSCSSコマンドのチェック
function validateScssCommand($command) {
    $allowedCommands = [
        'scss list',
        'scss backup',
        'scss restore'
    ];

    // color コマンドの場合は特別な検証
    if (str_starts_with($command, 'scss color ')) {
        $colorValue = substr($command, 11); // 'scss color ' の後

        // 色値の基本的な検証
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $colorValue)) {
            return true;
        }

        // その他の有効な色値 (unset, transparent等)
        if (in_array(strtolower($colorValue), ['unset', 'transparent', 'inherit', 'initial'])) {
            return true;
        }

        return false;
    }

    return in_array($command, $allowedCommands);
}


/**
 * SCSS Module関連のアクション処理
 */
function handleScssModuleAction($input) {
    $action = $input['action'];

    // デバッグログを追加
    error_log("SCSS Module Action received: " . $action);
    error_log("Input data: " . print_r($input, true));

    try {
        // Web APIからの呼び出しであることを示すフラグを設定（既に定義されている場合はスキップ）
        if (!defined('MP_WEB_API')) {
            define('MP_WEB_API', true);
        }

        // ファイル存在チェック
        $moduleFile = __DIR__ . '/scss/module.php';
        if (!file_exists($moduleFile)) {
            error_log("Module file not found: " . $moduleFile);
            return ['success' => false, 'error' => 'module.php file not found at: ' . $moduleFile];
        }

        require_once $moduleFile;
        
        // クラス存在チェック
        if (!class_exists('SCSSModuleGenerator')) {
            error_log("SCSSModuleGenerator class not found");
            return ['success' => false, 'error' => 'SCSSModuleGenerator class not found'];
        }

        $generator = new SCSSModuleGenerator();

        switch ($action) {
            case 'scss_module_stats':
                error_log("Executing scss_module_stats");
                return handleScssModuleStats($generator);

            case 'scss_module_init':
                error_log("Executing scss_module_init");
                return handleScssModuleInit($generator);

            case 'scss_module_update':
                error_log("Executing scss_module_update");
                return handleScssModuleUpdate($generator);

            case 'scss_module_add':
                error_log("Executing scss_module_add");
                return handleScssModuleAdd($generator, $input);

            default:
                error_log("Unknown SCSS Module action: " . $action);
                return ['success' => false, 'error' => 'Unknown SCSS Module action: ' . $action];
        }

    } catch (Exception $e) {
        error_log("SCSS Module Exception: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return ['success' => false, 'error' => 'Exception: ' . $e->getMessage()];
    }
}

/**
 * モジュール統計取得
 */
function handleScssModuleStats($generator) {
    $stats = $generator->getModuleStats();

    return [
        'success' => true,
        'data' => $stats
    ];
}

/**
 * 全モジュール初期化
 */
function handleScssModuleInit($generator) {
    $results = $generator->generateAll(false);

    return [
        'success' => true,
        'data' => [
            'total' => $results['total'],
            'created' => $results['created'],
            'skipped' => $results['skipped'],
            'errors' => $results['errors'],
            'details' => $results['details']
        ]
    ];
}

/**
 * 全モジュール更新
 */
function handleScssModuleUpdate($generator) {
    $results = $generator->generateAll(true);

    return [
        'success' => true,
        'data' => [
            'total' => $results['total'],
            'created' => $results['created'],
            'skipped' => $results['skipped'],
            'errors' => $results['errors'],
            'details' => $results['details']
        ]
    ];
}

/**
 * 単一モジュール追加
 */
function handleScssModuleAdd($generator, $input) {
    if (!isset($input['module_path'])) {
        return ['success' => false, 'error' => 'Module path not specified'];
    }

    $modulePath = $input['module_path'];

    if (strpos($modulePath, '/') === false) {
        return ['success' => false, 'error' => 'Invalid format. Use module_name/file_name'];
    }

    list($moduleName, $fileName) = explode('/', $modulePath, 2);

    try {
        $result = $generator->generateSingle($moduleName, $fileName, false);

        if ($result['created']) {
            $message = "✓ 成功: {$moduleName}/_" . $fileName . ".scss を作成しました";
            $status = 'created';
        } elseif ($result['skipped']) {
            $message = "- スキップ: 既存 - {$moduleName}/_" . $fileName . ".scss";
            $status = 'skipped';
        } else {
            $message = "処理完了";
            $status = 'completed';
        }

        return [
            'success' => true,
            'data' => [
                'module_name' => $moduleName,
                'file_name' => $fileName,
                'status' => $status,
                'message' => $message,
                'scss_file' => "{$moduleName}/_" . $fileName . ".scss"
            ]
        ];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

?>