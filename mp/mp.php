<?php
/**
 * MP (Module Pallet) 統合ツール v1.1.0
 * 
 * PHPプロジェクトの効率的な開発をサポートするツール群
 * - export: PHPファイルをHTMLに変換（GETパラメータ対応）
 * - init: modules.txtからpartsディレクトリを初期化
 * - add: 単一モジュール追加
 * 
 * v1.1.0 変更点:
 * - 出力バッファリング処理の大幅改善
 * - エラーハンドリングの強化
 * - コード構造の最適化
 */

// CLI実行チェック
if (php_sapi_name() !== 'cli') {
    die("このスクリプトはコマンドラインでのみ実行可能です。\n");
}

// 基本設定
define('MP_VERSION', '1.1.0');
define('MP_CONFIG_DIR', __DIR__ . '/config/');
define('MP_PARTS_DIR', __DIR__ . '/../parts/');
define('MP_PROJECT_ROOT', __DIR__ . '/../');
define('MP_EXPORT_CONFIG', 'export-config.json');
define('MP_MODULES_FILE', 'modules.txt');
define('MP_BASE_TEMPLATE', 'base.php');

/**
 * 共通ユーティリティクラス
 */
class MPUtils
{
    /**
     * 確実な出力バッファリングでPHPファイルを実行
     */
    public static function capturePhpOutput(string $phpFile, array $getParams = []): string
    {
        // 現在のディレクトリとバッファレベルを保存
        $originalDir = getcwd();
        $originalBufferLevel = ob_get_level();

        try {
            // ファイル存在チェック
            if (!file_exists($phpFile)) {
                throw new Exception("PHPファイルが見つかりません: {$phpFile}");
            }

            // プロジェクトルートに移動
            $projectRoot = dirname($phpFile);
            $includeFile = basename($phpFile);
            chdir($projectRoot);

            // 既存の出力バッファを全てクリア
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // GETパラメータを設定
            $originalGet = $_GET;
            $_GET = array_merge($_GET, $getParams);

            // エラー表示を一時的に抑制
            $originalErrorReporting = error_reporting(E_ERROR | E_PARSE);

            // 新しい出力バッファを開始
            ob_start();

            // PHPファイルを実行
            include $includeFile;

            // 出力内容を取得
            $output = ob_get_contents();
            ob_end_clean();

            return $output;

        } catch (Exception $e) {
            // エラー時のクリーンアップ
            while (ob_get_level() > $originalBufferLevel) {
                ob_end_clean();
            }
            throw $e;

        } finally {
            // 状態を確実に復元
            chdir($originalDir);
            $_GET = $originalGet ?? [];
            if (isset($originalErrorReporting)) {
                error_reporting($originalErrorReporting);
            }
        }
    }

    /**
     * ディレクトリを再帰的に作成
     */
    public static function createDirectory(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }
        return mkdir($dir, 0755, true);
    }

    /**
     * JSONファイルを安全に読み込み
     */
    public static function loadJsonFile(string $file): array
    {
        if (!file_exists($file)) {
            throw new Exception("ファイルが見つかりません: {$file}");
        }

        $content = file_get_contents($file);
        if ($content === false) {
            throw new Exception("ファイルの読み込みに失敗: {$file}");
        }

        $data = json_decode($content, true);
        if ($data === null) {
            throw new Exception("JSONの解析に失敗: {$file}");
        }

        return $data;
    }
}

/**
 * Export機能クラス
 */
class MPExport
{
    private string $configFile;
    private string $outputDir;

    public function __construct(string $configFile = null, string $outputDir = null)
    {
        $this->configFile = $configFile ?? MP_CONFIG_DIR . MP_EXPORT_CONFIG;
        $this->outputDir = $outputDir ?? MP_PROJECT_ROOT;
    }

    /**
     * ヘルプを表示
     */
    public function showHelp(): void
    {
        echo "MP Export - HTML変換ツール v" . MP_VERSION . "\n";
        echo "使用方法:\n";
        echo "  php mp/mp.php export [options]\n";
        echo "\n";
        echo "オプション:\n";
        echo "  -h, --help           このヘルプを表示\n";
        echo "  -c, --config FILE    設定ファイルを指定\n";
        echo "  -d, --dir DIR        出力ディレクトリを指定\n";
        echo "  --init               サンプル設定ファイルを生成\n";
        echo "\n";
    }

    /**
     * サンプル設定ファイルを生成
     */
    public function generateConfig(): void
    {
        $sampleConfig = [
            "exports" => [
                [
                    "input" => "index.php",
                    "output" => "index.html",
                    "params" => []
                ],
                [
                    "input" => "index.php",
                    "output" => "post-15.html",
                    "params" => ["p" => "15"]
                ],
                [
                    "input" => "index.php",
                    "output" => "category-news.html",
                    "params" => ["cat" => "news"]
                ]
            ]
        ];

        MPUtils::createDirectory(dirname($this->configFile));

        $json = json_encode($sampleConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->configFile, $json);

        echo "サンプル設定ファイルを生成: {$this->configFile}\n";
        echo "設定ファイルを編集してから export を実行してください。\n";
    }

    /**
     * HTML変換を実行
     */
    public function run(): int
    {
        try {
            $config = MPUtils::loadJsonFile($this->configFile);

            if (!isset($config['exports']) || !is_array($config['exports'])) {
                throw new Exception("設定ファイルに'exports'配列が見つかりません");
            }

            $exports = $config['exports'];
            $totalCount = count($exports);
            $successCount = 0;

            echo "HTML変換開始... ({$totalCount} ファイル)\n";
            echo str_repeat("-", 60) . "\n";

            foreach ($exports as $index => $export) {
                $inputFile = MP_PROJECT_ROOT . $export['input'];
                $outputFile = rtrim($this->outputDir, '/') . '/' . ltrim($export['output'], '/');
                $params = $export['params'] ?? [];

                $paramStr = empty($params) ? '' : ' (' . http_build_query($params) . ')';
                echo sprintf("[%d/%d] %s%s → %s\n",
                    $index + 1, $totalCount, $export['input'], $paramStr, $export['output']);

                if ($this->convertSingleFile($inputFile, $outputFile, $params)) {
                    $successCount++;
                    echo "✓ 成功: {$export['output']}\n";
                } else {
                    echo "✗ 失敗: {$export['output']}\n";
                }
                echo "\n";
            }

            echo str_repeat("-", 60) . "\n";
            echo "変換完了: {$successCount}/{$totalCount} ファイル\n";

            return $successCount === $totalCount ? 0 : 1;

        } catch (Exception $e) {
            echo "エラー: {$e->getMessage()}\n";
            return 1;
        }
    }

    /**
     * 単一ファイルの変換
     */
    private function convertSingleFile(string $inputFile, string $outputFile, array $params): bool
    {
        try {
            // 出力ディレクトリの作成
            MPUtils::createDirectory(dirname($outputFile));

            // PHPファイルの出力をキャプチャ
            $htmlContent = MPUtils::capturePhpOutput($inputFile, $params);

            // 出力内容の検証
            if (empty(trim($htmlContent))) {
                throw new Exception("出力内容が空です");
            }

            // HTMLファイルとして書き出し
            $result = file_put_contents($outputFile, $htmlContent);
            if ($result === false) {
                throw new Exception("ファイルの書き出しに失敗");
            }

            return true;

        } catch (Exception $e) {
            echo "  エラー: {$e->getMessage()}\n";
            return false;
        }
    }
}

/**
 * Init機能クラス
 */
class MPInit
{
    private string $modulesFile;

    public function __construct(string $modulesFile = null)
    {
        $this->modulesFile = $modulesFile ?? MP_CONFIG_DIR . MP_MODULES_FILE;
    }

    /**
     * ヘルプを表示
     */
    public function showHelp(): void
    {
        echo "MP Init - プロジェクト初期化ツール v" . MP_VERSION . "\n";
        echo "使用方法:\n";
        echo "  php mp/mp.php init [options]\n";
        echo "\n";
        echo "オプション:\n";
        echo "  -h, --help           このヘルプを表示\n";
        echo "  -f, --file FILE      modules.txtファイルを指定\n";
        echo "  --sample             サンプルmodules.txtを生成\n";
        echo "\n";
    }

    /**
     * サンプルmodules.txtを生成
     */
    public function generateSample(): void
    {
        $sampleModules = "bl_header/header
bl_header/header2
bl_kv/kv
bl_kv/kv-single
bl_cta/top-kvBottom-cta
bl_cta/cta
bl_footer/footer";

        MPUtils::createDirectory(dirname($this->modulesFile));

        file_put_contents($this->modulesFile, $sampleModules);

        echo "サンプルmodules.txtを生成: {$this->modulesFile}\n";
        echo "ファイルを編集してから init を実行してください。\n";
    }

    /**
     * 初期化を実行
     */
    public function run(): int
    {
        try {
            if (!file_exists($this->modulesFile)) {
                throw new Exception("modules.txtが見つかりません: {$this->modulesFile}");
            }

            $modules = file_get_contents($this->modulesFile);
            $lines = array_filter(array_map('trim', explode("\n", $modules)));

            if (empty($lines)) {
                throw new Exception("modules.txtが空です");
            }

            // テンプレート内容を取得
            $baseTemplate = MP_PARTS_DIR . MP_BASE_TEMPLATE;
            $templateContent = file_exists($baseTemplate) ? file_get_contents($baseTemplate) : '';

            echo "プロジェクト初期化開始...\n";
            echo "parts ディレクトリ: " . MP_PARTS_DIR . "\n";
            echo str_repeat("-", 50) . "\n";

            $createdCount = 0;

            foreach ($lines as $line) {
                // コメント行をスキップ（# と // の両方対応）
                if (str_starts_with($line, '#') || str_starts_with($line, '//') || empty($line)) {
                    continue;
                }

                if (strpos($line, '/') === false) {
                    echo "スキップ: 無効な形式 - {$line}\n";
                    continue;
                }

                list($moduleName, $fileName) = explode('/', $line, 2);
                $moduleDir = MP_PARTS_DIR . $moduleName;
                $filePath = $moduleDir . '/' . $fileName . '.php';

                // ディレクトリ作成
                if (MPUtils::createDirectory($moduleDir)) {
                    // ファイル作成
                    if (!file_exists($filePath)) {
                        file_put_contents($filePath, $templateContent);
                        echo "✓ 作成: {$moduleName}/{$fileName}.php\n";
                        $createdCount++;
                    } else {
                        echo "- スキップ: 既存 - {$moduleName}/{$fileName}.php\n";
                    }
                } else {
                    echo "✗ エラー: ディレクトリ作成失敗 - {$moduleDir}\n";
                }
            }

            echo str_repeat("-", 50) . "\n";
            echo "初期化完了: {$createdCount}ファイル作成\n";

            return 0;

        } catch (Exception $e) {
            echo "エラー: {$e->getMessage()}\n";
            return 1;
        }
    }
}

/**
 * Add機能クラス
 */
class MPAdd
{
    /**
     * ヘルプを表示
     */
    public function showHelp(): void
    {
        echo "MP Add - モジュール追加ツール v" . MP_VERSION . "\n";
        echo "使用方法:\n";
        echo "  php mp/mp.php add <module_name>/<file_name>\n";
        echo "\n";
        echo "例:\n";
        echo "  php mp/mp.php add bl_news/news-list\n";
        echo "  php mp/mp.php add bl_gallery/gallery-single\n";
        echo "\n";
        echo "オプション:\n";
        echo "  -h, --help           このヘルプを表示\n";
        echo "\n";
    }

    /**
     * モジュール追加を実行
     */
    public function run(string $modulePath): int
    {
        try {
            if (strpos($modulePath, '/') === false) {
                throw new Exception("無効な形式です。module_name/file_name の形式で指定してください");
            }

            list($moduleName, $fileName) = explode('/', $modulePath, 2);

            $baseTemplate = MP_PARTS_DIR . MP_BASE_TEMPLATE;
            $moduleDir = MP_PARTS_DIR . $moduleName;
            $filePath = $moduleDir . '/' . $fileName . '.php';

            // テンプレート内容を取得
            $templateContent = file_exists($baseTemplate) ? file_get_contents($baseTemplate) : '';

            echo "モジュール追加: {$moduleName}/{$fileName}.php\n";

            // ディレクトリ作成
            if (!is_dir($moduleDir)) {
                if (MPUtils::createDirectory($moduleDir)) {
                    echo "✓ ディレクトリ作成: {$moduleName}/\n";
                } else {
                    throw new Exception("ディレクトリ作成失敗: {$moduleDir}");
                }
            }

            // ファイル作成
            if (file_exists($filePath)) {
                echo "⚠ 警告: ファイルが既に存在します - {$filePath}\n";
                return 1;
            }

            file_put_contents($filePath, $templateContent);
            echo "✓ 成功: {$moduleName}/{$fileName}.php を作成しました\n";

            return 0;

        } catch (Exception $e) {
            echo "エラー: {$e->getMessage()}\n";
            return 1;
        }
    }
}

/**
 * メインクラス
 */
class MP
{
    /**
     * メインヘルプを表示
     */
    public static function showMainHelp(): void
    {
        echo "MP (Module Pallet) 統合ツール v" . MP_VERSION . "\n";
        echo "使用方法:\n";
        echo "  php mp/mp.php <command> [options]\n";
        echo "\n";
        echo "利用可能なコマンド:\n";
        echo "  export    PHPファイルをHTMLに変換\n";
        echo "  init      modules.txtからpartsディレクトリを初期化\n";
        echo "  add       単一モジュールを追加\n";
        echo "  help      このヘルプを表示\n";
        echo "  version   バージョン情報を表示\n";
        echo "\n";
        echo "詳細なヘルプ:\n";
        echo "  php mp/mp.php <command> --help\n";
        echo "\n";
    }

    /**
     * バージョン情報を表示
     */
    public static function showVersion(): void
    {
        echo "MP (Module Pallet) v" . MP_VERSION . "\n";
        echo "PHPプロジェクト効率化ツール\n";
    }

    /**
     * コマンドライン引数を解析
     */
    public static function parseArgs(array $args, array $validOptions = []): array
    {
        $options = [];
        $positional = [];

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            if (str_starts_with($arg, '--')) {
                $option = substr($arg, 2);
                if (in_array($option, $validOptions)) {
                    if ($i + 1 < count($args) && !str_starts_with($args[$i + 1], '-')) {
                        $options[$option] = $args[++$i];
                    } else {
                        $options[$option] = true;
                    }
                }
            } elseif (str_starts_with($arg, '-') && strlen($arg) === 2) {
                $option = substr($arg, 1);
                $longOption = match($option) {
                    'h' => 'help',
                    'c' => 'config',
                    'd' => 'dir',
                    'f' => 'file',
                    'v' => 'version',
                    default => $option
                };

                if (in_array($longOption, $validOptions)) {
                    if ($i + 1 < count($args) && !str_starts_with($args[$i + 1], '-')) {
                        $options[$longOption] = $args[++$i];
                    } else {
                        $options[$longOption] = true;
                    }
                }
            } else {
                $positional[] = $arg;
            }
        }

        return ['options' => $options, 'positional' => $positional];
    }
}

/**
 * SCSSヘルプを表示
 */
function showScssHelp(): void
{
    echo "MP SCSS機能 v" . MP_VERSION . "\n";
    echo "使用方法:\n";
    echo "  php mp/mp.php scss <command> [options]\n";
    echo "\n";
    echo "利用可能なコマンド:\n";
    echo "【Global色編集】\n";
    echo "  color <color_value>    色の検索・追加（インタラクティブ）\n";
    echo "  list                   全色一覧を表示\n";
    echo "  backup                 バックアップを作成\n";
    echo "  restore [file]         バックアップから復元\n";
    echo "\n";
    echo "【Module生成】\n";
    echo "  module init            全モジュールのSCSS生成\n";
    echo "  module update          全モジュールのSCSS更新\n";
    echo "  module add <module/file> 単一モジュールSCSS生成\n";
    echo "  module stats           モジュール統計表示\n";
    echo "\n";
    echo "例:\n";
    echo "  php mp/mp.php scss color #FFFFFF\n";
    echo "  php mp/mp.php scss module init\n";
    echo "  php mp/mp.php scss module add bl_company/company\n";
    echo "\n";
}

/**
 * 色検索・追加コマンドを処理
 */
function handleColorCommand(string $colorValue, array $options): int
{
    try {
        require_once __DIR__ . '/scss/color.php';
        $manager = new SCSSColorManager();

        // 既存色を検索
        $existing = $manager->searchByColor($colorValue);
        $similar = $manager->findSimilarColors($colorValue, 50);

        echo "=== 色検索結果: {$colorValue} ===\n\n";

        if (!empty($existing)) {
            echo "✓ この色は既に登録されています:\n";
            foreach ($existing as $color) {
                echo "  • {$color['variable']}: {$color['value']}";
                if (!empty($color['comment'])) {
                    echo " ({$color['comment']})";
                }
                echo " [{$manager->getCategories()[$color['category']]}]\n";
            }
            echo "\n";
        }

        if (!empty($similar)) {
            echo "📎 類似色:\n";
            $displayCount = min(3, count($similar));
            for ($i = 0; $i < $displayCount; $i++) {
                $color = $similar[$i];
                echo "  • {$color['variable']}: {$color['value']} (差: {$color['distance']})\n";
            }
            echo "\n";
        }

        if (empty($existing)) {
            echo "ℹ️  この色は登録されていません。\n\n";
        }

        // バックアップのみの場合は終了
        if (isset($options['backup-only'])) {
            return 0;
        }

        // インタラクティブメニュー
        echo "次の操作を選択してください:\n";
        echo "1) この色をコピー用に表示して終了\n";
        echo "2) この色を新規追加\n";
        if (!empty($existing)) {
            echo "3) 既存の色を編集\n";
            echo "4) 既存の色を削除\n";
        }
        echo "0) キャンセル\n";
        echo "選択: ";

        $choice = trim(fgets(STDIN));

        switch ($choice) {
            case '1':
                return handleCopyDisplay($colorValue, $existing);

            case '2':
                return handleAddColor($manager, $colorValue);

            case '3':
                if (!empty($existing)) {
                    return handleEditColor($manager, $existing);
                }
                break;

            case '4':
                if (!empty($existing)) {
                    return handleDeleteColor($manager, $existing);
                }
                break;

            case '0':
                echo "キャンセルしました。\n";
                return 0;
        }

        return 0;

    } catch (Exception $e) {
        echo "エラー: {$e->getMessage()}\n";
        return 1;
    }
}

/**
 * コピー表示処理
 */
function handleCopyDisplay(string $colorValue, array $existing): int
{
    echo "\n=== コピー用表示 ===\n";

    if (!empty($existing)) {
        echo "既存の色変数:\n";
        foreach ($existing as $color) {
            echo "{$color['variable']}\n";
        }
    }

    echo "色値: {$colorValue}\n";
    return 0;
}

/**
 * 色追加処理
 */
function handleAddColor(SCSSColorManager $manager, string $colorValue): int
{
    $categories = $manager->getCategories();

    echo "\nカテゴリーを選択してください:\n";
    $categoryKeys = array_keys($categories);
    for ($i = 0; $i < count($categoryKeys); $i++) {
        $key = $categoryKeys[$i];
        echo ($i + 1) . ") {$categories[$key]}\n";
    }
    echo "選択 (1-" . count($categories) . "): ";

    $categoryChoice = trim(fgets(STDIN));
    $categoryIndex = (int)$categoryChoice - 1;

    if ($categoryIndex < 0 || $categoryIndex >= count($categoryKeys)) {
        echo "無効な選択です。\n";
        return 1;
    }

    $selectedCategory = $categoryKeys[$categoryIndex];

    echo "\n色名を入力してください:\n";
    $suggestions = $manager->generateColorName($colorValue);
    echo "自動生成候補: " . implode(', ', $suggestions) . "\n";
    echo "色名 (空欄で自動生成): ";

    $colorName = trim(fgets(STDIN));

    if (empty($colorName)) {
        $colorName = $suggestions[0];
    }

    // カテゴリープレフィックスを追加
    $prefixes = [
        'background' => 'bg-',
        'border' => 'border-',
        'font' => 'font-',
        'hover' => 'img-hover-', // 既存に合わせる
        'shadow' => 'shadow-'
    ];

    $fullName = $prefixes[$selectedCategory] . $colorName;

    // 重複チェック
    if ($manager->checkDuplicate($selectedCategory, $fullName)) {
        $fullName = $manager->generateNumberedName($selectedCategory, $fullName);
        echo "重複のため色名を変更: {$fullName}\n";
    }

    echo "コメントを入力してください (任意): ";
    $comment = trim(fgets(STDIN));

    // 追加実行
    if ($manager->addColor($selectedCategory, $fullName, $colorValue, $comment)) {
        echo "\n✓ --color-{$fullName}: {$colorValue} を追加しました\n";
        if (!empty($comment)) {
            echo "  コメント: {$comment}\n";
        }
        return 0;
    } else {
        echo "\n✗ 色の追加に失敗しました\n";
        return 1;
    }
}

/**
 * 色編集処理
 */
function handleEditColor(SCSSColorManager $manager, array $existing): int
{
    if (count($existing) > 1) {
        echo "\n編集する色を選択してください:\n";
        for ($i = 0; $i < count($existing); $i++) {
            $color = $existing[$i];
            echo ($i + 1) . ") {$color['variable']}: {$color['value']}\n";
        }
        echo "選択: ";

        $choice = trim(fgets(STDIN));
        $index = (int)$choice - 1;

        if ($index < 0 || $index >= count($existing)) {
            echo "無効な選択です。\n";
            return 1;
        }

        $targetColor = $existing[$index];
    } else {
        $targetColor = $existing[0];
    }

    echo "\n現在の設定:\n";
    echo "色名: {$targetColor['name']}\n";
    echo "色値: {$targetColor['value']}\n";
    echo "コメント: {$targetColor['comment']}\n";

    echo "\n新しい色値 (空欄で変更なし): ";
    $newValue = trim(fgets(STDIN));
    if (empty($newValue)) {
        $newValue = $targetColor['value'];
    }

    echo "新しいコメント (空欄で変更なし): ";
    $newComment = trim(fgets(STDIN));
    if (empty($newComment)) {
        $newComment = $targetColor['comment'];
    }

    if ($manager->updateColor($targetColor['name'], $targetColor['name'], $newValue, $newComment)) {
        echo "\n✓ 色を更新しました\n";
        return 0;
    } else {
        echo "\n✗ 色の更新に失敗しました\n";
        return 1;
    }
}

/**
 * 色削除処理
 */
function handleDeleteColor(SCSSColorManager $manager, array $existing): int
{
    if (count($existing) > 1) {
        echo "\n削除する色を選択してください:\n";
        for ($i = 0; $i < count($existing); $i++) {
            $color = $existing[$i];
            echo ($i + 1) . ") {$color['variable']}: {$color['value']}\n";
        }
        echo "選択: ";

        $choice = trim(fgets(STDIN));
        $index = (int)$choice - 1;

        if ($index < 0 || $index >= count($existing)) {
            echo "無効な選択です。\n";
            return 1;
        }

        $targetColor = $existing[$index];
    } else {
        $targetColor = $existing[0];
    }

    echo "\n本当に削除しますか？\n";
    echo "対象: {$targetColor['variable']}: {$targetColor['value']}\n";
    echo "削除 (y/N): ";

    $confirm = trim(fgets(STDIN));
    if (strtolower($confirm) !== 'y') {
        echo "キャンセルしました。\n";
        return 0;
    }

    if ($manager->deleteColor($targetColor['name'])) {
        echo "\n✓ 色を削除しました\n";
        return 0;
    } else {
        echo "\n✗ 色の削除に失敗しました\n";
        return 1;
    }
}

/**
 * モジュールコマンドを処理
 */
function handleModuleCommand(array $args, array $options): int
{
    if (empty($args)) {
        echo "エラー: moduleサブコマンドを指定してください\n";
        echo "使用方法:\n";
        echo "  php mp/mp.php scss module init\n";
        echo "  php mp/mp.php scss module update\n";
        echo "  php mp/mp.php scss module add <module/file>\n";
        echo "  php mp/mp.php scss module stats\n";
        exit(1);
    }

    try {
        require_once __DIR__ . '/scss/module.php';
        $generator = new SCSSModuleGenerator();

        $subCommand = $args[0];
        $subArgs = array_slice($args, 1);

        switch ($subCommand) {
            case 'init':
                echo "SCSS Module生成開始...\n";
                $results = $generator->generateAll(false);

                foreach ($results['details'] as $detail) {
                    echo $detail . "\n";
                }

                echo str_repeat("-", 50) . "\n";
                echo "生成完了: {$results['created']}作成, {$results['skipped']}スキップ, {$results['errors']}エラー\n";
                return $results['errors'] > 0 ? 1 : 0;

            case 'update':
                echo "SCSS Module更新開始...\n";
                $results = $generator->generateAll(true);

                foreach ($results['details'] as $detail) {
                    echo $detail . "\n";
                }

                echo str_repeat("-", 50) . "\n";
                echo "更新完了: {$results['created']}作成/更新, {$results['errors']}エラー\n";
                return $results['errors'] > 0 ? 1 : 0;

            case 'add':
                if (empty($subArgs)) {
                    echo "エラー: module/file を指定してください\n";
                    echo "使用例: php mp/mp.php scss module add bl_company/company\n";
                    return 1;
                }

                $modulePath = $subArgs[0];
                if (strpos($modulePath, '/') === false) {
                    echo "エラー: module/file 形式で指定してください\n";
                    return 1;
                }

                list($moduleName, $fileName) = explode('/', $modulePath, 2);

                echo "SCSS Module追加: {$moduleName}/{$fileName}\n";
                $result = $generator->generateSingle($moduleName, $fileName, false);

                if ($result['created']) {
                    echo "✓ 成功: {$moduleName}/_" . $fileName . ".scss を作成しました\n";
                    return 0;
                } elseif ($result['skipped']) {
                    echo "- スキップ: 既存 - {$moduleName}/_" . $fileName . ".scss\n";
                    return 0;
                }
                return 1;

            case 'stats':
                $stats = $generator->getModuleStats();

                echo "=== Module統計 ===\n";
                echo "総モジュール数: {$stats['total_modules']}\n";
                echo "総ファイル数: {$stats['total_files']}\n";
                echo "ブロックモジュール: {$stats['block_modules']}\n";
                echo "エレメントモジュール: {$stats['element_modules']}\n\n";

                if (!empty($stats['modules'])) {
                    foreach ($stats['modules'] as $moduleName => $moduleInfo) {
                        $typeLabel = $moduleInfo['type'] === 'block' ? 'Block' : 'Element';
                        echo "{$moduleName} ({$typeLabel}) - {$moduleInfo['total']}ファイル\n";
                        foreach ($moduleInfo['files'] as $file) {
                            echo "  • {$file}.php\n";
                        }
                        echo "\n";
                    }
                } else {
                    echo "モジュールが見つかりません。\n";
                }
                return 0;

            default:
                echo "エラー: 不明なmoduleサブコマンド '{$subCommand}'\n";
                return 1;
        }

    } catch (Exception $e) {
        echo "エラー: {$e->getMessage()}\n";
        return 1;
    }
}

/**
 * 一覧表示コマンドを処理
 */
function handleListCommand(): int
{
    try {
        require_once __DIR__ . '/scss/color.php';
        $manager = new SCSSColorManager();

        $colorsByCategory = $manager->getColorsByCategory();
        $categories = $manager->getCategories();

        echo "=== SCSS 色変数一覧 ===\n\n";

        foreach ($categories as $key => $label) {
            if (empty($colorsByCategory[$key])) {
                continue;
            }

            echo "📂 {$label} (" . count($colorsByCategory[$key]) . ")\n";
            echo str_repeat("-", 50) . "\n";

            foreach ($colorsByCategory[$key] as $color) {
                echo "  • {$color['variable']}: {$color['value']}";
                if (!empty($color['comment'])) {
                    echo " // {$color['comment']}";
                }
                echo "\n";
            }
            echo "\n";
        }

        $totalColors = count($manager->getAllColors());
        echo "合計: {$totalColors} 色\n";

        return 0;

    } catch (Exception $e) {
        echo "エラー: {$e->getMessage()}\n";
        return 1;
    }
}

/**
 * バックアップコマンドを処理
 */
function handleBackupCommand(): int
{
    try {
        require_once __DIR__ . '/scss/color.php';
        $manager = new SCSSColorManager();

        $backupFile = $manager->createBackup();
        echo "✓ バックアップを作成しました: " . basename($backupFile) . "\n";

        return 0;

    } catch (Exception $e) {
        echo "エラー: {$e->getMessage()}\n";
        return 1;
    }
}

/**
 * バックアップ一覧表示
 */
function handleListBackupsCommand(): int
{
    $backupDir = __DIR__ . '/backup/color/';

    if (!is_dir($backupDir)) {
        echo "バックアップディレクトリが存在しません。\n";
        return 1;
    }

    $files = glob($backupDir . '*_color.scss');

    if (empty($files)) {
        echo "バックアップファイルが見つかりません。\n";
        return 0;
    }

    // ファイル名でソート（新しい順）
    usort($files, function($a, $b) {
        return filemtime($b) <=> filemtime($a);
    });

    echo "=== バックアップファイル一覧 ===\n\n";

    foreach ($files as $index => $file) {
        $filename = basename($file);
        $timestamp = date('Y-m-d H:i:s', filemtime($file));
        $size = round(filesize($file) / 1024, 1);

        echo ($index + 1) . ") {$filename}\n";
        echo "   作成日時: {$timestamp}\n";
        echo "   サイズ: {$size} KB\n\n";
    }

    echo "復元するには: php mp/mp.php scss restore <filename>\n";

    return 0;
}

/**
 * バックアップ復元コマンドを処理
 */
function handleRestoreCommand(string $filename): int
{
    try {
        require_once __DIR__ . '/scss/color.php';

        $backupDir = __DIR__ . '/backup/color/';
        $backupFile = $backupDir . $filename;

        if (!file_exists($backupFile)) {
            echo "バックアップファイルが見つかりません: {$filename}\n";
            return 1;
        }

        $scssFile = __DIR__ . '/../scss/global/_color.scss';

        echo "復元対象: {$filename}\n";
        echo "復元先: " . basename($scssFile) . "\n";
        echo "本当に復元しますか？現在のファイルは上書きされます。(y/N): ";

        $confirm = trim(fgets(STDIN));
        if (strtolower($confirm) !== 'y') {
            echo "キャンセルしました。\n";
            return 0;
        }

        // 現在のファイルをバックアップしてから復元
        $manager = new SCSSColorManager();
        $currentBackup = $manager->createBackup();
        echo "現在のファイルをバックアップ: " . basename($currentBackup) . "\n";

        if (copy($backupFile, $scssFile)) {
            echo "✓ バックアップから復元しました\n";
            return 0;
        } else {
            echo "✗ 復元に失敗しました\n";
            return 1;
        }

    } catch (Exception $e) {
        echo "エラー: {$e->getMessage()}\n";
        return 1;
    }
}

/**
 * 対話的な入力を読み取り（Windows対応）
 */
function readInput(string $prompt): string
{
    echo $prompt;
    return trim(fgets(STDIN));
}

/**
 * カラー出力（対応ターミナルのみ）
 */
function colorOutput(string $text, string $color = 'default'): string
{
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'reset' => "\033[0m"
    ];

    if (!isset($colors[$color])) {
        return $text;
    }

    return $colors[$color] . $text . $colors['reset'];
}



// ===== メイン処理 =====

$argc = count($argv);

if ($argc < 2) {
    MP::showMainHelp();
    exit(1);
}

$command = $argv[1];
$args = array_slice($argv, 2);

switch ($command) {
    case 'export':
        $parsed = MP::parseArgs($args, ['help', 'config', 'dir', 'init']);
        $options = $parsed['options'];

        if (isset($options['help'])) {
            $export = new MPExport();
            $export->showHelp();
            exit(0);
        }

        if (isset($options['init'])) {
            $export = new MPExport();
            $export->generateConfig();
            exit(0);
        }

        $configFile = isset($options['config']) ? MP_CONFIG_DIR . $options['config'] : null;
        $outputDir = $options['dir'] ?? null;

        $export = new MPExport($configFile, $outputDir);
        exit($export->run());

    case 'init':
        $parsed = MP::parseArgs($args, ['help', 'file', 'sample']);
        $options = $parsed['options'];

        if (isset($options['help'])) {
            $init = new MPInit();
            $init->showHelp();
            exit(0);
        }

        if (isset($options['sample'])) {
            $init = new MPInit();
            $init->generateSample();
            exit(0);
        }

        $modulesFile = isset($options['file']) ? MP_CONFIG_DIR . $options['file'] : null;

        $init = new MPInit($modulesFile);
        exit($init->run());

    case 'add':
        $parsed = MP::parseArgs($args, ['help']);
        $options = $parsed['options'];
        $positional = $parsed['positional'];

        if (isset($options['help'])) {
            $add = new MPAdd();
            $add->showHelp();
            exit(0);
        }

        if (empty($positional)) {
            echo "エラー: モジュールパスを指定してください\n";
            $add = new MPAdd();
            $add->showHelp();
            exit(1);
        }

        $add = new MPAdd();
        exit($add->run($positional[0]));

    case 'help':
    case '--help':
    case '-h':
        MP::showMainHelp();
        exit(0);

    case 'version':
    case '--version':
    case '-v':
        MP::showVersion();
        exit(0);

    case 'scss':
        $parsed = MP::parseArgs($args, ['help', 'backup-only']);
        $options = $parsed['options'];
        $positional = $parsed['positional'];

        if (isset($options['help']) || empty($positional)) {
            showScssHelp();
            exit(0);
        }

        $subCommand = $positional[0];
        $subArgs = array_slice($positional, 1);

        switch ($subCommand) {
            case 'color':
                if (empty($subArgs)) {
                    echo "エラー: 色値を指定してください\n";
                    echo "使用例: php mp/mp.php scss color #FFFFFF\n";
                    exit(1);
                }
                exit(handleColorCommand($subArgs[0], $options));

            case 'list':
                exit(handleListCommand());

            case 'backup':
                exit(handleBackupCommand());

            case 'restore':
                if (empty($subArgs)) {
                    exit(handleListBackupsCommand());
                }
                exit(handleRestoreCommand($subArgs[0]));

            case 'module':
                exit(handleModuleCommand($subArgs, $options));

            default:
                echo "エラー: 不明なサブコマンド '{$subCommand}'\n\n";
                showScssHelp();
                exit(1);
        }

    default:
        echo "エラー: 不明なコマンド '{$command}'\n\n";
        MP::showMainHelp();
        exit(1);
}


?>