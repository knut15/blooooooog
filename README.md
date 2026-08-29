# blooooooog

WordPress 블로그의 자식 테마와 로컬 개발 환경.

WordPress 코어는 이 저장소에 없다. `setup-local.sh` 가 내려받는다.

## 로컬에서 띄우기

```bash
./setup-local.sh   # PHP 8.3 · MariaDB · WordPress 설치 (재실행해도 안전)
./serve.sh         # http://localhost:8080
```

`setup-local.sh` 는 최초 실행 시 `.env` 를 만들고 비밀번호를 무작위로 생성한다.
`.env` 는 커밋하지 않는다. 형식은 `.env.example` 을 본다.

사전 준비:

```bash
brew install php@8.3 mariadb
```

## 구성

| 경로 | 내용 |
|---|---|
| `wordpress/wp-content/themes/generatepress-child/` | 자식 테마 (GeneratePress 기반) |
| `setup-local.sh` | 로컬 환경 구축 |
| `serve.sh` | 개발 서버 |
| `router.php` | PHP 내장 서버에서 퍼머링크를 살리는 라우터 |
| `.curvez/` | curvez 프로젝트 전제 |

## 자식 테마

| 파일 | 내용 |
|---|---|
| `style.css` | 디자인 토큰과 전체 스타일 (16개 섹션) |
| `functions.php` | 바이라인, sticky 바, 댓글 폼, 글 네비게이션, 아바타 |
| `inc/avatars.php` | 댓글용 종이접기 스타일 동물 아바타 8종 (SVG) |
| `js/post-sticky.js` | 스크롤 시 상단 고정 바 |

한국어 본문을 위해 `word-break: keep-all` 을 적용했다. 본문 폭 728px, 18px / 행간 1.8.

## 브랜치

2단 전략이다.

- `main` — 배포된 것. PR 머지는 사람이 누른다
- `release` — 통합 브랜치. 작업 브랜치는 여기서 따고 PR 도 여기로 연다
- `feature/*` · `fix/*` — 작업 브랜치

### 머지 방식

단계마다 다르다.

| 흐름 | 방식 | 이유 |
|---|---|---|
| `feature/*` → `release` | **rebase** | 이력을 한 줄로 유지한다 |
| `release` → `main` | **merge commit** | rebase 로 올리면 커밋 해시가 복제돼 두 브랜치 이력이 갈라진다. 그 뒤로는 같은 파일을 양쪽이 각각 추가한 것으로 보여 add/add 충돌이 난다 |

두 단계 모두 rebase 를 쓰면 `release` 와 `main` 이 내용은 같은데 해시가 다른
커밋을 각각 갖게 되고, 다음 머지부터 매번 충돌한다.
