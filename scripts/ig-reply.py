# -*- coding: utf-8 -*-
"""댓글에 키워드가 오면 공개 답글 + DM 으로 템플릿을 보낸다.

한 번 처리한 댓글은 state 파일에 남겨 두 번 보내지 않는다.
발행 규약과 같은 원칙 — 응답이 아니라 기록이 사실이다.
"""
import json, os, re, sys, time, urllib.parse, urllib.request

ROOT = os.path.expanduser('~/Workspace/blog')
STATE = os.path.join(ROOT, 'scripts/.ig-replied.json')
API = 'https://graph.instagram.com/v23.0'
# 키워드는 글의 캡션에서 읽는다. 캡션의 「…」 안에 쓴 단어가 그 글의 키워드다.
# 글마다 달라져도 이 파일을 고칠 일이 없다.
KEYWORD_FALLBACK = '여름'
KEYWORD_RE = re.compile(r'[「『]\s*([^」』\n]{1,12})\s*[」』]')

# 환경변수가 먼저다. Actions 에는 .env.local 이 없다.
ENV = {}
_envf = os.path.join(ROOT, '.env.local')
if os.path.exists(_envf):
    for line in open(_envf, encoding='utf-8'):
        if '=' in line and not line.startswith('#'):
            k, v = line.rstrip('\n').split('=', 1); ENV[k] = v
TOKEN = os.environ.get('INSTAGRAM_ACCESS_TOKEN') or ENV.get('INSTAGRAM_ACCESS_TOKEN', '')
UID = os.environ.get('INSTAGRAM_USER_ID') or ENV.get('INSTAGRAM_USER_ID', '')
if not TOKEN or not UID:
    print('  ✗ INSTAGRAM_ACCESS_TOKEN / INSTAGRAM_USER_ID 가 없다'); sys.exit(2)

DM = """👻 여름 배경화면 템플릿, 두고 갑니다.

gemini.google.com 에 그대로 붙여넣고 { } 안 7칸만 바꾸면 됩니다.

A full-bleed vertical phone wallpaper of {subject}, {setting}. Style: {style}. Mood: {mood}. Color palette: {colors}. Lighting: {lighting}. Composition: extreme vertical 9:19.5 aspect ratio, edge-to-edge composition that fills the entire frame from the very top to the very bottom. The {focal} is positioned in the lower third, leaving clean negative space in the upper third for clock and widgets. No borders, no frame, no margins, no text, no watermark, no UI elements, no phone frame, no device bezel. Ultra-detailed, high resolution, 4K."""

DM2 = """채우는 예시입니다.

{subject}  언덕 위 사이프러스 한 그루
{setting}  노을 진 그라데이션 하늘 아래
{style}    미니멀 일러스트
{mood}     차분하고 명상적인
{colors}   뮤트 세이지 + 크림
{lighting} 부드러운 골든아워 역광
{focal}    수평선

위쪽을 비우라는 줄을 빼면 시계에 다 가려요. 그 줄이 핵심입니다 🌊"""

PUBLIC_REPLY = 'DM 으로 템플릿 보내드렸어요 👻'

def api(path, params=None, post=False):
    p = dict(params or {}); p['access_token'] = TOKEN
    if post:
        req = urllib.request.Request(f'{API}/{path}', data=urllib.parse.urlencode(p).encode())
    else:
        req = urllib.request.Request(f'{API}/{path}?' + urllib.parse.urlencode(p))
    try:
        with urllib.request.urlopen(req, timeout=45) as r: return json.load(r)
    except urllib.error.HTTPError as e:
        try: return json.load(e)
        except Exception: return {'error': {'message': f'HTTP {e.code}'}}

def load(): return json.load(open(STATE)) if os.path.exists(STATE) else {}
def save(s): json.dump(s, open(STATE, 'w'), ensure_ascii=False, indent=1)

def keyword_of(media_id):
    r = api(media_id, {'fields': 'caption'})
    m = KEYWORD_RE.search(r.get('caption') or '')
    return (m.group(1).strip() if m else KEYWORD_FALLBACK)

def run(media_id, dry=False):
    seen = load()
    kw = keyword_of(media_id)
    r = api(f'{media_id}/comments', {'fields': 'id,text,username,timestamp', 'limit': '50'})
    if 'error' in r:
        print('  ✗ 댓글 조회 실패:', json.dumps(r['error'], ensure_ascii=False)[:300]); return 1
    rows = r.get('data', [])
    hits = [c for c in rows if kw in (c.get('text') or '')]
    print(f'  키워드 「{kw}」 · 댓글 {len(rows)}개 · 해당 {len(hits)}개 · 이미 처리 {len(seen)}건')
    if not rows:
        print('  (주인 계정이 단 댓글은 API 목록에 안 나온다. 다른 계정으로 시험한다)')
    sent = 0
    for c in hits:
        cid = c['id']
        if cid in seen:
            print(f'   - @{c.get("username")} {cid} 이미 보냄 · 건너뜀'); continue
        if dry:
            print(f'   ~ @{c.get("username")} {cid} 보낼 예정 (dry-run)'); continue
        m1 = api(f'{UID}/messages', {'recipient': json.dumps({'comment_id': cid}),
                                     'message': json.dumps({'text': DM})}, post=True)
        if 'error' in m1:
            print(f'   ✗ @{c.get("username")} DM 실패:', json.dumps(m1['error'], ensure_ascii=False)[:200]); continue
        time.sleep(1)
        api(f'{UID}/messages', {'recipient': json.dumps({'comment_id': cid}),
                                'message': json.dumps({'text': DM2})}, post=True)
        rep = api(f'{cid}/replies', {'message': PUBLIC_REPLY}, post=True)
        seen[cid] = {'user': c.get('username'), 'at': time.strftime('%Y-%m-%dT%H:%M:%S'),
                     'dm': m1.get('message_id', ''), 'reply': rep.get('id', '')}
        save(seen); sent += 1
        print(f'   ✓ @{c.get("username")} DM + 공개답글 완료')
        time.sleep(2)
    print(f'  이번 실행: {sent}건 발송')
    return 0

def recent_media(n=5):
    r = api(f'{UID}/media', {'fields': 'id,timestamp,permalink', 'limit': str(n)})
    if 'error' in r:
        print('  ✗ 게시물 조회 실패:', json.dumps(r['error'], ensure_ascii=False)[:300]); return []
    return r.get('data', [])

if __name__ == '__main__':
    dry = '--dry' in sys.argv
    args = [a for a in sys.argv[1:] if not a.startswith('-')]
    targets = args or [m['id'] for m in recent_media(5)]
    if not targets:
        print('  대상 글이 없다'); sys.exit(0)
    rc = 0
    for mid in targets:
        print(f'\n▸ media {mid}')
        rc |= run(mid, dry=dry)
    sys.exit(rc)
