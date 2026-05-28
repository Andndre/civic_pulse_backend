# Sistem Desain (Design System) - CivicPulse

Dokumen ini mendefinisikan token warna yang digunakan di seluruh aplikasi CivicPulse (Mobile & Web) untuk memastikan konsistensi visual.

## Token Warna (Color Tokens)

| Token | Value | Hex Preview | Usage |
|-------|-------|-------------|-------|
| **primary** | `#2196F3` | ![#2196F3](https://via.placeholder.com/15/2196F3/000000?text=+) | CTA (Call-to-Action), headers, navigasi aktif |
| **secondary** | `#FFC107` | ![#FFC107](https://via.placeholder.com/15/FFC107/000000?text=+) | Aksen, sorotan (*highlights*) |
| **background** | `#F8F9FA` | ![#F8F9FA](https://via.placeholder.com/15/F8F9FA/000000?text=+) | Latar belakang aplikasi (*app background*) |
| **surface** | `#FFFFFF` | ![#FFFFFF](https://via.placeholder.com/15/FFFFFF/000000?text=+) | Kartu (*cards*), kontainer data |
| **textPrimary** | `#333333` | ![#333333](https://via.placeholder.com/15/333333/000000?text=+) | Judul (*headings*), teks utama |
| **textSecondary** | `#757575` | ![#757575](https://via.placeholder.com/15/757575/000000?text=+) | Label, keterangan gambar/teks sekunder |
| **success** | `#4CAF50` | ![#4CAF50](https://via.placeholder.com/15/4CAF50/000000?text=+) | Indikator aman, partisipasi tinggi, heatmap OK |
| **warning** | `#FF9800` | ![#FF9800](https://via.placeholder.com/15/FF9800/000000?text=+) | Indikator peringatan/perhatian, heatmap warning |
| **danger** | `#F44336` | ![#F44336](https://via.placeholder.com/15/F44336/000000?text=+) | Indikator kritis/skor rendah, heatmap danger |

## Panduan Penggunaan Warna

1. **Konsistensi Visual**: Jangan menggunakan warna hex ad-hoc di luar tabel ini. Semua komponen UI harus merujuk ke token ini.
2. **Keterbacaan Kontras**: Pastikan teks di atas warna latar belakang memiliki kontras yang cukup tinggi sesuai standar aksesibilitas WCAG (misalnya, teks putih di atas tombol `primary` biru).
3. **Analitik Transparansi**: Saat membuat tabel heatmap atau diagram bertingkat, gunakan opacity rendah (20-30%) dari warna `success`, `warning`, dan `danger` agar angka di dalam sel tetap mudah dibaca.
