from PIL import Image, ImageDraw, ImageFont
from mesh import mesh
import os

SEED = {"01":11, "02":23, "03":37, "04":41, "05":59, "06":67, "07":73, "08":89, "09":97}

W, H = 1080, 1350
BG, INK, DIM = (0x1B, 0x20, 0x28), (255, 255, 255), (0x9A, 0xA8, 0xB6)
CYAN = (0x5F, 0xE8, 0xFF)
TTC = "/System/Library/Fonts/AppleSDGothicNeo.ttc"
MONO = os.path.expanduser("~/Library/Fonts/NotoSansMonoCJKkr-Bold.otf")
PAD_L, PAD_R, PAD_B, PAD_T = 60, 60, 84, 250
OUT = "news"
os.makedirs(OUT, exist_ok=True)

def f(sz, w=6):  return ImageFont.truetype(TTC, sz, index=w)
def m(sz):       return ImageFont.truetype(MONO, sz)

GHOST = Image.open("cta/ghost_mark.png").convert("RGBA")

def card(key="01"):
    im = mesh(SEED[key])
    return im, ImageDraw.Draw(im)

def chip(d, x, y, text="NEWS"):
    """제목 위에 얹는 칩. 계정 액센트 한 색만 쓴다."""
    ft = f(30, 6)
    tw = d.textlength(text, font=ft)
    pw, ph = round(tw) + 40, 52
    d.rounded_rectangle([x, y, x + pw, y + ph], radius=8, fill=CYAN)
    d.text((x + 20, y + 8), text, font=ft, fill=BG)
    return y + ph

def wrap(d, text, font, width):
    lines, cur = [], ""
    for word in text.split(" "):
        t = (cur + " " + word).strip()
        if d.textlength(t, font=font) <= width:
            cur = t
        else:
            lines.append(cur); cur = word
    if cur: lines.append(cur)
    return lines

def ghost_after(im, x, y, line_h):
    """유령은 2줄 제목 끝에 이어 붙인다. 구석에 따로 띄우지 않는다."""
    size = round(line_h * 0.62)
    g = GHOST.resize((round(size * GHOST.width / GHOST.height), size), Image.LANCZOS)
    im.paste(g, (round(x) + 22, y + (line_h - size) // 2), g)

def source(d, y=H - PAD_B - 8):
    d.text((PAD_L, y), "출처 · Anthropic 공식 발표", font=f(24, 2), fill=DIM)

# ── 1. 커버 — 글자 라인을 D01 배경화면 커버와 같은 자리에 둔다 ──
im, d = card("01")
d.text((PAD_L, 180), "2026.09.01", font=m(34), fill=DIM)
d.text((PAD_L, 240), "Claude Fable 5.1", font=f(72, 6), fill=INK)
d.text((PAD_L, 330), "Claude Mythos 5.1", font=f(72, 6), fill=DIM)

# D01 실측: 1줄 글자 y 1003~1042, 2줄 y 1067~1151. 아래 여백 176 이 그 값을 낸다.
COVER_PAD_B = 176
f2 = f(96, 6)
a2, d2 = f2.getmetrics()
y2 = H - COVER_PAD_B - (a2 + d2)
y1 = y2 - int(46 * 1.2) - 6
chip(d, PAD_L, y1 - 26 - 52)
d.text((PAD_L, y1), "과학 에이전트 벤치마크", font=f(46, 6), fill=INK)

x = PAD_L
for t, col in [("24.7%", DIM), ("  \u2192  ", INK), ("52.6%", CYAN)]:
    d.text((x, y2), t, font=f2, fill=col)
    x += d.textlength(t, font=f2)
ghost_after(im, x, y2, a2 + d2)
d.text((PAD_L, y2 + a2 + d2 + 14), "Fable 5  \u2192  Fable 5.1", font=f(34, 2), fill=DIM)
im.save(f"{OUT}/01-cover.jpg", quality=94, subsampling=0)

# ── 본문 카드 만들기 ────────────────────────────────────
def body_card(n, chiptext, head, rows, note=None, mono=False, src=True):
    im, d = card(n[:2])
    y = chip(d, PAD_L, PAD_T, chiptext)
    y += 30
    for ln in wrap(d, head, f(58, 6), W - PAD_L - PAD_R):
        d.text((PAD_L, y), ln, font=f(58, 6), fill=INK); y += 74
    y += 30
    for row in rows:
        if isinstance(row, tuple):
            label, val, hi = row
            fnt = m(40) if mono else f(40, 6)
            d.text((PAD_L, y), label, font=f(38, 2), fill=DIM)
            vw = d.textlength(val, font=fnt)
            d.text((W - PAD_R - vw, y - 2), val, font=fnt, fill=CYAN if hi else INK)
            y += 62
            d.line([PAD_L, y - 12, W - PAD_R, y - 12], fill=(0x4A, 0x58, 0x66), width=1)
        else:
            for ln in wrap(d, row, f(36, 0), W - PAD_L - PAD_R):
                d.text((PAD_L, y), ln, font=f(36, 0), fill=DIM); y += 50
            y += 16
    if note:
        ny = H - PAD_B - 120
        d.line([PAD_L, ny - 20, W - PAD_R, ny - 20], fill=(0x4A, 0x58, 0x66), width=1)
        for ln in wrap(d, note, f(26, 0), W - PAD_L - PAD_R):
            d.text((PAD_L, ny), ln, font=f(26, 0), fill=DIM); ny += 36
    elif src:
        source(d)
    im.save(f"{OUT}/{n}.jpg", quality=94, subsampling=0)

body_card("02-what", "NEWS", "무엇이 나왔나", [
    "Fable 5.1 과 Mythos 5.1 을 함께 냈습니다. 둘은 같은 모델이고 세이프가드 수준만 다릅니다.",
    "Fable 5.1 은 오늘부터 전 채널에서 씁니다. Mythos 5.1 은 사이버 방어와 생명과학 종사자를 위한 trusted access 로만 열립니다.",
])
body_card("03-core", "NEWS", "무엇을 노렸나", [
    "긴 호흡의 복잡한 작업과 리서치입니다.",
    "Anthropic 은 이번 발표를 “AI 모델이 과학 발전에 기여하는 방식의 초기 모습” 이라고 적었습니다.",
])
body_card("04-science", "BENCHMARK", "과학 에이전트", [
    ("Fable 5.1", "52.6%", True),
    ("Opus 5", "29.0%", False),
    ("Fable 5", "24.7%", False),
    ("GPT-5.6 Sol", "22.4%", False),
    "Terminal-Bench-Science 0.1 — 직전 세대의 두 배를 넘겼습니다.",
], mono=True)
body_card("05-coding", "BENCHMARK", "에이전트 코딩", [
    ("Mythos 5.1", "60.9%", True),
    ("Fable 5.1", "55.8%", True),
    ("Opus 5", "52.3%", False),
    ("Fable 5", "42.0%", False),
    ("GPT-5.6 Sol", "37.3%", False),
    "Terminal-Bench 4.0",
], mono=True)
body_card("06-rest", "BENCHMARK", "나머지 지표", [
    ("GDPval-AA v2 지식노동", "1853", True),
    ("OSWorld 2.0 컴퓨터 사용", "77.9%", False),
    ("Humanity's Last Exam 도구 사용", "65.0%", False),
    ("CursorBench 3.2.0", "73.4%", False),
    ("AutomationBench", "31.4%", False),
], note="Anthropic 은 Fable 5.1 을 프로덕션 세이프가드를 켠 상태로 평가했고, 세이프가드가 개입한 일부 과제가 0점 처리되어 실제 성능이 과소평가됐을 수 있다고 각주에 적었습니다. 모든 수치는 Anthropic 자체 발표입니다.", mono=True)
body_card("07-cost", "NEWS", "이번 발표의 진짜 뉴스", [
    ("캐시 읽기 비용", "-75%", True),
    ("실제 워크로드 총비용", "-25%", True),
    ("에이전트 많이 쓰면 최대", "-45%", True),
    "effort level 을 낮추면 Fable 5 와 비슷하거나 더 나은 결과를 훨씬 싸게 얻습니다.",
], mono=True)
body_card("08-safe", "NEWS", "덜 막힙니다", [
    ("사이버보안 정상 요청 오탐", "-60%", True),
    ("기초 생물·의학 질문 거부", "-85%", True),
    "8월 7일 Fable 5 에 넣었던 개선의 연장선입니다.",
], mono=True)
body_card("09-efs", "NEWS", "기업용 EFS", [
    "Enterprise Frontier Safeguards 를 새로 만들었습니다.",
    "zero data retention 수준의 프라이버시와 악용 방지를 함께 가져가는 것이 목표입니다. 올가을부터 단계적으로 나옵니다.",
])
print("완료:", sorted(os.listdir(OUT)))
