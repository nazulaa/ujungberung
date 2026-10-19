# 🌿 Website Desa Ujungberung — Majalengka

Website resmi Desa Ujungberung, Kecamatan Ujungberung, Kabupaten Majalengka, Jawa Barat.

---

## 📁 Struktur Project

```
desa-ujungberung/
├── index.html          ← Halaman utama
├── css/
│   └── style.css       ← Semua styling
├── js/
│   └── script.js       ← Interaktivitas & animasi
├── images/             ← Taruh foto-foto desa di sini
│   └── (kosong — isi dengan foto)
└── README.md
```

---

## 🚀 Cara Menggunakan di VS Code

1. Buka VS Code
2. Klik **File → Open Folder** → pilih folder `desa-ujungberung`
3. Install ekstensi **Live Server** (klik Extensions icon → cari "Live Server")
4. Klik kanan `index.html` → **Open with Live Server**
5. Website akan terbuka di browser!

---

## ✏️ Cara Mengedit Konten

### Ganti Nama Kepala Desa
Di `index.html`, cari teks `[Nama Kepala Desa]` dan ganti dengan nama sebenarnya.

### Foto Kepala Desa
Ganti bagian `<div class="photo-placeholder">` dengan:
```html
<img src="images/kades.jpg" alt="Kepala Desa" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
```

### Nomor Telepon & Kontak
Cari bagian kontak di `index.html` dan isi dengan informasi yang benar.

### Tambah Video Profil
- Cara mudah: Di website, scroll ke bagian **Video Profil** → paste link YouTube → klik **Muat Video**
- Cara permanen: Di `index.html`, cari `data-url=""` pada video-item pertama dan isi dengan link YouTube

### Tambah/Ganti Foto Galeri
1. Taruh foto di folder `images/`
2. Di `index.html`, cari `.gal-placeholder` dan ganti dengan:
```html
<div class="galeri-item">
  <img src="images/nama-foto.jpg" alt="Deskripsi" style="width:100%;height:100%;object-fit:cover;">
</div>
```

### Update Berita
Di `index.html`, cari bagian `<!-- BERITA & PENGUMUMAN -->` dan edit atau tambah card berita sesuai kebutuhan.

### Update Peta (Google Maps)
Di bagian kontak, ganti koordinat di URL iframe Google Maps dengan koordinat desa yang tepat.

---

## 🎨 Warna Tema

| Nama         | Kode Hex |
|--------------|----------|
| Primary Dark | #1B5E20  |
| Primary      | #2E7D32  |
| Primary Light| #4CAF50  |
| Accent       | #F9A825  |

---

## 📞 Butuh Bantuan?

Hubungi developer atau edit langsung file `index.html` dan `css/style.css` menggunakan VS Code.

---

*Dibuat dengan ❤️ untuk Desa Ujungberung*
