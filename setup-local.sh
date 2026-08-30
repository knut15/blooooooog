#!/usr/bin/env bash
# 로컬 WordPress 설치 스크립트 (재실행 가능)
# 사용법: ./setup-local.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
WP_DIR="$ROOT/wordpress"
ENV_FILE="$ROOT/.env"

export PATH="/opt/homebrew/opt/php@8.3/bin:/opt/homebrew/opt/mariadb/bin:$PATH"
WP="php -d memory_limit=512M $ROOT/bin/wp.phar --path=$WP_DIR"

# --- 1. 비밀번호 생성 (최초 1회만, .env 에 보관) ---
if [ ! -f "$ENV_FILE" ]; then
  cat > "$ENV_FILE" <<EOF
DB_NAME=wp_blog
DB_USER=wp_user
DB_PASS=$(openssl rand -base64 18 | tr -d '/+=' | head -c 20)
WP_ADMIN_USER=kim
WP_ADMIN_PASS=$(openssl rand -base64 18 | tr -d '/+=' | head -c 20)
WP_ADMIN_EMAIL=curve.ball.hiro@gmail.com
WP_URL=http://localhost:8080
WP_TITLE=CurvezLog
EOF
  chmod 600 "$ENV_FILE"
  echo "[+] .env 생성"
fi
set -a
# shellcheck source=/dev/null
. "$ENV_FILE"
set +a

# --- 2. MariaDB 기동 ---
brew services start mariadb >/dev/null 2>&1 || true
for _ in $(seq 1 30); do
  mysqladmin ping --silent 2>/dev/null && break
  sleep 1
done
mysqladmin ping --silent || { echo "[!] MariaDB 기동 실패"; exit 1; }
echo "[+] MariaDB 기동 확인"

# --- 3. DB / 사용자 생성 ---
mysql <<EOF
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOF
echo "[+] DB '${DB_NAME}' 준비"

# --- 4. WordPress 코어 다운로드 (한국어) ---
mkdir -p "$WP_DIR"
if [ ! -f "$WP_DIR/wp-load.php" ]; then
  $WP core download --locale=ko_KR
  echo "[+] WordPress 코어 다운로드"
fi

# --- 5. wp-config.php ---
if [ ! -f "$WP_DIR/wp-config.php" ]; then
  $WP config create \
    --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" \
    --dbhost=127.0.0.1 --dbcharset=utf8mb4 --dbcollate=utf8mb4_unicode_ci \
    --dbprefix=wpk_ --locale=ko_KR --skip-check \
    --extra-php <<'PHP'
define( 'DISALLOW_FILE_EDIT', true );   // 관리자 화면에서 테마/플러그인 파일 편집 차단
define( 'WP_POST_REVISIONS', 5 );        // 리비전 누적 제한
define( 'AUTOSAVE_INTERVAL', 120 );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
PHP
  echo "[+] wp-config.php 생성"
fi

# --- 6. 코어 설치 ---
if ! $WP core is-installed 2>/dev/null; then
  $WP core install \
    --url="$WP_URL" --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" --admin_password="$WP_ADMIN_PASS" \
    --admin_email="$WP_ADMIN_EMAIL" --skip-email
  echo "[+] WordPress 설치 완료"
fi

# --- 7. 기본 옵션 (SEO 기초) ---
$WP option update timezone_string 'Asia/Seoul'
$WP option update date_format 'Y년 n월 j일'
$WP option update time_format 'H:i'
$WP option update start_of_week 1
$WP option update blogdescription '' 
$WP rewrite structure '/%category%/%postname%/' --hard
$WP option update permalink_structure '/%category%/%postname%/'
# 검색엔진 색인 허용 (로컬에서는 0 유지, 실서버 이전 시 1)
$WP option update blog_public 0
echo "[+] 기본 옵션 설정"

# --- 8. 기본 콘텐츠 정리 ---
$WP post delete 1 --force 2>/dev/null || true   # Hello world
$WP post delete 2 --force 2>/dev/null || true   # 샘플 페이지
$WP plugin delete akismet hello 2>/dev/null || true
$WP theme delete twentytwentythree twentytwentytwo 2>/dev/null || true
echo "[+] 기본 샘플 콘텐츠 정리"

# --- 9. 테마 ---
$WP theme install generatepress --activate
echo "[+] GeneratePress 설치·활성화"

# --- 10. 플러그인 ---
$WP plugin install seo-by-rank-math --activate
$WP plugin install ad-inserter --activate
echo "[+] 플러그인 설치·활성화"

# --- 11. ads.txt 자리표시자 ---
if [ ! -f "$WP_DIR/ads.txt" ]; then
  cat > "$WP_DIR/ads.txt" <<'EOF'
# 애드센스 승인 후 아래 한 줄을 실제 퍼블리셔 ID 로 교체할 것
# google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0
EOF
  echo "[+] ads.txt 자리표시자 생성"
fi

echo
echo "==================== 완료 ===================="
echo " 경로   : $WP_DIR"
echo " URL    : $WP_URL"
echo " 관리자 : $WP_ADMIN_USER / $WP_ADMIN_PASS"
echo " 서버 실행: ./serve.sh"
echo "=============================================="
