<?php
/**
 * SCSS Module生成機能
 * 
 * partsフォルダのPHPモジュールからSCSSファイルを自動生成
 * - HTMLクラス解析
 * - テンプレート適用
 * - メディアクエリ挿入
 */

// CLI実行チェック（Web APIからの呼び出しは除外）
if (php_sapi_name() !== 'cli' && !defined('MP_WEB_API')) {
    die("このスクリプトはコマンドラインでのみ実行可能です。\n");
}

define('SCSS_MODULE_DIR', __DIR__ . '/../../scss/module/');
define('SCSS_TEMPLATE_DIR', __DIR__ . '/../../scss/template/');
define('PARTS_DIR', __DIR__ . '/../../parts/');

/**
 * SCSS Module生成クラス
 */
class SCSSModuleGenerator
{
    private string $scssModuleDir;
    private string $scssTemplateDir;
    private string $partsDir;
    private string $baseTemplate = '';
    private string $queryTemplate = '';

    public function __construct(string $scssModuleDir = null, string $scssTemplateDir = null, string $partsDir = null)
    {
        $this->scssModuleDir = $scssModuleDir ?? SCSS_MODULE_DIR;
        $this->scssTemplateDir = $scssTemplateDir ?? SCSS_TEMPLATE_DIR;
        $this->partsDir = $partsDir ?? PARTS_DIR;

        $this->loadTemplates();
    }

    /**
     * テンプレートファイルを読み込み
     */
    private function loadTemplates(): void
    {
        // base.scss
        $baseFile = $this->scssTemplateDir . 'base.scss';
        if (file_exists($baseFile)) {
            $this->baseTemplate = file_get_contents($baseFile);
        }

        // query.scss
        $queryFile = $this->scssTemplateDir . 'query.scss';
        if (file_exists($queryFile)) {
            $this->queryTemplate = file_get_contents($queryFile);
        }
    }

    /**
     * partsディレクトリをスキャンしてモジュール一覧を取得
     */
    public function scanModules(): array
    {
        if (!is_dir($this->partsDir)) {
            throw new Exception("partsディレクトリが見つかりません: {$this->partsDir}");
        }

        $modules = [];
        $dirs = glob($this->partsDir . '{bl_*,el_*}', GLOB_BRACE | GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $moduleName = basename($dir);
            $phpFiles = glob($dir . '/*.php');

            $files = [];
            foreach ($phpFiles as $phpFile) {
                $fileName = basename($phpFile, '.php');
                $files[] = [
                    'name' => $fileName,
                    'path' => $phpFile,
                    'scss_path' => $this->scssModuleDir . $moduleName . '/_' . $fileName . '.scss'
                ];
            }

            if (!empty($files)) {
                $modules[$moduleName] = [
                    'type' => str_starts_with($moduleName, 'bl_') ? 'block' : 'element',
                    'files' => $files,
                    'total' => count($files)
                ];
            }
        }

        return $modules;
    }

    /**
     * 全モジュールのSCSS生成
     */
    public function generateAll(bool $updateMode = false): array
    {
        $modules = $this->scanModules();
        $results = [
            'total' => 0,
            'created' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($modules as $moduleName => $moduleInfo) {
            foreach ($moduleInfo['files'] as $file) {
                $results['total']++;

                try {
                    $result = $this->generateSingle($moduleName, $file['name'], $updateMode);

                    if ($result['created']) {
                        $results['created']++;
                        $results['details'][] = "✓ 作成: {$moduleName}/_" . $file['name'] . ".scss";
                    } elseif ($result['skipped']) {
                        $results['skipped']++;
                        $results['details'][] = "- スキップ: 既存 - {$moduleName}/_" . $file['name'] . ".scss";
                    }
                } catch (Exception $e) {
                    $results['errors']++;
                    $results['details'][] = "✗ エラー: {$moduleName}/{$file['name']} - " . $e->getMessage();
                }
            }
        }

        return $results;
    }

    /**
     * 単一モジュールのSCSS生成
     */
    public function generateSingle(string $moduleName, string $fileName, bool $updateMode = false): array
    {
        $phpFile = $this->partsDir . $moduleName . '/' . $fileName . '.php';
        $scssFile = $this->scssModuleDir . $moduleName . '/_' . $fileName . '.scss';

        // PHPファイル存在チェック
        if (!file_exists($phpFile)) {
            throw new Exception("PHPファイルが見つかりません: {$phpFile}");
        }

        // 既存ファイルチェック（updateモードでない場合）
        $fileExisted = file_exists($scssFile);
        if (!$updateMode && $fileExisted) {
            return ['created' => false, 'skipped' => true, 'updated' => false];
        }

        // ディレクトリ作成
        $scssDir = dirname($scssFile);
        if (!is_dir($scssDir)) {
            if (!mkdir($scssDir, 0755, true)) {
                throw new Exception("ディレクトリ作成失敗: {$scssDir}");
            }
        }

        // PHPファイル読み込み
        try {
            $phpContent = file_get_contents($phpFile);
            if ($phpContent === false) {
                throw new Exception("ファイル読み込み失敗");
            }
        } catch (Exception $e) {
            throw new Exception("読み込みエラー: " . $e->getMessage());
        }

        // HTMLクラス抽出
        try {
            $classes = $this->extractClasses($phpContent, $moduleName);
        } catch (Exception $e) {
            // HTML解析失敗時はbaseテンプレートのみでファイル生成
            $classes = [];
        }

        // SCSS内容生成
        $scssContent = $this->generateScssContent($moduleName, $classes);

        // ファイル書き込み
        if (file_put_contents($scssFile, $scssContent) === false) {
            throw new Exception("ファイル書き込み失敗: {$scssFile}");
        }

        return [
            'created' => true,
            'skipped' => false,
            'updated' => $updateMode && $fileExisted
        ];
    }

    /**
     * PHPファイルからHTMLクラスを抽出
     */
    private function extractClasses(string $phpContent, string $moduleName): array
    {
        // PHPコードを除去してHTML部分のみ抽出
        $htmlContent = $this->extractHtmlFromPhp($phpContent);

        // クラス名の抽出パターン
        $pattern = '/class=["\']([^"\']*)["\'][^>]*>/i';
        preg_match_all($pattern, $htmlContent, $matches);

        $targetPrefix = str_replace('_', '_', $moduleName); // bl_company
        $lyPrefix = 'ly_' . substr($moduleName, 3); // ly_company

        $extractedClasses = [];
        $foundClasses = [];

        foreach ($matches[1] as $classString) {
            $classes = preg_split('/\s+/', trim($classString));

            foreach ($classes as $class) {
                $class = trim($class);
                if (empty($class)) continue;

                // 対象プレフィックスまたはlyプレフィックスで始まるクラスのみ
                if (str_starts_with($class, $targetPrefix) || str_starts_with($class, $lyPrefix)) {
                    if (!in_array($class, $foundClasses)) {
                        $foundClasses[] = $class;

                        // 注釈の判定
                        $comment = $this->getClassComment($class);

                        $extractedClasses[] = [
                            'name' => $class,
                            'comment' => $comment
                        ];
                    }
                }
            }
        }

        return $extractedClasses;
    }

    /**
     * PHPファイルからHTML部分を抽出
     */
    private function extractHtmlFromPhp(string $phpContent): string
    {
        // auto_indent_start()とauto_indent_end()の間のHTML部分を抽出
        $pattern = '/auto_indent_start\(\);\s*\?>(.*?)<\?php[^>]*auto_indent_end\(/s';

        if (preg_match($pattern, $phpContent, $matches)) {
            return $matches[1];
        }

        // パターンが見つからない場合は、PHPタグを除去してHTML部分を取得
        $html = preg_replace('/<\?php.*?\?>/s', '', $phpContent);
        return $html;
    }

    /**
     * クラス名から注釈を取得
     */
    private function getClassComment(string $className): string
    {
        if (str_ends_with($className, '_img')) {
            return '//画像';
        } elseif (str_ends_with($className, '_list')) {
            return '//リスト';
        } elseif (str_ends_with($className, '_txt')) {
            return '//文章';
        } elseif (str_ends_with($className, '_ttl')) {
            return '//タイトル';
        }

        return '';
    }

    /**
     * SCSS内容を生成
     */
    private function generateScssContent(string $moduleName, array $classes): string
    {
        $content = '';

        // base.scssテンプレート
        if (!empty($this->baseTemplate)) {
            $content .= trim($this->baseTemplate) . "\n\n";
        }

        // モジュールタイプコメント
        if (str_starts_with($moduleName, 'bl_')) {
            $content .= "/* ブロックモジュール */\n";
        } elseif (str_starts_with($moduleName, 'el_')) {
            $content .= "/* エレメントモジュール */\n";
        }

        // クラス別スタイル生成
        foreach ($classes as $index => $classInfo) {
            // 前のクラスとの間に改行を追加（最初以外）
            if ($index > 0) {
                $content .= "\n";
            }

            // 注釈がある場合は追加
            if (!empty($classInfo['comment'])) {
                $content .= $classInfo['comment'] . "\n";
            }

            // クラス定義
            $content .= ".{$classInfo['name']} {\n";

            // query.scssの内容を挿入
            if (!empty($this->queryTemplate)) {
                $indentedQuery = $this->indentContent($this->queryTemplate, 1);
                $content .= $indentedQuery;
            }

            $content .= "}\n";
        }

        return $content;
    }

    /**
     * 内容にインデントを追加
     */
    private function indentContent(string $content, int $level): string
    {
        $indent = str_repeat('    ', $level); // 4スペース × レベル
        $lines = explode("\n", $content);
        $indentedLines = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $indentedLines[] = '';
            } else {
                $indentedLines[] = $indent . $line;
            }
        }

        return implode("\n", $indentedLines) . "\n";
    }

    /**
     * モジュール統計を取得
     */
    public function getModuleStats(): array
    {
        $modules = $this->scanModules();
        $stats = [
            'total_modules' => count($modules),
            'total_files' => 0,
            'block_modules' => 0,
            'element_modules' => 0,
            'modules' => []
        ];

        foreach ($modules as $moduleName => $moduleInfo) {
            $stats['total_files'] += $moduleInfo['total'];

            if ($moduleInfo['type'] === 'block') {
                $stats['block_modules']++;
            } else {
                $stats['element_modules']++;
            }

            $stats['modules'][$moduleName] = [
                'type' => $moduleInfo['type'],
                'files' => array_column($moduleInfo['files'], 'name'),
                'total' => $moduleInfo['total']
            ];
        }

        return $stats;
    }
}

/**
 * CLI実行部分
 */
if (php_sapi_name() === 'cli' && isset($argv) && basename($argv[0]) === 'module.php') {
    $generator = new SCSSModuleGenerator();

    if (count($argv) < 2) {
        echo "使用方法:\n";
        echo "  php scss/module.php init      - 全モジュールのSCSS生成\n";
        echo "  php scss/module.php update    - 全モジュールのSCSS更新\n";
        echo "  php scss/module.php add <module/file> - 単一モジュール生成\n";
        echo "  php scss/module.php stats     - モジュール統計表示\n";
        exit(1);
    }

    $command = $argv[1];

    try {
        switch ($command) {
            case 'init':
                echo "SCSS Module生成開始...\n";
                $results = $generator->generateAll(false);

                foreach ($results['details'] as $detail) {
                    echo $detail . "\n";
                }

                echo str_repeat("-", 50) . "\n";
                echo "生成完了: {$results['created']}作成, {$results['skipped']}スキップ, {$results['errors']}エラー\n";
                break;

            case 'update':
                echo "SCSS Module更新開始...\n";
                $results = $generator->generateAll(true);

                foreach ($results['details'] as $detail) {
                    echo $detail . "\n";
                }

                echo str_repeat("-", 50) . "\n";
                echo "更新完了: {$results['created']}作成/更新, {$results['errors']}エラー\n";
                break;

            case 'add':
                if (count($argv) < 3) {
                    echo "使用方法: php scss/module.php add <module/file>\n";
                    echo "例: php scss/module.php add bl_company/company\n";
                    exit(1);
                }

                $modulePath = $argv[2];
                if (strpos($modulePath, '/') === false) {
                    echo "エラー: module/file 形式で指定してください\n";
                    exit(1);
                }

                list($moduleName, $fileName) = explode('/', $modulePath, 2);

                echo "SCSS Module追加: {$moduleName}/{$fileName}\n";
                $result = $generator->generateSingle($moduleName, $fileName, false);

                if ($result['created']) {
                    echo "✓ 成功: {$moduleName}/_" . $fileName . ".scss を作成しました\n";
                } elseif ($result['skipped']) {
                    echo "- スキップ: 既存 - {$moduleName}/_" . $fileName . ".scss\n";
                }
                break;

            case 'stats':
                $stats = $generator->getModuleStats();

                echo "=== Module統計 ===\n";
                echo "総モジュール数: {$stats['total_modules']}\n";
                echo "総ファイル数: {$stats['total_files']}\n";
                echo "ブロックモジュール: {$stats['block_modules']}\n";
                echo "エレメントモジュール: {$stats['element_modules']}\n\n";

                foreach ($stats['modules'] as $moduleName => $moduleInfo) {
                    $typeLabel = $moduleInfo['type'] === 'block' ? 'Block' : 'Element';
                    echo "{$moduleName} ({$typeLabel}) - {$moduleInfo['total']}ファイル\n";
                    foreach ($moduleInfo['files'] as $file) {
                        echo "  • {$file}.php\n";
                    }
                    echo "\n";
                }
                break;

            default:
                echo "エラー: 不明なコマンド '{$command}'\n";
                exit(1);
        }
    } catch (Exception $e) {
        echo "エラー: {$e->getMessage()}\n";
        exit(1);
    }
}
?>