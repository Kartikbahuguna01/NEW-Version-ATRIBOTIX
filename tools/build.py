#!/usr/bin/env python3
"""build.py — creates dist/, the folder you upload to the server.

  python3 tools/build.py

What it does: copies the site into dist/ and minifies css/*.css and js/*.js
(comments and extra whitespace removed). Everything else is copied unchanged.
The readable source files stay in css/ and js/ — edit those, then rebuild.
"""
import re, shutil
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DIST = ROOT / 'dist'
SKIP = {'dist', 'tools', 'README.md', 'AUDIT-REPORT.md', '.git'}


def minify_css(css: str) -> str:
    css = re.sub(r'/\*.*?\*/', '', css, flags=re.S)
    css = re.sub(r'\s+', ' ', css)
    css = re.sub(r'\s*([{};,])\s*', r'\1', css)      # around braces, semicolons, commas
    css = re.sub(r':\s+', ':', css)                  # after a colon (declarations only)
    return css.replace(';}', '}').strip()


def minify_js(js: str) -> str:
    js = re.sub(r'/\*.*?\*/', '', js, flags=re.S)
    lines = []
    for line in js.splitlines():
        line = re.sub(r'(?<=\s)//\s.*$', '', line)     # trailing // comments
        line = line.strip()
        if line and not line.startswith('//'):
            lines.append(line)
    return '\n'.join(lines) + '\n'


def main():
    if DIST.exists():
        shutil.rmtree(DIST)
    for item in ROOT.iterdir():
        if item.name in SKIP:
            continue
        target = DIST / item.name
        if item.is_dir():
            shutil.copytree(item, target)
        else:
            DIST.mkdir(exist_ok=True)
            shutil.copy2(item, target)
    for css in (DIST / 'css').glob('*.css'):
        css.write_text(minify_css(css.read_text()))
    for js in (DIST / 'js').glob('*.js'):
        js.write_text(minify_js(js.read_text()))
    before = sum(p.stat().st_size for p in list((ROOT / 'css').glob('*.css')) + list((ROOT / 'js').glob('*.js')))
    after = sum(p.stat().st_size for p in list((DIST / 'css').glob('*.css')) + list((DIST / 'js').glob('*.js')))
    print(f'dist/ ready. CSS+JS: {before // 1024} KB -> {after // 1024} KB')


if __name__ == '__main__':
    main()
