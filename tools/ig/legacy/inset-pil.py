from PIL import Image, ImageDraw, ImageOps
import os

W, H = 1080, 1350
SRC, OUT = "crayon/pair", "crayon/out"
os.makedirs(OUT, exist_ok=True)

# 크레용 결과 ↔ 원본 사진
PAIRS = [
    ("dog",     "src/IMG_3654.jpg",  "01-cover"),
    ("play",    "src/IMG_2514.jpeg", "02-play"),
    ("autumn",  "src/IMG_2706.jpeg", "03-autumn"),
    ("library", "src/IMG_2945.jpeg", "04-library"),
    ("sunrise", "src/IMG_3164.jpeg", "05-sunrise"),
    ("sky",     "src/IMG_3903.jpg",  "06-sky"),
    ("dessert", "src/IMG_2722.jpg",  "07-dessert"),
]

INSET_W = 200          # 원본 액자의 가로. 250 의 80% 다
MARGIN  = 56           # 왼쪽·아래 여백
BORDER  = 14           # 흰 테두리 두께

def fill(im, w, h):
    """비율을 지키며 꽉 채워 자른다"""
    s = max(w / im.width, h / im.height)
    im = im.resize((round(im.width * s), round(im.height * s)), Image.LANCZOS)
    l, t = (im.width - w) // 2, (im.height - h) // 2
    return im.crop((l, t, l + w, t + h))

for key, orig_path, out in PAIRS:
    base = fill(Image.open(f"{SRC}/{key}.png").convert("RGB"), W, H)

    # 원본을 세로 액자로. 사진 비율을 살리되 4:5 로 통일한다.
    photo = fill(ImageOps.exif_transpose(Image.open(orig_path)).convert("RGB"), INSET_W, round(INSET_W * 5 / 4))
    fw, fh = photo.width + BORDER * 2, photo.height + BORDER * 2
    frame = Image.new("RGB", (fw, fh), (255, 255, 255))
    frame.paste(photo, (BORDER, BORDER))

    # 종이에 붙인 사진처럼 옅은 그림자를 깐다
    shadow = Image.new("RGBA", (fw + 16, fh + 16), (0, 0, 0, 0))
    ImageDraw.Draw(shadow).rectangle([8, 10, fw + 8, fh + 10], fill=(0, 0, 0, 46))
    from PIL import ImageFilter
    shadow = shadow.filter(ImageFilter.GaussianBlur(7))

    x, y = MARGIN, H - MARGIN - fh
    base.paste(shadow, (x - 8, y - 8), shadow)
    base.paste(frame, (x, y))
    base.save(f"{OUT}/{out}.jpg", quality=94, subsampling=0)
    print(f"{out}.jpg  액자 {fw}x{fh} @ ({x},{y})")
