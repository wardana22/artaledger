---
name: ui-ux-pro-max
description: Desain UI/UX tingkat lanjut, Responsive Sidebar Navigation, mikro-animasi interaktif, header glassmorphic, glowing status pills, dan aksesibilitas ARIA.
repository: https://github.com/nextlevelbuilder/ui-ux-pro-max-skill
---

# UI/UX Pro Max - ArtaLedger

Skill ini digunakan untuk merancang antarmuka pengguna ArtaLedger yang elegan, modern, dan berkelas enterprise.

## 🎨 Panduan Desain
- **Palette**: Dark Mode / Modern Slate & Indigo Accents.
- **Glassmorphism**: Backdrop blur pada header & modal.
- **Micro-Animations**: Transisi halus pada tombol, hover card, dan chart widget.
- **Dynamic Action Icons Rule (Wajib)**:
  - **Kondisi Normal / Diam (Default)**: Seluruh tombol ikon aksi (Edit, Delete, View, Post, Reverse) HARUS berwarna netral selaras tema terang/gelap (`text-slate-400 dark:text-slate-400`) dengan kontainer netral yang lembut (`bg-slate-100/60 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 shadow-2xs`).
  - **Kondisi Disorot Kursor (Solid Fill Hover State)**: Bertransisi halus (`transition-all duration-200`) menjadi warna solid dengan teks putih dan bayangan berpijar (*vivid shadow*):
    - Edit: `hover:bg-indigo-600 hover:text-white hover:border-indigo-600 shadow-2xs hover:shadow-md hover:shadow-indigo-500/20`
    - Hapus / Delete: `hover:bg-rose-600 hover:text-white hover:border-rose-600 shadow-2xs hover:shadow-md hover:shadow-rose-500/20`
    - Reverse: `hover:bg-amber-500 hover:text-white hover:border-amber-500 shadow-2xs hover:shadow-md hover:shadow-amber-500/20`
    - Post / Approve: `hover:bg-emerald-600 hover:text-white hover:border-emerald-600 shadow-2xs hover:shadow-md hover:shadow-emerald-500/20`
    - View / Detail: `hover:bg-sky-600 hover:text-white hover:border-sky-600 shadow-2xs hover:shadow-md hover:shadow-sky-500/20`
- **Aksesibilitas (a11y)**: Atribut ARIA, kontras warna tinggi, dan navigasi keyboard.
