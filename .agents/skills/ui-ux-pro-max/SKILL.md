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
  - **Kondisi Disorot Kursor (Hover)**: Bertransisi halus (`transition-all duration-200`) menyala ke warna semantik ber-latar transparan:
    - Edit: `hover:bg-indigo-500/10 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-500/30`
    - Hapus / Delete: `hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-500/30`
    - Reverse: `hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 hover:border-amber-500/30`
    - Post / Approve: `hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400 hover:border-emerald-500/30`
    - View / Detail: `hover:bg-sky-500/10 hover:text-sky-600 dark:hover:text-sky-400 hover:border-sky-500/30`
- **Aksesibilitas (a11y)**: Atribut ARIA, kontras warna tinggi, dan navigasi keyboard.
