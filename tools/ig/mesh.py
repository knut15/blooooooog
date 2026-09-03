from PIL import Image, ImageFilter
import numpy as np

W, H = 1080, 1350

# 네 색. 전부 어둡게 잡는다 — 흰 글자가 위에 얹히고, 밝은 시안은 액센트 하나로만 남긴다.
PALETTE = [
    (0x14, 0x20, 0x3A),   # 짙은 남색
    (0x0E, 0x4C, 0x5A),   # 청록
    (0x2A, 0x1E, 0x4A),   # 보라
    (0x12, 0x2E, 0x33),   # 먹청
]

def mesh(seed: int, w=W, h=H) -> Image.Image:
    """네 색 앵커를 무작위로 흩고 거리 가중으로 섞은 뒤 크게 늘려 번지게 한다."""
    rng = np.random.default_rng(seed)
    gw, gh = 9, 11                                  # 저해상도에서 섞고 확대하면 매끄럽다
    yy, xx = np.mgrid[0:gh, 0:gw].astype(np.float32)
    xx /= gw - 1; yy /= gh - 1

    acc = np.zeros((gh, gw, 3), np.float32)
    wsum = np.zeros((gh, gw, 1), np.float32)
    for col in PALETTE:
        # 앵커 두 개씩 — 같은 색이 두 군데서 번져야 "랜덤하게 섞인" 모양이 난다
        for _ in range(2):
            px, py = rng.uniform(-0.15, 1.15), rng.uniform(-0.15, 1.15)
            dist = np.sqrt((xx - px) ** 2 + (yy - py) ** 2) + 1e-3
            wgt = (1.0 / dist ** 2.2)[..., None]
            acc += wgt * np.array(col, np.float32)
            wsum += wgt
    grid = (acc / wsum).clip(0, 255).astype(np.uint8)

    im = Image.fromarray(grid, "RGB").resize((w, h), Image.BICUBIC)
    im = im.filter(ImageFilter.GaussianBlur(radius=w * 0.06))

    # 아주 미세한 그레인 — 그라디언트 띠(banding)를 깬다
    a = np.asarray(im).astype(np.int16)
    a += rng.integers(-3, 4, a.shape, dtype=np.int16)
    return Image.fromarray(a.clip(0, 255).astype(np.uint8), "RGB")

if __name__ == "__main__":
    for i in range(1, 4):
        mesh(i).save(f"out/mesh_{i}.jpg", quality=92)
    print("샘플 3장 저장")
