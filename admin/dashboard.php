<?php
require_once '../config/database.php';
requireLogin();

$heroes = getHeroes();
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - Nothrenzz</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    /* [CSS SAMA SEPERTI PUNYA LO - TIDAK BERUBAH] */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg-dark: #0a0a0f;
      --bg-card: #1e1e28;
      --text-primary: #f3f4f6;
      --text-secondary: #d1d5db;
      --text-muted: #9ca3af;
      --accent: #e5e7eb;
      --border: #333344;
      --border-light: #444455;
      --success: #22c55e;
      --danger: #ef4444;
      --warning: #f59e0b;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-dark);
      color: var(--text-primary);
      min-height: 100vh;
      padding-bottom: 100px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 16px;
    }

    /* Header */
    .glass-header {
      position: sticky;
      top: 0;
      z-index: 1000;
      background: var(--bg-card);
      border-bottom: 1px solid var(--border);
      padding: 12px 0;
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-title {
      display: flex;
      flex-direction: column;
    }

    .title-main {
      font-weight: 600;
      font-size: 0.95rem;
    }

    .title-sub {
      font-size: 0.7rem;
      color: var(--text-muted);
      margin-top: 1px;
    }

    .header-actions {
      display: flex;
      gap: 8px;
    }

    .btn-header {
      padding: 8px 14px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid var(--border-light);
      font-family: 'Inter', sans-serif;
      background: transparent;
      color: var(--text-secondary);
    }

    .btn-header:hover {
      background: #2a2a3a;
      color: var(--text-primary);
      transform: translateY(-1px);
    }

    .btn-header.logout:hover {
      background: var(--danger);
      border-color: var(--danger);
      color: #fff;
    }

    /* Main Content */
    .section-top {
      display: flex;
      flex-direction: column;
      gap: 16px;
      margin: 24px 0;
      animation: fadeInDown 0.6s ease;
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @media (min-width: 640px) {
      .section-top {
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-end;
      }
    }

    .section-title {
      font-size: 1.5rem;
      font-weight: 600;
    }

    .section-subtitle {
      font-size: 0.875rem;
      color: var(--text-secondary);
      margin-top: 4px;
    }

    .search-box {
      padding: 12px 16px;
      border-radius: 16px;
      font-size: 0.875rem;
      color: var(--text-primary);
      background: #222233;
      border: 1px solid var(--border-light);
      width: 100%;
      max-width: 400px;
      outline: none;
      font-family: 'Inter', sans-serif;
      transition: all 0.3s ease;
    }

    .search-box:focus {
      border-color: #555566;
      background: #2a2a3a;
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    /* Fixed Add Button - Smooth Animation */
    .fab {
      position: fixed;
      bottom: 24px;
      right: 24px;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: #2d2d37;
      border: 1px solid var(--border-light);
      color: var(--text-primary);
      font-size: 1.5rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4);
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      z-index: 100;
      animation: fabEnter 0.6s ease 0.3s both;
    }

    @keyframes fabEnter {
      from {
        transform: scale(0) rotate(-180deg);
        opacity: 0;
      }
      to {
        transform: scale(1) rotate(0);
        opacity: 1;
      }
    }

    .fab:hover {
      background: #3a3a4a;
      transform: scale(1.1) rotate(90deg);
      box-shadow: 0 8px 30px rgba(0,0,0,0.5);
    }

    .fab:active {
      transform: scale(0.95);
    }

    /* Tooltip */
    .fab::before {
      content: 'Tambah Hero';
      position: absolute;
      right: 70px;
      background: var(--bg-card);
      padding: 8px 14px;
      border-radius: 10px;
      font-size: 0.8rem;
      font-weight: 600;
      white-space: nowrap;
      opacity: 0;
      transform: translateX(10px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      pointer-events: none;
      border: 1px solid var(--border);
    }

    .fab:hover::before {
      opacity: 1;
      transform: translateX(0);
    }

    /* Hero List - Smooth Stagger */
    .hero-list {
      background: #14141e;
      border-radius: 24px;
      padding: 20px;
      border: 2px solid #222233;
      margin-top: 20px;
      animation: fadeInUp 0.6s ease 0.2s both;
    }

    .hero-item {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 16px;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 16px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      opacity: 0;
      animation: itemEnter 0.5s ease forwards;
    }

    @keyframes itemEnter {
      from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .hero-item:hover {
      background: #252530;
      transform: translateY(-3px);
      border-color: var(--border-light);
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    .hero-img {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      background: #1a1a1a;
      overflow: hidden;
      flex-shrink: 0;
      transition: transform 0.3s ease;
    }

    .hero-item:hover .hero-img {
      transform: scale(1.05);
    }

    .hero-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .hero-info {
      flex: 1;
      min-width: 0;
    }

    .hero-name {
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 4px;
      transition: color 0.2s ease;
    }

    .hero-item:hover .hero-name {
      color: #fff;
    }

    .hero-role {
      font-size: 0.8rem;
      color: var(--text-muted);
    }

    .hero-actions {
      display: flex;
      gap: 8px;
      flex-shrink: 0;
    }

    .btn {
      padding: 8px 16px;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid var(--border-light);
      font-family: 'Inter', sans-serif;
    }

    .btn-primary {
      background: #2d2d37;
      color: var(--text-primary);
    }

    .btn-primary:hover {
      background: #3a3a4a;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    .btn-danger {
      background: transparent;
      color: var(--danger);
      border-color: var(--danger);
    }

    .btn-danger:hover {
      background: var(--danger);
      color: #fff;
      transform: translateY(-2px);
    }

    .btn-sm {
      padding: 6px 12px;
      font-size: 0.8rem;
    }

    /* Modal - Super Smooth */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0);
      backdrop-filter: blur(0);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2000;
      padding: 20px;
      overflow-y: auto;
      opacity: 0;
      visibility: hidden;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .modal-overlay.show {
      opacity: 1;
      visibility: visible;
      background: rgba(0,0,0,0.8);
      backdrop-filter: blur(4px);
    }

    .modal {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 24px;
      width: 100%;
      max-width: 700px;
      max-height: 90vh;
      overflow-y: auto;
      padding: 32px;
      transform: scale(0.8) translateY(50px);
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .modal-overlay.show .modal {
      transform: scale(1) translateY(0);
      opacity: 1;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .modal-title {
      font-size: 1.25rem;
      font-weight: 700;
    }

    .modal-close {
      width: 36px;
      height: 36px;
      border-radius: 12px;
      background: #222233;
      border: 1px solid var(--border-light);
      color: var(--text-primary);
      cursor: pointer;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .modal-close:hover {
      background: var(--danger);
      border-color: var(--danger);
      transform: rotate(90deg);
    }

    .form-group {
      margin-bottom: 20px;
      opacity: 0;
      transform: translateX(-20px);
      animation: formSlideIn 0.4s ease forwards;
    }

    .form-group:nth-child(1) { animation-delay: 0.1s; }
    .form-group:nth-child(2) { animation-delay: 0.15s; }
    .form-group:nth-child(3) { animation-delay: 0.2s; }

    @keyframes formSlideIn {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .form-label {
      display: block;
      font-size: 0.875rem;
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--text-secondary);
    }

    .form-input {
      width: 100%;
      padding: 12px 16px;
      border-radius: 14px;
      font-size: 0.95rem;
      color: var(--text-primary);
      background: #222233;
      border: 1px solid var(--border-light);
      outline: none;
      transition: all 0.3s ease;
      font-family: 'Inter', sans-serif;
    }

    .form-input:focus {
      border-color: #555566;
      background: #2a2a3a;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    /* Series Section - Smooth */
    .series-section {
      background: #1a1a23;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 16px;
      border: 1px solid var(--border);
      opacity: 0;
      transform: translateY(20px);
      animation: seriesEnter 0.5s ease forwards;
    }

    @keyframes seriesEnter {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .series-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .series-title-input {
      flex: 1;
      margin-right: 10px;
    }

    .link-item {
      background: #222233;
      border-radius: 12px;
      padding: 12px;
      margin-bottom: 10px;
      display: flex;
      gap: 10px;
      align-items: center;
      opacity: 0;
      transform: translateX(-20px);
      animation: linkEnter 0.4s ease forwards;
    }

    @keyframes linkEnter {
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .link-input {
      flex: 1;
    }

    .link-input input {
      width: 100%;
      margin-bottom: 6px;
    }

    .link-input input:last-child {
      margin-bottom: 0;
    }

    .empty-state {
      text-align: center;
      color: var(--text-muted);
      padding: 48px 16px;
      animation: fadeIn 0.5s ease;
    }

    .add-btn {
      width: 100%;
      padding: 12px;
      border-radius: 14px;
      background: #222233;
      border: 2px dashed var(--border-light);
      color: var(--text-secondary);
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 600;
    }

    .add-btn:hover {
      border-color: var(--text-primary);
      color: var(--text-primary);
      background: #2a2a3a;
      transform: translateY(-2px);
    }

    /* Toast - Smooth Bounce */
    .toast {
      position: fixed;
      bottom: 100px;
      right: 20px;
      background: var(--success);
      color: #fff;
      padding: 14px 24px;
      border-radius: 14px;
      font-weight: 600;
      transform: translateX(150px);
      opacity: 0;
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      z-index: 3000;
      box-shadow: 0 4px 20px rgba(34, 197, 94, 0.3);
    }

    .toast.show {
      transform: translateX(0);
      opacity: 1;
    }

    .toast.hide {
      transform: translateX(150px);
      opacity: 0;
    }

    @media (max-width: 640px) {
      .hero-item {
        flex-wrap: wrap;
      }
      
      .hero-actions {
        width: 100%;
        justify-content: flex-end;
      }
      
      .modal {
        padding: 20px;
      }

      .fab {
        bottom: 20px;
        right: 20px;
        width: 52px;
        height: 52px;
      }

      .fab::before {
        display: none;
      }
      /* UPLOAD AREA */
.upload-area {
  border: 2px dashed var(--border);
  border-radius: 16px;
  padding: 30px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #1a1a23;
  margin-bottom: 8px;
}

.upload-area:hover {
  border-color: var(--accent);
  background: #222233;
}

.upload-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.upload-preview img {
  max-width: 180px;
  max-height: 180px;
  border-radius: 16px;
  object-fit: cover;
  border: 3px solid var(--border);
}

.upload-preview svg {
  color: var(--text-muted);
}

.upload-preview p {
  color: var(--text-muted);
  font-size: 0.9rem;
  margin: 0;
}
    }
  </style>
</head>
<body>

<header class="glass-header">
  <div class="container header-content">
    <div class="header-title">
      <span class="title-main">Nothrenzz Admin</span>
      <span class="title-sub">Manage Scripts</span>
    </div>
    <div class="header-actions">
      <button class="btn-header" onclick="exportData()">💾 Export</button>
      <a href="javascript:void(0)" class="btn-header logout" onclick="openLogoutModalFix()">Logout</a>
    </div>
  </div>
</header>

<main class="container">
  <section class="section-top">
    <div>
      <h1 class="section-title">Kelola Hero</h1>
      <p class="section-subtitle">Tambah, edit, atau hapus hero dan script</p>
    </div>
    <input type="text" id="searchHero" class="search-box" placeholder="Cari hero..." oninput="filterHeroes()">
  </section>

  <div class="hero-list" id="heroList">
    <div class="empty-state">Memuat data...</div>
  </div>
</main>

<!-- Fixed Add Button -->
<button class="fab" onclick="openModal()" title="Tambah Hero Baru">+</button>

<!-- Modal Form -->
<div class="modal-overlay" id="modal">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title" id="modalTitle">Tambah Hero</h2>
      <button class="modal-close" onclick="closeModal()">×</button>
    </div>
    
    <form id="heroForm" onsubmit="saveHero(event)" enctype="multipart/form-data">
      <input type="hidden" id="heroIndex" value="-1">
      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
      
      <div class="form-group">
        <label class="form-label">Nama Hero</label>
        <input type="text" id="heroName" class="form-input" placeholder="Contoh: Gusion" required>
      </div>
      
      <!-- UPLOAD GAMBAR -->
<div class="form-group">
  <label class="form-label">Gambar Hero</label>
  <div class="upload-area" id="uploadArea" onclick="document.getElementById('heroImage').click()">
    <input type="file" id="heroImage" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
    <div class="upload-preview" id="uploadPreview">
      <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
        <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"></circle>
        <polyline points="21 15 16 10 5 21"></polyline>
      </svg>
      <p>Klik untuk upload gambar</p>
    </div>
  </div>
  <small style="color: var(--text-muted); display: block; margin-top: 8px;">
    Format: JPG, PNG, GIF. Maksimal 5MB
  </small>
</div>
      
      <div class="form-group">
        <label class="form-label">Role/Description</label>
        <input type="text" id="heroDesc" class="form-input" placeholder="Contoh: Assassin/Mage" required>
      </div>
      
      <div id="seriesContainer">
        <!-- Series akan di-render di sini -->
      </div>
      
      <button type="button" class="add-btn" onclick="addSeries()" style="margin-bottom: 20px;">
        + Tambah Series
      </button>
      
      <div style="display: flex; gap: 12px;">
        <button type="submit" class="btn btn-primary" style="flex: 1; background: var(--success); border-color: var(--success); color: #fff;">Simpan</button>
        <button type="button" class="btn btn-primary" onclick="closeModal()" style="flex: 1;">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL KONFIRMASI LOGOUT - DENGAN ANIMASI -->
<div id="logoutModalFix" style="
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0);
  backdrop-filter: blur(0);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 999999;
  font-family: 'Inter', sans-serif;
  transition: all 0.3s ease;
">
  <div id="logoutModalContent" style="
    background: #1e1e28;
    border: 2px solid #444455;
    border-radius: 24px;
    width: 90%;
    max-width: 350px;
    padding: 0;
    box-shadow: 0 20px 60px black;
    transform: scale(0.8) translateY(30px);
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  ">
    <div style="
      padding: 20px;
      border-bottom: 2px solid #333344;
      display: flex;
      justify-content: space-between;
      align-items: center;
    ">
      <h3 style="color: white; margin: 0; font-size: 1.2rem;">🔒 Konfirmasi Logout</h3>
      <button onclick="closeLogoutModalFix()" style="
        background: #333344;
        border: none;
        color: white;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        font-size: 1.3rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
      ">×</button>
    </div>
    <div style="padding: 30px 20px; text-align: center;">
      <p style="color: #d1d5db; margin-bottom: 30px;">Yakin ingin keluar dari dashboard?</p>
      <div style="display: flex; gap: 12px;">
        <button onclick="logoutFix()" style="
          flex: 1;
          background: #ef4444;
          color: white;
          border: none;
          padding: 12px;
          border-radius: 12px;
          font-weight: 600;
          cursor: pointer;
          font-family: 'Inter', sans-serif;
          transition: all 0.2s;
        " onmouseover="this.style.transform='translateY(-2px)'" 
           onmouseout="this.style.transform='translateY(0)'">Ya, Logout</button>
        <button onclick="closeLogoutModalFix()" style="
          flex: 1;
          background: #2d2d37;
          color: white;
          border: 1px solid #444455;
          padding: 12px;
          border-radius: 12px;
          font-weight: 600;
          cursor: pointer;
          font-family: 'Inter', sans-serif;
          transition: all 0.2s;
        " onmouseover="this.style.transform='translateY(-2px)'" 
           onmouseout="this.style.transform='translateY(0)'">Batal</button>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast">Data berhasil disimpan!</div>

<script>
  // ==================== MODAL LOGOUT FIX DENGAN ANIMASI ====================
  function openLogoutModalFix() {
    const overlay = document.getElementById('logoutModalFix');
    const content = document.getElementById('logoutModalContent');
    
    overlay.style.display = 'flex';
    overlay.style.background = 'rgba(0,0,0,0.8)';
    overlay.style.backdropFilter = 'blur(5px)';
    
    setTimeout(() => {
      content.style.transform = 'scale(1) translateY(0)';
      content.style.opacity = '1';
    }, 10);
    
    document.body.style.overflow = 'hidden';
  }

  function closeLogoutModalFix() {
    const overlay = document.getElementById('logoutModalFix');
    const content = document.getElementById('logoutModalContent');
    
    content.style.transform = 'scale(0.8) translateY(30px)';
    content.style.opacity = '0';
    
    setTimeout(() => {
      overlay.style.background = 'rgba(0,0,0,0)';
      overlay.style.backdropFilter = 'blur(0)';
      setTimeout(() => {
        overlay.style.display = 'none';
      }, 300);
    }, 10);
    
    document.body.style.overflow = '';
  }

  function logoutFix() {
    window.location.href = 'logout.php';
  }

  // Data dari PHP
  const initialHeroes = <?php echo json_encode($heroes); ?>;
  let heroes = Array.isArray(initialHeroes) ? [...initialHeroes] : [];
  let editingIndex = -1;
  let tempSeries = [];

  // Load data
  function loadData() {
    renderHeroList();
  }

  function renderHeroList() {
    const container = document.getElementById('heroList');
    const search = document.getElementById('searchHero').value.toLowerCase();
    
    const filtered = heroes.filter(h => 
      h.name.toLowerCase().includes(search) ||
      (h.description && h.description.toLowerCase().includes(search))
    );
    
    if (filtered.length === 0) {
      container.innerHTML = '<div class="empty-state">Tidak ada hero ditemukan</div>';
      return;
    }
    
    container.innerHTML = filtered.map((hero, idx) => {
      const originalIndex = heroes.findIndex(h => h.name === hero.name);
      const totalLinks = hero.series ? hero.series.reduce((acc, s) => acc + (s.items?.length || 0), 0) : 0;
      
      return `
      <div class="hero-item">
        <div class="hero-img">
          <img src="../${hero.image}?v=${localStorage.getItem('img_version') || '1'}" 
             alt="${hero.name}" 
               onerror="this.src='https://placehold.co/100x100/1a1a1a/fff?text=${hero.name ? hero.name.charAt(0) : '?'}'">
        </div>
        <div class="hero-info">
          <div class="hero-name">${hero.name || ''}</div>
          <div class="hero-role">${hero.description || ''} • ${totalLinks} link</div>
        </div>
        <div class="hero-actions">
          <button class="btn btn-primary btn-sm" onclick="editHero(${originalIndex})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteHero(${originalIndex})">Hapus</button>
        </div>
      </div>
    `}).join('');
  }

  function filterHeroes() {
    renderHeroList();
  }

  // Modal Functions
function openModal(index = -1) {
  editingIndex = index;
  const modal = document.getElementById('modal');
  const title = document.getElementById('modalTitle');
  
  if (index >= 0 && heroes[index]) {
    const hero = heroes[index];
    title.textContent = 'Edit Hero';
    document.getElementById('heroIndex').value = index;
    document.getElementById('heroName').value = hero.name || '';
    document.getElementById('heroDesc').value = hero.description || '';
    tempSeries = JSON.parse(JSON.stringify(hero.series || []));
    
// Tampilkan preview gambar jika ada (AMAN DENGAN TRY-CATCH)
try {
  const preview = document.getElementById('uploadPreview');
  if (preview && hero.image) {
    let version = '1';
    try {
      version = localStorage.getItem('img_version') || '1';
    } catch (e) {
      console.warn('localStorage error, using default version');
    }
    preview.innerHTML = `<img src="../${hero.image}?v=${version}" alt="Preview">`;
  }
} catch (e) {
  console.error('Preview error:', e);
  // Fallback: tampilkan tanpa versi
  const preview = document.getElementById('uploadPreview');
  if (preview && hero.image) {
    preview.innerHTML = `<img src="../${hero.image}" alt="Preview">`;
  }
}   } else {
    title.textContent = 'Tambah Hero';
    document.getElementById('heroForm').reset();
    document.getElementById('heroIndex').value = -1;
    tempSeries = [];
    
    // Reset preview
    const preview = document.getElementById('uploadPreview');
    if (preview) {
      preview.innerHTML = `
        <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
        <p>Klik untuk upload gambar</p>
      `;
    }
  }
  
  renderSeries();
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
}
  // PREVIEW GAMBAR
function previewImage(input) {
  const preview = document.getElementById('uploadPreview');
  
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!validTypes.includes(file.type)) {
      alert('Hanya file JPG, PNG, GIF yang diperbolehkan');
      input.value = '';
      return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
      alert('Ukuran file maksimal 5MB');
      input.value = '';
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
    }
    reader.readAsDataURL(file);
  }
}

  function closeModal() {
    document.getElementById('modal').classList.remove('show');
    document.body.style.overflow = '';
  }

  function renderSeries() {
    const container = document.getElementById('seriesContainer');
    container.innerHTML = '';
    
    tempSeries.forEach((series, sIdx) => {
      const seriesDiv = document.createElement('div');
      seriesDiv.className = 'series-section';
      seriesDiv.innerHTML = `
        <div class="series-header">
          <input type="text" class="form-input series-title-input" 
                 value="${series.name || ''}" placeholder="Nama Series" 
                 onchange="updateSeriesName(${sIdx}, this.value)" required>
          <button type="button" class="btn btn-danger btn-sm" onclick="removeSeries(${sIdx})">×</button>
        </div>
        <div id="links-${sIdx}">
          ${(series.items || []).map((item, iIdx) => `
            <div class="link-item">
              <div class="link-input">
                <input type="text" class="form-input" placeholder="Nama skin" 
                       value="${item.name || ''}" onchange="updateLink(${sIdx}, ${iIdx}, 'name', this.value)" required>
                <input type="url" class="form-input" placeholder="https://..." 
                       value="${item.link || ''}" onchange="updateLink(${sIdx}, ${iIdx}, 'link', this.value)" required>
              </div>
              <button type="button" class="btn btn-danger btn-sm" onclick="removeLink(${sIdx}, ${iIdx})">×</button>
            </div>
          `).join('')}
        </div>
        <button type="button" class="add-btn" onclick="addLink(${sIdx})" style="margin-top: 10px;">
          + Tambah Link
        </button>
      `;
      container.appendChild(seriesDiv);
    });
  }

  function updateSeriesName(sIdx, value) {
    if (!tempSeries[sIdx]) tempSeries[sIdx] = { name: '', items: [] };
    tempSeries[sIdx].name = value;
  }

  function updateLink(sIdx, iIdx, field, value) {
    if (!tempSeries[sIdx].items[iIdx]) tempSeries[sIdx].items[iIdx] = { name: '', link: '' };
    tempSeries[sIdx].items[iIdx][field] = value;
  }

  function addSeries() {
    tempSeries.push({ name: '', items: [] });
    renderSeries();
    
    // Scroll ke series baru
    setTimeout(() => {
      const sections = document.querySelectorAll('.series-section');
      if (sections.length > 0) {
        sections[sections.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }, 100);
  }

  function removeSeries(idx) {
    tempSeries.splice(idx, 1);
    renderSeries();
  }

  function addLink(sIdx) {
    if (!tempSeries[sIdx].items) tempSeries[sIdx].items = [];
    tempSeries[sIdx].items.push({ name: '', link: '' });
    renderSeries();
  }

  function removeLink(sIdx, iIdx) {
    tempSeries[sIdx].items.splice(iIdx, 1);
    renderSeries();
  }

  async function saveHero(e) {
  e.preventDefault();
  
  const formData = new FormData();
  
  const name = document.getElementById('heroName').value;
  const description = document.getElementById('heroDesc').value;
  const validSeries = tempSeries.filter(s => s.name && s.items && s.items.length > 0);
  
  formData.append('name', name);
  formData.append('description', description);
  formData.append('series', JSON.stringify(validSeries));
  
  const fileInput = document.getElementById('heroImage');
  if (fileInput.files[0]) {
    formData.append('image', fileInput.files[0]);
  }
  
  const idx = parseInt(document.getElementById('heroIndex').value);
  if (idx >= 0) {
    formData.append('id', idx);
  }
  
  // Tambah CSRF token
  const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
  if (csrfToken) {
    formData.append('csrf_token', csrfToken);
  }
  
  const btn = e.target.querySelector('button[type="submit"]');
  const originalText = btn.innerHTML;
  btn.innerHTML = 'Menyimpan...';
  btn.disabled = true;
  
  try {
    const response = await fetch('api/save_hero.php', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      localStorage.setItem('img_version', Date.now());
      showToast(idx >= 0 ? 'Hero berhasil diupdate!' : 'Hero berhasil ditambahkan!');
      setTimeout(() => location.reload(), 1500);
    } else {
      alert(result.error || 'Gagal menyimpan');
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  } catch (error) {
    console.error('Save error:', error);
    alert('Terjadi kesalahan koneksi');
    btn.innerHTML = originalText;
    btn.disabled = false;
  }
}

  async function saveToServer(data) {
    try {
      await fetch('api/save_hero.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
    } catch (error) {
      console.error('Save error:', error);
    }
  }

  function editHero(index) {
    openModal(index);
  }

  async function deleteHero(index) {
  if (!confirm(`Yakin mau hapus ${heroes[index]?.name}?`)) return;
  
  try {
    const response = await fetch('api/delete_hero.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: index })
    });
    
    const result = await response.json();
    
    if (result.success) {
      showToast('Hero berhasil dihapus!');
      setTimeout(() => location.reload(), 1500);
    } else {
      alert('Gagal menghapus');
    }
  } catch (error) {
    console.error('Delete error:', error);
    alert('Terjadi kesalahan');
  }
}

  function exportData() {
    const dataStr = JSON.stringify(heroes, null, 2);
    const blob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'data.json';
    a.click();
    URL.revokeObjectURL(url);
    showToast('File data.json berhasil di-download!');
  }

  function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.remove('hide');
    toast.classList.add('show');
    
    setTimeout(() => {
      toast.classList.add('hide');
      setTimeout(() => {
        toast.classList.remove('show', 'hide');
      }, 500);
    }, 3000);
  }

  // Close modal on outside click
  document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
  });

  // ESC to close modals
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeModal();
      closeLogoutModalFix();
    }
  });

  // Init
  loadData();
</script>

</body>
</html>