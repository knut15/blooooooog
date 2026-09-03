from PIL import Image, ImageDraw, ImageFont, ImageOps, ImageFilter

W, H = 1080, 1350
CYAN, WHITE = (0x5F, 0xE8, 0xFF), (255, 255, 255)
TTC = "/System/Library/Fonts/AppleSDGothicNeo.ttc"
BOLD = 6
PAD_L, PAD_B, INSET_M = 60, 176, 56      # 아래 여백 176 은 D01 커버의 글자 라인 값이다

L1 = "해외에서 난리난"
L2 = [("크레용 프사 ", WHITE), ("만들기", CYAN)]

def fill(im, w, h):
    s = max(w / im.width, h / im.height)
    im = im.resize((round(im.width * s), round(im.height * s)), Image.LANCZOS)
    l, t = (im.width - w) // 2, (im.height - h) // 2
    return im.crop((l, t, l + w, t + h))

base = fill(Image.open("crayon/pair/dog.png").convert("RGB"), W, H)

# 크레용 그림이 밝아서 흰 글자가 안 읽힌다. 아래쪽만 어둡게 깐다.
grad = Image.new("L", (1, H), 0)
start = int(H * 0.52)
for y in range(start, H):
    t = (y - start) / (H - start)
    grad.putpixel((0, y), int(255 * 0.86 * (t ** 1.35)))
base.paste(Image.new("RGB", (W, H), (0, 0, 0)), (0, 0), grad.resize((W, H)))

d = ImageDraw.Draw(base)
f1, f2 = ImageFont.truetype(TTC, 46, index=BOLD), ImageFont.truetype(TTC, 96, index=BOLD)
a2, d2 = f2.getmetrics()
y2 = H - PAD_B - (a2 + d2)
y1 = y2 - int(46 * 1.2) - 6
d.text((PAD_L, y1), L1, font=f1, fill=WHITE)

x = PAD_L
for text, col in L2:                      # 자간 -1%
    for ch in text:
        d.text((x, y2), ch, font=f2, fill=col)
        x += d.textlength(ch, font=f2) - 0.96

# 유령은 2줄 제목 끝에 이어 붙인다. 이모지가 아니라 프로필 선화다.
mark = Image.open("cta/ghost_mark.png").convert("RGBA")
MH = 76
mark = mark.resize((round(MH * mark.width / mark.height), MH), Image.LANCZOS)
base.paste(mark, (round(x) + 22, y2 + (a2 + d2 - MH) // 2), mark)

base.save("crayon/out/01-cover.jpg", quality=94, subsampling=0)
print("커버 저장 · 원본 액자 없음 · 2줄 끝 x", round(x))
