# 캐러셀 렌더러

`content.json` 한 벌을 넣으면 `1080×1350` JPEG 이 장수만큼 나온다. 나온 파일을
`docs/ig/<폴더>/` 에 넣으면 `ghosty-publish` 스킬이 그대로 발행한다.

```bash
python3 tools/ig/render.py docs/ig/d14/content.json --out docs/ig/d14
```

의존은 **Pillow · numpy · Chrome** 셋뿐이다. playwright 를 쓰지 않는다 — 이미
깔려 있는 `/Applications/Google Chrome.app` 을 headless 로 부른다.

## 파일

| 파일 | 하는 일 |
|---|---|
| `render.py` | 데이터를 템플릿에 주입하고 장마다 Chrome 으로 찍는다 |
| `template.html` | 슬라이드 조판과 색. **고칠 곳은 대개 여기다** |
| `mesh.py` | 본문 장의 배경 그라디언트를 시드로 만든다 |
| `assets/ghost_mark.png` | 커버 제목 끝에 붙는 유령 선화 |
| `content.example.json` | D13 을 이 구조로 옮겨 적은 예시 |
| `legacy/*.py` | D12·D13·N01 을 만든 PIL 스크립트. 아래 "무엇이 대체됐나" 참고 |

## content.json

```jsonc
{
  "handle": "@prompt_ghosty",
  "slides": [ /* 2~10 장 */ ]
}
```

첫 장은 반드시 `cover` 다. 나머지 일곱 타입은 섞어 쓴다.

| type | 필드 | 쓰는 자리 |
|---|---|---|
| `cover` | `photo` `l1` `l2` | 그림 전면 + 2줄 제목 + 유령 |
| `text` | `h` `body[]` `n?` | 문제 제기, 설명 |
| `list` | `h` `items[[제목,설명]]` `n?` | 항목 나열 |
| `prompt` | `h` `label` `prompt` `tip?` `small?` | 프롬프트 전문 |
| `gallery` | `h` `images[4]` `cap?` | 결과물 모음 |
| `compare` | `h` `bad[태그,본문]` `good[…]` | 나쁜 예 / 좋은 예 |
| `quote` | `q` | 한 문장 |
| `cta` | `h` `btn` `note?` | 글자로 된 마무리 장 |
| `image` | `image` | 그림 한 장만 꽉 채운다. 마무리 고스트 장이 이것 |

본문 필드에는 HTML 조각을 쓸 수 있다. `<em>` 은 커버 강조(시안), `<mark>` 은
제목 강조, `<strong>` `<b>` 는 본문 강조다. 줄바꿈은 `\n`.

`images` 와 `photo` 는 `content.json` 이 있는 폴더 기준 상대경로다. 렌더할 때
data URI 로 박아 넣으므로 결과 JPEG 은 원본 파일에 의존하지 않는다.

## 색

**액센트는 시안 `#5FE8FF` 하나뿐이다.** 이 계정의 기존 커버·카드뉴스가 전부 그
한 색으로 되어 있어서, 색을 하나 더 들이면 프로필 그리드에서 두 계정처럼 보인다.

| 토큰 | 값 | 자리 |
|---|---|---|
| `--accent` | `#5FE8FF` | 강조 글자, 번호 원, 브랜드 바, CTA 단추 |
| `--ink` | `#FFFFFF` | 본문 |
| `--ink-soft` | `#9AA8B6` | 보조 설명, 페이지 번호 |
| `--card` | `#232B36` | 박스 바탕 (색이 아니라 명도 한 단계) |
| `--line` | `#4A5866` | 구분선, 나쁜 예 테두리 |
| `--bg` | `#1B2028` | 메시가 덮지 못한 자리 |

원본 샘플은 액센트가 주황·파랑 **둘**이라 `compare` 의 나쁜 예/좋은 예를 색으로
갈랐다. 여기서는 좋은 쪽만 시안이고 나쁜 쪽은 무채색이다. **색만으로 갈리므로
태그 글자를 서로 다르게 써야 한다** — 양쪽 다 "이렇게 쓰면" 으로 두면 구분이
약하다.

## 커버는 기존 커버와 같은 자리에 글자를 둔다

프로필 그리드에 나란히 걸리므로 글자 높이가 어긋나면 한 벌로 안 읽힌다.
`template.html` 의 `.cover .l1 { top:997px }` `.cover .l2 { top:1058px }` 가
D01 실측값에서 나온 값이다. **고치지 않는다.**

새로 뽑은 커버는 재서 확인한다. D01·D13 과 ±3px 안이어야 한다.

```bash
python3 - <<'PY'
import numpy as np; from PIL import Image
a=np.asarray(Image.open("docs/ig/d14/01.jpg").convert("RGB")).astype(int)
m=(a>225).all(axis=2)
for name,(y0,y1) in [("1줄",(960,1056)),("2줄",(1057,1200))]:
    ys=[y0+i for i,r in enumerate(m[y0:y1,60:520]) if r.sum()>3]
    print(name, (min(ys),max(ys)) if ys else None)
PY
# 기준 — 1줄 (1003, 1042) · 2줄 (1067, 1151)
```

## 무엇이 대체됐나

| 옛것 | 새것 | 왜 |
|---|---|---|
| `legacy/cover-pil.py` | `template.html` 의 `cover` | 커버와 본문을 한 렌더러로 묶는다 |
| `legacy/news-pil.py` | `template.html` 의 나머지 타입 | 장을 늘릴 때 코드를 고치지 않는다 |
| `legacy/inset-pil.py` | — | **대체되지 않았다.** 그림 위에 원본 사진 액자를 얹는 조판(D13 2~7장)은 여기에만 있다 |

`legacy/` 는 `/private/tmp` 세션 스크래치패드에만 있던 것을 옮겨 놓은 것이다.
D12·D13·N01 이 실제로 어떻게 만들어졌는지의 유일한 기록이라 남긴다.

## 규칙은 스킬 쪽이 먼저다

이 폴더는 그리는 방법만 다룬다. 무엇을 그릴지는 스킬이 정한다.

- 제목·캡션·컨펌 절차 — `ig-content` 스킬
- 발행 — `ghosty-publish` 스킬

특히 이 둘을 어기지 않는다.

- **이미지 안에 이모지를 넣지 않는다.** 유령은 이모지가 아니라 `ghost_mark.png` 다
- **유령은 커버에만.** 본문 장에는 텍스트 핸들 바만 붙는다
- **마지막 장 CTA 와 캡션이 어긋나면 안 된다.** `cta` 장에 "댓글 남기면 보내드려요"
  를 넣었으면 캡션도 같은 약속이어야 하고, 실제로 보낼 것이 있어야 한다
