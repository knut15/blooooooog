#!/usr/bin/env bash
# 로컬 개발 서버 실행 (http://localhost:8080)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
export PATH="/opt/homebrew/opt/php@8.3/bin:$PATH"
brew services start mariadb >/dev/null 2>&1 || true
echo "http://localhost:8080  (종료: Ctrl+C)"
php -S localhost:8080 -t "$ROOT/wordpress" "$ROOT/router.php"
