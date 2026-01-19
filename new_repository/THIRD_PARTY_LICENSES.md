# サードパーティライセンス

このドキュメントは、本プロジェクトが使用しているサードパーティライブラリとそのライセンス情報を記載しています。

## PHP依存関係（Composer）

### Laravel Framework
- **ライセンス**: MIT License
- **著作権**: Taylor Otwell
- **URL**: https://laravel.com
- **用途**: Webアプリケーションフレームワーク

### Guzzle HTTP Client
- **ライセンス**: MIT License
- **著作権**: Michael Dowling
- **URL**: https://github.com/guzzle/guzzle
- **用途**: HTTPクライアント

### Symfony HTTP Foundation
- **ライセンス**: MIT License
- **著作権**: Fabien Potencier
- **URL**: https://symfony.com
- **用途**: HTTPリクエスト・レスポンス処理

### PHPUnit
- **ライセンス**: BSD-3-Clause
- **著作権**: Sebastian Bergmann
- **URL**: https://phpunit.de
- **用途**: ユニットテストフレームワーク

### Pest
- **ライセンス**: MIT License
- **著作権**: Nuno Maduro
- **URL**: https://pestphp.com
- **用途**: テストフレームワーク

### Mockery
- **ライセンス**: BSD-3-Clause
- **著作権**: Pádraic Brady
- **URL**: https://github.com/mockery/mockery
- **用途**: モックライブラリ

### FakerPHP
- **ライセンス**: MIT License
- **著作権**: François Zaninotto
- **URL**: https://github.com/FakerPHP/Faker
- **用途**: テストデータ生成

## JavaScript依存関係（npm）

### Vite
- **ライセンス**: MIT License
- **著作権**: Evan You
- **URL**: https://vitejs.dev
- **用途**: フロントエンドビルドツール

### Axios
- **ライセンス**: MIT License
- **著作権**: Matt Zabriskie
- **URL**: https://axios-http.com
- **用途**: HTTPクライアント

### Vitest
- **ライセンス**: MIT License
- **著作権**: Anthony Fu
- **URL**: https://vitest.dev
- **用途**: JavaScriptテストフレームワーク

## ライセンス全文

### MIT License

```
MIT License

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

### BSD-3-Clause License

```
BSD 3-Clause License

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this
   list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice,
   this list of conditions and the following disclaimer in the documentation
   and/or other materials provided with the distribution.

3. Neither the name of the copyright holder nor the names of its
   contributors may be used to endorse or promote products derived from
   this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
```

## ライセンスの更新

依存関係を追加・更新した際は、このドキュメントも更新してください。

最新の依存関係リストは以下のコマンドで確認できます：

```bash
# PHP
composer licenses

# JavaScript
npm list --depth=0
```

---

**最終更新**: 2026-01-19
