#!/usr/bin/env php
<?php

/**
 * コンプライアンスチェックスクリプト
 *
 * このスクリプトは以下をチェックします：
 * - OSSライセンスの確認
 * - 互換性のないライセンスの検出
 * - ライセンス情報の欠如
 * - 外部コード利用のドキュメント
 */

class ComplianceChecker
{
    private array $warnings = [];
    private array $errors = [];
    private array $info = [];

    // 警告を出すライセンス（GPLv3など強いコピーレフト）
    private const COPYLEFT_LICENSES = ['GPL-3.0', 'AGPL-3.0', 'GPL-2.0'];

    // 許可されるライセンス
    private const ALLOWED_LICENSES = [
        'MIT', 'BSD-2-Clause', 'BSD-3-Clause', 'Apache-2.0',
        'ISC', 'CC0-1.0', 'LGPL-2.1', 'LGPL-3.0', 'MPL-2.0'
    ];

    public function run(): int
    {
        echo "=== コンプライアンスチェック開始 ===\n\n";

        $this->checkComposerLicenses();
        $this->checkPackageJsonLicenses();
        $this->checkRobotsTxt();
        $this->checkComplianceDocumentation();
        $this->checkSourceCodeHeaders();

        return $this->printResults();
    }

    private function checkComposerLicenses(): void
    {
        echo "📦 Composer依存関係のライセンスチェック...\n";

        if (!file_exists('composer.json')) {
            $this->errors[] = 'composer.jsonが見つかりません';
            return;
        }

        $composerJson = json_decode(file_get_contents('composer.json'), true);

        // プロジェクト自体のライセンス確認
        if (isset($composerJson['license'])) {
            $this->info[] = "プロジェクトライセンス: {$composerJson['license']}";
        } else {
            $this->warnings[] = 'プロジェクトのライセンスが未設定です';
        }

        // composer.lockから依存関係のライセンスを確認
        if (file_exists('composer.lock')) {
            $this->checkComposerLockLicenses();
        } else {
            $this->warnings[] = 'composer.lockが見つかりません。composer installを実行してください';
        }

        echo "✓ Composerチェック完了\n\n";
    }

    private function checkComposerLockLicenses(): void
    {
        $composerLock = json_decode(file_get_contents('composer.lock'), true);

        $packages = array_merge(
            $composerLock['packages'] ?? [],
            $composerLock['packages-dev'] ?? []
        );

        $licenseCount = [];

        foreach ($packages as $package) {
            $name = $package['name'];
            $licenses = $package['license'] ?? ['UNKNOWN'];

            foreach ($licenses as $license) {
                $licenseCount[$license] = ($licenseCount[$license] ?? 0) + 1;

                // コピーレフトライセンスの警告
                if (in_array($license, self::COPYLEFT_LICENSES)) {
                    $this->warnings[] = "強いコピーレフトライセンス検出: {$name} ({$license})";
                }

                // 不明なライセンス
                if ($license === 'UNKNOWN') {
                    $this->warnings[] = "ライセンス不明: {$name}";
                }

                // プロプライエタリライセンス
                if (stripos($license, 'proprietary') !== false) {
                    $this->errors[] = "プロプライエタリライセンス検出: {$name} ({$license})";
                }
            }
        }

        $this->info[] = "依存パッケージ数: " . count($packages);
        $this->info[] = "ライセンス種類: " . implode(', ', array_keys($licenseCount));
    }

    private function checkPackageJsonLicenses(): void
    {
        echo "📦 npm依存関係のライセンスチェック...\n";

        if (!file_exists('package.json')) {
            $this->info[] = 'package.jsonが見つかりません（npmを使用していない場合は問題ありません）';
            echo "✓ npmチェックスキップ\n\n";
            return;
        }

        $packageJson = json_decode(file_get_contents('package.json'), true);

        if (isset($packageJson['license'])) {
            $this->info[] = "npmプロジェクトライセンス: {$packageJson['license']}";
        }

        echo "✓ npmチェック完了\n\n";
    }

    private function checkRobotsTxt(): void
    {
        echo "🤖 robots.txtとAIクローラー対策チェック...\n";

        $robotsTxtPath = 'public/robots.txt';

        if (!file_exists($robotsTxtPath)) {
            $this->warnings[] = 'robots.txtが存在しません。AIクローラー対策が未設定です';
            echo "✓ robots.txtチェック完了（未設定）\n\n";
            return;
        }

        $robotsTxt = file_get_contents($robotsTxtPath);

        // 主要なAIクローラーのチェック
        $aiCrawlers = [
            'GPTBot' => 'OpenAI',
            'ChatGPT-User' => 'OpenAI',
            'CCBot' => 'Common Crawl (used by AI)',
            'anthropic-ai' => 'Anthropic Claude',
            'Claude-Web' => 'Anthropic Claude',
            'Google-Extended' => 'Google Bard/Gemini',
            'cohere-ai' => 'Cohere',
            'Bytespider' => 'ByteDance (TikTok)',
            'Applebot-Extended' => 'Apple Intelligence'
        ];

        $blockedCrawlers = [];
        foreach ($aiCrawlers as $crawler => $company) {
            if (stripos($robotsTxt, $crawler) !== false) {
                $blockedCrawlers[] = "$crawler ($company)";
            }
        }

        if (empty($blockedCrawlers)) {
            $this->warnings[] = 'AIクローラーのブロック設定が見つかりません';
        } else {
            $this->info[] = 'ブロック済みAIクローラー: ' . implode(', ', $blockedCrawlers);
        }

        echo "✓ robots.txtチェック完了\n\n";
    }

    private function checkComplianceDocumentation(): void
    {
        echo "📄 コンプライアンスドキュメントチェック...\n";

        $docs = [
            'LICENSE' => false,
            'LICENSE.md' => false,
            'COMPLIANCE.md' => false,
            'THIRD_PARTY_LICENSES.md' => false
        ];

        foreach ($docs as $doc => $exists) {
            if (file_exists($doc)) {
                $docs[$doc] = true;
                $this->info[] = "$doc が存在します";
            }
        }

        if (!$docs['LICENSE'] && !$docs['LICENSE.md']) {
            $this->errors[] = 'LICENSEファイルが存在しません';
        }

        if (!$docs['COMPLIANCE.md']) {
            $this->warnings[] = 'COMPLIANCE.md（コンプライアンスガイド）が存在しません';
        }

        echo "✓ ドキュメントチェック完了\n\n";
    }

    private function checkSourceCodeHeaders(): void
    {
        echo "💻 ソースコードのヘッダーチェック...\n";

        // PHPファイルのサンプルチェック
        $phpFiles = glob('app/**/*.php', GLOB_BRACE);

        if (empty($phpFiles)) {
            echo "✓ ソースコードチェックスキップ\n\n";
            return;
        }

        // 最初の3ファイルをサンプルチェック
        $sampleFiles = array_slice($phpFiles, 0, 3);
        $filesWithCopyright = 0;

        foreach ($sampleFiles as $file) {
            $content = file_get_contents($file);
            if (preg_match('/@copyright|Copyright \(c\)|©/', $content)) {
                $filesWithCopyright++;
            }
        }

        if ($filesWithCopyright === 0) {
            $this->info[] = 'サンプルファイルに著作権表示が見つかりませんでした（必須ではありません）';
        } else {
            $this->info[] = "サンプルファイル中 $filesWithCopyright/{count($sampleFiles)} に著作権表示があります";
        }

        echo "✓ ソースコードチェック完了\n\n";
    }

    private function printResults(): int
    {
        echo "\n=== チェック結果 ===\n\n";

        if (!empty($this->info)) {
            echo "ℹ️  情報:\n";
            foreach ($this->info as $info) {
                echo "  • $info\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️  警告:\n";
            foreach ($this->warnings as $warning) {
                echo "  • $warning\n";
            }
            echo "\n";
        }

        if (!empty($this->errors)) {
            echo "❌ エラー:\n";
            foreach ($this->errors as $error) {
                echo "  • $error\n";
            }
            echo "\n";
            echo "コンプライアンスチェックに失敗しました。\n";
            return 1;
        }

        echo "✅ コンプライアンスチェック完了\n";

        if (!empty($this->warnings)) {
            echo "\n⚠️  警告がありますが、継続可能です。\n";
            return 0;
        }

        return 0;
    }
}

// スクリプト実行
$checker = new ComplianceChecker();
exit($checker->run());
