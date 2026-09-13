import sys
import os
import pptx
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

def create_presentation_5w1h(output_path):
    prs = Presentation()
    # 16:9 widescreen slides: 13.333 x 7.5 inches
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)

    blank_slide_layout = prs.slide_layouts[6]

    # Color Palette
    COLOR_BG_DARK = RGBColor(15, 23, 42)       # Slate 900
    COLOR_BG_LIGHT = RGBColor(248, 250, 252)   # Slate 50
    COLOR_CARD_DARK = RGBColor(30, 41, 59)     # Slate 800
    COLOR_CARD_BORDER = RGBColor(51, 65, 85)   # Slate 700
    COLOR_CARD_LIGHT = RGBColor(255, 255, 255) # White
    COLOR_ACCENT = RGBColor(99, 102, 241)      # Indigo 500
    COLOR_PRIMARY = RGBColor(30, 41, 59)       # Slate 800
    COLOR_EMERALD = RGBColor(16, 185, 129)     # Emerald 500
    COLOR_AMBER = RGBColor(245, 158, 11)       # Amber 500
    COLOR_RED = RGBColor(239, 68, 68)          # Red 500
    COLOR_TEXT_MUTED = RGBColor(148, 163, 184) # Slate 400
    COLOR_TEXT_LIGHT = RGBColor(255, 255, 255)
    COLOR_TEXT_BODY = RGBColor(71, 85, 105)    # Slate 600

    def add_bg(slide, dark=True):
        bg = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
        bg.fill.solid()
        bg.fill.fore_color.rgb = COLOR_BG_DARK if dark else COLOR_BG_LIGHT
        bg.line.fill.background()
        return bg

    def add_header(slide, title_text, category_text, dark=False):
        header_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.5), Inches(11.7), Inches(1.1))
        tf = header_box.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
        
        p_cat = tf.paragraphs[0]
        p_cat.text = category_text.upper()
        p_cat.font.name = "Segoe UI"
        p_cat.font.size = Pt(11)
        p_cat.font.bold = True
        p_cat.font.color.rgb = COLOR_ACCENT
        p_cat.space_after = Pt(2)

        p_title = tf.add_paragraph()
        p_title.text = title_text
        p_title.font.name = "Segoe UI"
        p_title.font.size = Pt(23)
        p_title.font.bold = True
        p_title.font.color.rgb = COLOR_TEXT_LIGHT if dark else COLOR_PRIMARY

    def add_card(slide, x, y, w, h, bg_color=COLOR_CARD_LIGHT, border_color=None):
        card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(x), Inches(y), Inches(w), Inches(h))
        card.fill.solid()
        card.fill.fore_color.rgb = bg_color
        if border_color:
            card.line.color.rgb = border_color
            card.line.width = Pt(1.5)
        else:
            card.line.fill.background()
        return card

    # ==========================================
    # SLIDE 1: Title Slide (Dark Theme)
    # ==========================================
    s1 = prs.slides.add_slide(blank_slide_layout)
    add_bg(s1, dark=True)

    strip = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), Inches(1.1), Inches(1.8), Inches(0.08))
    strip.fill.solid()
    strip.fill.fore_color.rgb = COLOR_ACCENT
    strip.line.fill.background()

    tbox = s1.shapes.add_textbox(Inches(0.8), Inches(1.35), Inches(11.7), Inches(3.6))
    tf = tbox.text_frame
    tf.word_wrap = True
    
    p_badge = tf.paragraphs[0]
    p_badge.text = "KERANGKA ANALISA 5W + 1H • ENTERPRISE ERP SYSTEM"
    p_badge.font.name = "Segoe UI"
    p_badge.font.size = Pt(13)
    p_badge.font.bold = True
    p_badge.font.color.rgb = COLOR_ACCENT
    p_badge.space_after = Pt(8)

    p_main = tf.add_paragraph()
    p_main.text = "ARTALEDGER"
    p_main.font.name = "Segoe UI"
    p_main.font.size = Pt(46)
    p_main.font.bold = True
    p_main.font.color.rgb = COLOR_TEXT_LIGHT
    p_main.space_after = Pt(10)

    p_sub = tf.add_paragraph()
    p_sub.text = "Presentasi Komprehensif Sistem Informasi Akuntansi & Tata Kelola Keuangan Korporat Modern"
    p_sub.font.name = "Segoe UI"
    p_sub.font.size = Pt(18)
    p_sub.font.color.rgb = COLOR_TEXT_MUTED

    # Bottom Metadata Cards
    metas = [
        ("Metodologi Analisa", "Pendekatan Terstruktur 5W + 1H", COLOR_ACCENT),
        ("Status Kesiapan", "🟢 Production-Ready (201 Tests Passed)", COLOR_EMERALD),
        ("Kepatuhan Standar", "PSAK Compliant, Double-Entry & Audit Locking", COLOR_AMBER)
    ]
    for idx, (label, val, col) in enumerate(metas):
        cx = 0.8 + idx * 4.0
        add_card(s1, cx, 5.4, 3.7, 1.3, bg_color=COLOR_CARD_DARK, border_color=COLOR_CARD_BORDER)
        cbox = s1.shapes.add_textbox(Inches(cx + 0.25), Inches(5.55), Inches(3.2), Inches(1.0))
        ctf = cbox.text_frame
        ctf.word_wrap = True
        cp1 = ctf.paragraphs[0]
        cp1.text = label.upper()
        cp1.font.name = "Segoe UI"
        cp1.font.size = Pt(10)
        cp1.font.bold = True
        cp1.font.color.rgb = col
        cp1.space_after = Pt(2)
        cp2 = ctf.add_paragraph()
        cp2.text = val
        cp2.font.name = "Segoe UI"
        cp2.font.size = Pt(11.5)
        cp2.font.color.rgb = COLOR_TEXT_LIGHT

    # ==========================================
    # SLIDE 2: 5W+1H Overview Matrix
    # ==========================================
    s2 = prs.slides.add_slide(blank_slide_layout)
    add_bg(s2, dark=False)
    add_header(s2, "Matriks Eksekutif Analisis Sistem ArtaLedger", "KERANGKA KERJA 5W + 1H")

    matrix_items = [
        ("WHAT", "Apa itu ArtaLedger?", "Sistem Akuntansi ERP modern berstandar PSAK dengan Core Double-Entry presisi tinggi, modul rekonsiliasi perbankan, umur piutang/hutang, dan manajemen aset tetap.", COLOR_ACCENT),
        ("WHY", "Mengapa Sangat Dibutuhkan?", "Mengeliminasi risiko human error penjurnalan, deviasi format kertas kerja auditor, keterlambatan rekonsiliasi manual perbankan, dan ancaman manipulasi periode.", COLOR_RED),
        ("WHO", "Siapa Pemangku Kepentingannya?", "Jajaran Direksi & C-Level (visibilitas KPI finansial), Tim Finansial/Akuntan (otomasi pembukuan & audit), serta Tim IT (keamanan, stabilitas, dan RBAC).", COLOR_EMERALD),
        ("WHERE", "Di Mana Diimplementasikan?", "Dideploy di seluruh unit operasional korporat (Kantor Pusat & Unit Bisnis Cabang) via antarmuka web modern reaktif dan server cloud/on-premise aman.", COLOR_AMBER),
        ("WHEN", "Kapan Roadmap & Tahapannya?", "Fondasi akuntansi siap produksi saat ini (2026), dilanjutkan roadmap 4 fase strategis sepanjang 2026-2027 menuju Ekosistem ERP Bisnis Lengkap.", COLOR_ACCENT),
        ("HOW", "Bagaimana Cara Kerjanya?", "Menggunakan arsitektur ACID Transactions, balancing |Δ| < 0.01, formula dinamis 12 KPI, dan jaminan mutu 201 pengujian otomatis Pest PHP (100% Passed).", COLOR_EMERALD)
    ]
    for idx, (q, sub, desc, col) in enumerate(matrix_items):
        cx = 0.8 + (idx % 3) * 3.95
        cy = 1.9 + (idx // 3) * 2.5
        add_card(s2, cx, cy, 3.8, 2.25, bg_color=COLOR_CARD_LIGHT, border_color=RGBColor(226, 232, 240))
        box = s2.shapes.add_textbox(Inches(cx + 0.25), Inches(cy + 0.2), Inches(3.3), Inches(1.85))
        btf = box.text_frame
        btf.word_wrap = True
        
        p1 = btf.paragraphs[0]
        p1.text = q
        p1.font.name = "Segoe UI"
        p1.font.size = Pt(16)
        p1.font.bold = True
        p1.font.color.rgb = col
        p1.space_after = Pt(2)

        p2 = btf.add_paragraph()
        p2.text = sub
        p2.font.name = "Segoe UI"
        p2.font.size = Pt(11)
        p2.font.bold = True
        p2.font.color.rgb = COLOR_PRIMARY
        p2.space_after = Pt(4)

        p3 = btf.add_paragraph()
        p3.text = desc
        p3.font.name = "Segoe UI"
        p3.font.size = Pt(10)
        p3.font.color.rgb = COLOR_TEXT_BODY

    # ==========================================
    # SLIDE 3: WHAT - Entitas & Modul Fungsional
    # ==========================================
    s3 = prs.slides.add_slide(blank_slide_layout)
    add_bg(s3, dark=False)
    add_header(s3, "WHAT: Apa Saja Modul Fungsional & Fondasi ArtaLedger?", "DIMENSI 1: WHAT (ENTITAS SISTEM)")

    what_modules = [
        ("1. Dasbor Finansial Eksekutif", "12 KPI Baku (EBITDA, NPM, DER, SGA/COGS to Sales, ITO, DSI), ApexCharts tren 12 bulan interaktif, tabel transaksi bernilai signifikan, dan sinkronisasi filter periode global."),
        ("2. Core Pelaporan Kepatuhan PSAK", "Neraca Lajur 10-Kolom Strict Normal Balance Placement (Excel Parity 100%), Laba Rugi Komprehensif (OCI PSAK 24), Arus Kas Langsung dinamis, dan Saldo Awal terkunci."),
        ("3. Rekonsiliasi Bank Otomatis (CMS BRI)", "Ekstraksi otomatis mutasi rekening koran PDF e-Tax/CMS BRI, matching otomatis 1-ke-1 dan 1-ke-N dengan jurnal kas, serta pelaporan berita acara selisih."),
        ("4. Aging AR/AP & Aset Tetap Terintegrasi", "Pelacakan umur piutang/hutang dengan Smart Settlement M:N, kalkulasi depresiasi aset otomatis (Garis Lurus & Saldo Menurun), cetak Barcode/QR Code, dan verifikasi fisik.")
    ]
    for idx, (title, desc) in enumerate(what_modules):
        cx = 0.8 + (idx % 2) * 5.95
        cy = 1.9 + (idx // 2) * 2.5
        add_card(s3, cx, cy, 5.75, 2.25, bg_color=COLOR_CARD_LIGHT, border_color=RGBColor(226, 232, 240))
        box = s3.shapes.add_textbox(Inches(cx + 0.3), Inches(cy + 0.25), Inches(5.15), Inches(1.8))
        btf = box.text_frame
        btf.word_wrap = True
        
        p1 = btf.paragraphs[0]
        p1.text = title
        p1.font.name = "Segoe UI"
        p1.font.size = Pt(14)
        p1.font.bold = True
        p1.font.color.rgb = COLOR_PRIMARY
        p1.space_after = Pt(6)

        p2 = btf.add_paragraph()
        p2.text = desc
        p2.font.name = "Segoe UI"
        p2.font.size = Pt(11)
        p2.font.color.rgb = COLOR_TEXT_BODY

    # ==========================================
    # SLIDE 4: WHY - Latar Belakang Masalah & Solusi
    # ==========================================
    s4 = prs.slides.add_slide(blank_slide_layout)
    add_bg(s4, dark=False)
    add_header(s4, "WHY: Mengapa ArtaLedger Sangat Dibutuhkan Korporasi?", "DIMENSI 2: WHY (NILAI URGENSI BISNIS)")

    why_cards = [
        ("Human Error & Jurnal Selisih", "Pencatatan manual sering memicu selisih debit-kredit dan salah akun.", "Strict Balancing Rule (|Δ| < 0.01) & blokir posting akun Header."),
        ("Kertas Kerja Tidak Cocok dengan Auditor", "Neraca lajur software umum formatnya melompat dan tidak standar.", "Strict Normal Balance Placement: Sinkron 100% lembar kerja Excel auditor."),
        ("Rekonsiliasi Bank Memakan Waktu Berhari-hari", "Pencocokan rekening koran manual sangat lambat setiap akhir bulan.", "Auto-Parser BRI CMS: Ekstraksi otomatis data PDF & auto-matching."),
        ("Risiko Fraud & Manipulasi Periode Lalu", "Transaksi periode lalu masih dapat diubah tanpa jejak pengawasan.", "Period Locking & Audit Trail: Penguncian resmi & izin sandi Super Admin.")
    ]
    for idx, (prob_title, prob_desc, sol_desc) in enumerate(why_cards):
        cx = 0.8 + (idx % 2) * 5.95
        cy = 1.9 + (idx // 2) * 2.5
        add_card(s4, cx, cy, 5.75, 2.25, bg_color=COLOR_CARD_LIGHT, border_color=RGBColor(226, 232, 240))
        box = s4.shapes.add_textbox(Inches(cx + 0.3), Inches(cy + 0.2), Inches(5.15), Inches(1.85))
        btf = box.text_frame
        btf.word_wrap = True
        
        p1 = btf.paragraphs[0]
        p1.text = prob_title
        p1.font.name = "Segoe UI"
        p1.font.size = Pt(13.5)
        p1.font.bold = True
        p1.font.color.rgb = COLOR_PRIMARY
        p1.space_after = Pt(4)

        p2 = btf.add_paragraph()
        p2.text = f"❌ Masalah: {prob_desc}"
        p2.font.name = "Segoe UI"
        p2.font.size = Pt(11)
        p2.font.color.rgb = COLOR_RED
        p2.space_after = Pt(3)

        p3 = btf.add_paragraph()
        p3.text = f"✅ Solusi: {sol_desc}"
        p3.font.name = "Segoe UI"
        p3.font.size = Pt(11)
        p3.font.bold = True
        p3.font.color.rgb = COLOR_EMERALD

    # ==========================================
    # SLIDE 5: WHO - Pemangku Kepentingan
    # ==========================================
    s5 = prs.slides.add_slide(blank_slide_layout)
    add_bg(s5, dark=False)
    add_header(s5, "WHO: Siapa Pemangku Kepentingan & Manfaat yang Didapat?", "DIMENSI 3: WHO (STAKEHOLDERS MATRIX)")

    who_stakeholders = [
        ("Direksi & C-Level (CEO/CFO)", "Kontrol Finansial Real-Time", "Mendapatkan visibilitas menyeluruh melalui 12 KPI eksekutif, grafik tren ApexCharts 12 bulan, dan kontrol transaksi bernilai signifikan tanpa menunggu laporan bulanan akuntan.", COLOR_EMERALD),
        ("Tim Finansial & Akuntan", "Efisiensi 80% & Kesiapan Audit", "Eliminasi salah posting jurnal, kemudahan rekonsiliasi mutasi bank otomatis, serta format neraca lajur yang sinkron 100% dengan lembar kerja auditor independen.", COLOR_ACCENT),
        ("Tim IT & Administrator Sistem", "Keamanan, Stabilitas & Audit Log", "Platform modern berbasis Laravel 12 & Livewire 3 yang modular, proteksi Spatie RBAC bertingkat, kepatuhan ACID, dan 201 pengujian otomatis Pest PHP (100% Passed).", COLOR_AMBER)
    ]
    for idx, (role, tagline, desc, col) in enumerate(who_stakeholders):
        cx = 0.8 + idx * 3.95
        add_card(s5, cx, 1.9, 3.8, 4.8, bg_color=COLOR_CARD_LIGHT, border_color=RGBColor(226, 232, 240))
        box = s5.shapes.add_textbox(Inches(cx + 0.3), Inches(2.2), Inches(3.2), Inches(4.2))
        btf = box.text_frame
        btf.word_wrap = True
        
        p_badge = btf.paragraphs[0]
        p_badge.text = f"PERAN 0{idx+1}"
        p_badge.font.name = "Segoe UI"
        p_badge.font.size = Pt(10.5)
        p_badge.font.bold = True
        p_badge.font.color.rgb = col
        p_badge.space_after = Pt(4)

        p_title = btf.add_paragraph()
        p_title.text = role
        p_title.font.name = "Segoe UI"
        p_title.font.size = Pt(14)
        p_title.font.bold = True
        p_title.font.color.rgb = COLOR_PRIMARY
        p_title.space_after = Pt(2)

        p_tag = btf.add_paragraph()
        p_tag.text = tagline
        p_tag.font.name = "Segoe UI"
        p_tag.font.size = Pt(11)
        p_tag.font.bold = True
        p_tag.font.color.rgb = col
        p_tag.space_after = Pt(8)

        p_desc = btf.add_paragraph()
        p_desc.text = desc
        p_desc.font.name = "Segoe UI"
        p_desc.font.size = Pt(11)
        p_desc.font.color.rgb = COLOR_TEXT_BODY

    # ==========================================
    # SLIDE 6: WHERE & WHEN - Lingkup & Roadmap
    # ==========================================
    s6 = prs.slides.add_slide(blank_slide_layout)
    add_bg(s6, dark=False)
    add_header(s6, "WHERE & WHEN: Lingkup Operasional & Roadmap Strategis", "DIMENSI 4 & 5: WHERE & WHEN")

    # Left Column: WHERE (4.5 inches width)
    add_card(s6, 0.8, 1.9, 4.3, 4.8, bg_color=COLOR_CARD_LIGHT, border_color=RGBColor(226, 232, 240))
    wbox = s6.shapes.add_textbox(Inches(1.05), Inches(2.15), Inches(3.8), Inches(4.3))
    wtf = wbox.text_frame
    wtf.word_wrap = True
    
    wp1 = wtf.paragraphs[0]
    wp1.text = "WHERE: LINGKUP OPERASIONAL"
    wp1.font.name = "Segoe UI"
    wp1.font.size = Pt(13)
    wp1.font.bold = True
    wp1.font.color.rgb = COLOR_ACCENT
    wp1.space_after = Pt(8)

    where_points = [
        ("Kantor Pusat (KP)", "Konsolidasi laporan keuangan, kontrol bagan akun (COA terpusat), dan kebijakan saldo awal."),
        ("Unit Usaha & Cabang", "Pencatatan mutasi transaksi kas, operasional klinik/rumah sakit, dan verifikasi aset lokal."),
        ("Auditor & Remote Akses", "Akses read-only laporan keuangan dan neraca lajur melalui jalur aman."),
        ("Infrastruktur Multi-Cloud", "Dapat dihosting pada Server On-Premise maupun Cloud VPS (AWS, DigitalOcean, Alibaba).")
    ]
    for w_title, w_desc in where_points:
        p = wtf.add_paragraph()
        p.text = f"• {w_title}: {w_desc}"
        p.font.name = "Segoe UI"
        p.font.size = Pt(10)
        p.font.color.rgb = COLOR_TEXT_BODY
        p.space_after = Pt(4)

    # Right Column: WHEN (7.1 inches width)
    add_card(s6, 5.4, 1.9, 7.1, 4.8, bg_color=COLOR_CARD_LIGHT, border_color=RGBColor(226, 232, 240))
    tbox = s6.shapes.add_textbox(Inches(5.65), Inches(2.15), Inches(6.6), Inches(4.3))
    ttf = tbox.text_frame
    ttf.word_wrap = True
    
    tp1 = ttf.paragraphs[0]
    tp1.text = "WHEN: ROADMAP PENGEMBANGAN 2026 - 2027"
    tp1.font.name = "Segoe UI"
    tp1.font.size = Pt(13)
    tp1.font.bold = True
    tp1.font.color.rgb = COLOR_EMERALD
    tp1.space_after = Pt(8)

    when_points = [
        ("Current State (2026)", "🟢 Fondasi Core GL, Neraca Lajur, P&L, Cash Flow, BRI Parser, Aging AR/AP, dan Aset Tetap siap produksi."),
        ("Fase 1 (Q4 2026)", "Siklus Bisnis Penjualan & Pembelian (Sales Invoicing & PO) terhubung otomatis GL serta Otomasi Pajak PPN/PPh."),
        ("Fase 2 (Q1 2027)", "Manajemen Stok Multi-Gudang (FIFO & Moving Average) dan Ekspansi Parser Multi-Bank (BCA, Mandiri, BNI)."),
        ("Fase 3 (Q2 2027)", "Kontrol Anggaran (Budgeting vs Actual) dengan Overbudget Guard dan RESTful API terintegrasi POS/E-Commerce."),
        ("Fase 4 (Q3 2027)", "Transformasi Cloud SaaS Multi-Tenancy dan Smart AI OCR Reader kuitansi otomatis.")
    ]
    for w_period, w_desc in when_points:
        p = ttf.add_paragraph()
        p.text = f"• {w_period}: {w_desc}"
        p.font.name = "Segoe UI"
        p.font.size = Pt(10)
        p.font.color.rgb = COLOR_TEXT_BODY
        p.space_after = Pt(4)

    # ==========================================
    # SLIDE 7: HOW - Mekanisme & Jaminan Mutu
    # ==========================================
    s7 = prs.slides.add_slide(blank_slide_layout)
    add_bg(s7, dark=False)
    add_header(s7, "HOW: Bagaimana ArtaLedger Bekerja & Menjamin Kualitas?", "DIMENSI 6: HOW (MEKANISME KERJA & JAMINAN MUTU)")

    how_pillars = [
        ("1. ACID Database Transactions", "Setiap transaksi posting dibungkus dalam blok transaksi database ACID dengan tipe data DECIMAL(15,2). Jika terjadi kegagalan sistem, seluruh data dibatalkan secara bersih (Automatic Rollback).", COLOR_ACCENT),
        ("2. Validasi Balancing Real-Time", "Mekanisme validasi ketat memastikan selisih mutlak antara total debit dan kredit tidak melebihi |Δ| < 0.01, serta memblokir posting langsung ke akun berstatus Header.", COLOR_EMERALD),
        ("3. 201 Automated Tests (Pest PHP)", "Keandalan sistem terbukti dengan 201 pengujian otomatis unit & integrasi dengan tingkat kelulusan 100% (749 asersi, 0 gagal), menjamin tidak ada fitur yang retak saat penambahan modul baru.", COLOR_AMBER),
        ("4. Standar Kode Bersih & DAG Sync", "Seluruh kode mematuhi konvensi PSR-12 melalui Laravel Pint (0 violations), serta diagram arsitektur sistem selalu disinkronkan secara ketat pada KNOWLEDGE_GRAPH.md.", COLOR_ACCENT)
    ]
    for idx, (title, desc, col) in enumerate(how_pillars):
        cx = 0.8 + idx * 2.95
        add_card(s7, cx, 1.9, 2.8, 4.8, bg_color=COLOR_CARD_LIGHT, border_color=RGBColor(226, 232, 240))
        box = s7.shapes.add_textbox(Inches(cx + 0.25), Inches(2.2), Inches(2.3), Inches(4.2))
        btf = box.text_frame
        btf.word_wrap = True
        
        p_num = btf.paragraphs[0]
        p_num.text = f"PILAR 0{idx+1}"
        p_num.font.name = "Segoe UI"
        p_num.font.size = Pt(10)
        p_num.font.bold = True
        p_num.font.color.rgb = col
        p_num.space_after = Pt(6)

        p_title = btf.add_paragraph()
        p_title.text = title
        p_title.font.name = "Segoe UI"
        p_title.font.size = Pt(13)
        p_title.font.bold = True
        p_title.font.color.rgb = COLOR_PRIMARY
        p_title.space_after = Pt(8)

        p_desc = btf.add_paragraph()
        p_desc.text = desc
        p_desc.font.name = "Segoe UI"
        p_desc.font.size = Pt(10.5)
        p_desc.font.color.rgb = COLOR_TEXT_BODY

    # ==========================================
    # SLIDE 8: Conclusion (Dark Theme)
    # ==========================================
    s8 = prs.slides.add_slide(blank_slide_layout)
    add_bg(s8, dark=True)

    head_box = s8.shapes.add_textbox(Inches(0.8), Inches(0.8), Inches(11.7), Inches(1.2))
    htf = head_box.text_frame
    htf.word_wrap = True
    hp1 = htf.paragraphs[0]
    hp1.text = "KESIMPULAN 5W + 1H"
    hp1.font.name = "Segoe UI"
    hp1.font.size = Pt(12)
    hp1.font.bold = True
    hp1.font.color.rgb = COLOR_ACCENT
    hp1.space_after = Pt(4)

    hp2 = htf.add_paragraph()
    hp2.text = "ArtaLedger: Investasi Terukur Transformasi Finansial Korporat"
    hp2.font.name = "Segoe UI"
    hp2.font.size = Pt(26)
    hp2.font.bold = True
    hp2.font.color.rgb = COLOR_TEXT_LIGHT

    summary_cards = [
        ("Akuntabilitas Teruji", "Kepatuhan penuh standar PSAK, format neraca lajur yang diakui auditor eksternal, dan audit trail mutasi tak terbantahkan.", COLOR_EMERALD),
        ("Efisiensi Operasional", "Otomasi rekonsiliasi mutasi bank, penjadwalan depresiasi aset fisik, dan percepatan penyajian laporan keuangan akhir bulan.", COLOR_ACCENT),
        ("Kesiapan Masa Depan", "Fondasi modern yang siap bertransformasi menjadi Ekosistem ERP Bisnis Lengkap dan Cloud SaaS Multi-Tenancy pada 2027.", COLOR_AMBER)
    ]
    for idx, (title, desc, col) in enumerate(summary_cards):
        cx = 0.8 + idx * 3.95
        add_card(s8, cx, 2.3, 3.8, 4.3, bg_color=COLOR_CARD_DARK, border_color=COLOR_CARD_BORDER)
        box = s8.shapes.add_textbox(Inches(cx + 0.3), Inches(2.55), Inches(3.2), Inches(3.8))
        btf = box.text_frame
        btf.word_wrap = True
        
        p_tag = btf.paragraphs[0]
        p_tag.text = "NILAI STRATEGIS"
        p_tag.font.name = "Segoe UI"
        p_tag.font.size = Pt(10.5)
        p_tag.font.bold = True
        p_tag.font.color.rgb = col
        p_tag.space_after = Pt(6)

        p_t = btf.add_paragraph()
        p_t.text = title
        p_t.font.name = "Segoe UI"
        p_t.font.size = Pt(15)
        p_t.font.bold = True
        p_t.font.color.rgb = COLOR_TEXT_LIGHT
        p_t.space_after = Pt(10)

        p_d = btf.add_paragraph()
        p_d.text = desc
        p_d.font.name = "Segoe UI"
        p_d.font.size = Pt(11.5)
        p_d.font.color.rgb = COLOR_TEXT_MUTED

    prs.save(output_path)
    print(f"Presentation 5W1H successfully created at: {output_path}")

if __name__ == "__main__":
    out_dir = r"d:\Belajar Laravel\artaledger\docs"
    pptx_file = os.path.join(out_dir, "Bahan_Presentasi_ArtaLedger.pptx")
    pptx_5w1h_file = os.path.join(out_dir, "Bahan_Presentasi_ArtaLedger_5W1H.pptx")
    
    # Save primary 5W1H file
    create_presentation_5w1h(pptx_5w1h_file)
    
    # Try updating main file if not locked
    try:
        import shutil
        shutil.copyfile(pptx_5w1h_file, pptx_file)
        print(f"Also successfully updated main file: {pptx_file}")
    except PermissionError:
        print(f"Notice: Main file {pptx_file} is currently open in Microsoft PowerPoint. Saved to {pptx_5w1h_file}.")
