#!/usr/bin/env python3
"""Membuat data wilayah untuk dropdown bertingkat di public/data/wilayah.

Pemakaian:
  python tools/build-wilayah.py            -> semua provinsi (lama)
  python tools/build-wilayah.py 63 35      -> hanya provinsi dengan kode 63 dan 35
"""
import json, os, sys, time
from concurrent.futures import ThreadPoolExecutor
from urllib.request import Request, urlopen

API = "https://emsifa.github.io/api-wilayah-indonesia/api"
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "public", "data", "wilayah")
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36",
    "Accept": "application/json",
}


def get(path, tries=6):
    for i in range(tries):
        try:
            req = Request(f"{API}/{path}.json", headers=HEADERS)
            with urlopen(req, timeout=30) as r:
                return json.load(r)
        except Exception as e:
            if i == tries - 1:
                raise RuntimeError(f"Gagal mengambil {path}: {e}")
            time.sleep(2 * (i + 1))


def rows(items):
    # Format yang dibaca formulir: [kode, nama]
    return [[x["id"], x["name"].title() if x["name"].isupper() else x["name"]] for x in items]


def save(rel, data):
    path = os.path.join(OUT, rel)
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, separators=(",", ":"))


def load_existing(rel):
    path = os.path.join(OUT, rel)
    if os.path.exists(path):
        with open(path, encoding="utf-8") as f:
            return json.load(f)
    return None


def pmap(fn, items):
    with ThreadPoolExecutor(max_workers=6) as ex:
        return list(ex.map(fn, items))


only = set(sys.argv[1:])

print("Provinsi...")
provs = get("provinces")
save("provinces.json", rows(provs))  # daftar provinsi selalu lengkap
if only:
    provs = [p for p in provs if p["id"] in only]
    if not provs:
        sys.exit("Kode provinsi tidak dikenal. Contoh: 63 (Kalimantan Selatan), 35 (Jawa Timur)")
    print("Hanya memproses:", ", ".join(p["name"] for p in provs))

print("Kabupaten/Kota...")
regs_by_prov = dict(zip([p["id"] for p in provs], pmap(lambda p: get(f"regencies/{p['id']}"), provs)))
merged = load_existing("regencies.json") or {}
merged.update({k: rows(v) for k, v in regs_by_prov.items()})
save("regencies.json", merged)
regs = [r for v in regs_by_prov.values() for r in v]

print(f"Kecamatan ({len(regs)} kabupaten/kota)...")
dists_by_reg = dict(zip([r["id"] for r in regs], pmap(lambda r: get(f"districts/{r['id']}"), regs)))
by_prov = {}
for reg_id, items in dists_by_reg.items():
    by_prov.setdefault(reg_id[:2], {})[reg_id] = rows(items)
for pid, data in by_prov.items():
    save(f"districts/{pid}.json", data)
dists = [d for v in dists_by_reg.values() for d in v]

print(f"Desa/Kelurahan ({len(dists)} kecamatan)...")
vills_by_dist = dict(zip([d["id"] for d in dists], pmap(lambda d: get(f"villages/{d['id']}"), dists)))
by_reg = {}
for dist_id, items in vills_by_dist.items():
    by_reg.setdefault(dist_id[:4], {})[dist_id] = rows(items)
for rid, data in by_reg.items():
    save(f"villages/{rid}.json", data)

print("Selesai ->", os.path.abspath(OUT))
