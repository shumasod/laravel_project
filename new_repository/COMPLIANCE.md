# コンプライアンスガイド

このドキュメントは、本プロジェクトにおけるライセンス、著作権、AI学習データ利用に関するコンプライアンスポリシーを定義します。

## 目次

1. [プロジェクトライセンス](#プロジェクトライセンス)
2. [依存関係とOSSライセンス](#依存関係とossライセンス)
3. [外部コードの利用ポリシー](#外部コードの利用ポリシー)
4. [AI学習データとしての利用制限](#ai学習データとしての利用制限)
5. [コンプライアンスチェック](#コンプライアンスチェック)

## プロジェクトライセンス

本プロジェクトはMITライセンスの下で公開されています。詳細は[LICENSE](./LICENSE)ファイルを参照してください。

**ライセンス**: MIT  
**著作権者**: shubox  
**連絡先**: shumasod@gmail.com

## 依存関係とOSSライセンス

### PHP依存関係（Composer）

本プロジェクトは以下の主要なOSSライブラリに依存しています：

- **Laravel Framework** (MIT License)
- **Guzzle HTTP** (MIT License)
- **Symfony Components** (MIT License)
- **PHPUnit** (BSD-3-Clause)
- **Pest** (MIT License)

すべての依存関係のライセンスは互換性があり、商用利用が可能です。

詳細な依存関係とライセンス情報は以下のコマンドで確認できます：

```bash
composer licenses
```

### JavaScript依存関係（npm）

- **Vite** (MIT License)
- **Axios** (MIT License)
- **Vitest** (MIT License)

詳細は以下のコマンドで確認できます：

```bash
npm list --depth=0
```

### ライセンス互換性チェック

プロジェクトでは以下のライセンスを許可しています：

✅ **許可されるライセンス**:
- MIT
- BSD-2-Clause / BSD-3-Clause
- Apache-2.0
- ISC
- LGPL-2.1 / LGPL-3.0
- MPL-2.0

⚠️ **注意が必要なライセンス**:
- GPL-2.0 / GPL-3.0 (強いコピーレフト)
- AGPL-3.0 (ネットワーク経由の利用にも適用)

❌ **禁止されるライセンス**:
- プロプライエタリライセンス
- 商用利用が制限されるライセンス

## 外部コードの利用ポリシー

### 禁止事項

本プロジェクトでは以下の行為を禁止します：

1. **転載禁止コードの無断利用**
   - 個人ブログやQiita等で「転載禁止」と明記されたコードの利用
   - 著作権表示のないコードスニペットの無断コピー
   - ライセンスが不明なコードの利用

2. **プロプライエタリコードの混入**
   - 企業の内部コードの無断利用
   - NDC（秘密保持契約）違反となるコードの利用
   - 競合他社のコードの無断利用

3. **ライセンス違反**
   - GPL等のコピーレフトライセンスとの非互換利用
   - 著作権表示の削除
   - ライセンス条項の無視

### 外部コード利用時のガイドライン

外部のコードを参考にする、または利用する場合は以下を遵守してください：

1. **出典の明記**
   ```php
   /**
    * Based on: [URL]
    * Original Author: [Name]
    * License: [License Type]
    * Modified by: [Your Name]
    */
   ```

2. **ライセンスの確認**
   - 公式ドキュメントやGitHubのLICENSEファイルを確認
   - ライセンスが不明な場合は利用しない
   - 必要に応じて著者に確認を取る

3. **著作権の尊重**
   - 著作権表示を保持する
   - ライセンス条項を遵守する
   - 改変した場合はその旨を明記する

## AI学習データとしての利用制限

### オプトアウトポリシー

本プロジェクトのコードとコンテンツは、**AI学習データとしての利用を明示的に禁止**します。

### 技術的対策

以下の技術的手段により、AIクローラーからのオプトアウトを実施しています：

1. **robots.txt**
   - 場所: `public/robots.txt`
   - 主要なAIクローラーをブロック
   - 対象: GPTBot, Claude-Web, Google-Extended, CCBot, 他

2. **ai.txt**
   - 場所: `public/.well-known/ai.txt`
   - AI学習利用の明示的な禁止宣言
   - 新しい標準規格に準拠

3. **HTTPヘッダー** (推奨)
   ```
   X-Robots-Tag: noai, noimageai
   ```

### ブロック対象のAIクローラー

- OpenAI (GPTBot, ChatGPT-User)
- Anthropic (anthropic-ai, Claude-Web)
- Google (Google-Extended)
- Common Crawl (CCBot)
- Cohere (cohere-ai)
- ByteDance (Bytespider)
- Apple (Applebot-Extended)
- Meta (FacebookBot, Meta-ExternalAgent)
- Perplexity (PerplexityBot)
- その他

### 法的根拠

1. **著作権法**
   - コードは著作物として保護されます
   - 無断での複製・改変は著作権侵害となります

2. **利用規約**
   - 本プロジェクトのコードを利用する際は、MITライセンスに従う必要があります
   - AI学習目的での利用は別途許諾が必要です

## コンプライアンスチェック

### 自動チェックツール

プロジェクトには自動コンプライアンスチェックツールが含まれています：

```bash
php scripts/compliance-check.php
```

このスクリプトは以下をチェックします：

- ✅ 依存関係のライセンス確認
- ✅ ライセンス互換性チェック
- ✅ robots.txtとAIクローラー対策の確認
- ✅ ドキュメントの存在確認
- ✅ ソースコードヘッダーの確認

### CI/CDパイプラインへの統合

継続的にコンプライアンスを維持するため、CI/CDパイプラインに以下を追加することを推奨します：

```yaml
# .github/workflows/compliance.yml 例
- name: Compliance Check
  run: php scripts/compliance-check.php
```

### 定期レビュー

以下のタイミングでコンプライアンスレビューを実施してください：

- 新しい依存関係の追加時
- 外部コードの参照・利用時
- 四半期ごとの定期レビュー
- 重大なライセンス変更の通知を受けた時

## 違反時の対応

コンプライアンス違反を発見した場合は、以下の手順で対応してください：

1. **即座の対応**
   - 該当コードの利用停止
   - リリース版からの除外

2. **調査と対策**
   - 違反の範囲と影響の調査
   - 代替手段の検討
   - 再発防止策の策定

3. **報告**
   - プロジェクトオーナーへの報告
   - 必要に応じて法務への相談

## 問い合わせ

コンプライアンスに関する質問や報告は以下までご連絡ください：

**Email**: shumasod@gmail.com  
**GitHub**: https://github.com/shumasod/laravel_project

---

**最終更新**: 2026-01-19  
**バージョン**: 1.0.0
