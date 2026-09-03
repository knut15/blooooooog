#!/usr/bin/env python3
"""
render.py — content.json + template.html → 캐러셀 JPEG (1080x1350)

    python3 render.py content.json --out out

playwright 를 쓰지 않는다. 이미 깔려 있는 Chrome 을 headless 로 부른다.
장마다 HTML 을 한 벌 쓰고 뷰포트를 통째로 찍는다.
"""
import sys, os, json, pathlib, base64, io, subprocess, tempfile
from PIL import Image
from mesh import mesh

W, H = 1080, 1350
HERE = pathlib.Path(__file__).parent
CHROME = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
SEEDS = [11, 23, 37, 41, 59, 67, 73, 89, 97, 101]


def uri(path: pathlib.Path, max_w=760, quality=88) -> str:
    im = Image.open(path).convert("RGB")
    im.thumbnail((max_w, max_w * 2), Image.LANCZOS)
    buf = io.BytesIO()
    im.save(buf, "JPEG", quality=quality)
    return "data:image/jpeg;base64," + base64.b64encode(buf.getvalue()).decode()


def mesh_uri(seed: int) -> str:
    buf = io.BytesIO()
    mesh(seed).save(buf, "JPEG", quality=88)
    return "data:image/jpeg;base64," + base64.b64encode(buf.getvalue()).decode()


def main():
    args = [a for a in sys.argv[1:] if not a.startswith("--")]
    data_path = pathlib.Path(args[0] if args else HERE / "content.json").resolve()
    out = pathlib.Path(sys.argv[sys.argv.index("--out") + 1]) if "--out" in sys.argv \
          else data_path.parent / "out"
    out.mkdir(parents=True, exist_ok=True)
    base_dir = data_path.parent

    data = json.loads(data_path.read_text(encoding="utf-8"))
    data.setdefault("handle", "@prompt_ghosty")
    data["ghost"] = "data:image/png;base64," + base64.b64encode(
        (HERE / "assets/ghost_mark.png").read_bytes()).decode()

    # 로컬 이미지를 전부 data URI 로 바꾼다. file:// 로 열어도 그림이 빠지지 않게.
    for i, sl in enumerate(data["slides"]):
        if sl.get("image"):
            sl["image"] = uri(base_dir / sl["image"], max_w=1200, quality=92)
        if sl.get("photo"):
            sl["photo"] = uri(base_dir / sl["photo"], max_w=1200, quality=92)
        if sl.get("images"):
            sl["images"] = [uri(base_dir / p) for p in sl["images"]]
        if sl["type"] not in ("cover", "image"):      # 이 둘은 그림이 배경이다
            sl["bg"] = mesh_uri(SEEDS[i % len(SEEDS)])

    tpl = (HERE / "template.html").read_text(encoding="utf-8")
    n = len(data["slides"])

    with tempfile.TemporaryDirectory() as tmp:
        for i in range(1, n + 1):
            inject = (f"<script>window.SLIDES={json.dumps(data, ensure_ascii=False)};"
                      f"window.ONLY={i};</script>")
            page = pathlib.Path(tmp) / f"{i:02d}.html"
            page.write_text(tpl.replace("<div id=\"root\"></div>",
                                        inject + "<div id=\"root\"></div>"), encoding="utf-8")
            png = pathlib.Path(tmp) / f"{i:02d}.png"
            subprocess.run([CHROME, "--headless", "--disable-gpu", "--hide-scrollbars",
                            "--force-device-scale-factor=1", f"--window-size={W},{H}",
                            f"--screenshot={png}", page.as_uri()],
                           check=True, capture_output=True)
            Image.open(png).convert("RGB").save(out / f"{i:02d}.jpg", quality=94, subsampling=0)
            print(f"  {i:02d}.jpg")

    print(f"완료 → {out}/  ({n}장)")


if __name__ == "__main__":
    main()
