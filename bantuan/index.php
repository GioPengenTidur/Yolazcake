<?php
session_start();

// Status login dipakai buat sapaan pembuka & ajakan login di chatbot
$sudahLogin = isset($_SESSION['username']);
$namaUser   = $sudahLogin ? $_SESSION['username'] : null;

if ($sudahLogin) {
    $sapaanAwal = 'Haii, ' . htmlspecialchars($namaUser, ENT_QUOTES) . '! 👋 Aku Yola, asisten AI YOLAZCAKE. '
        . 'Mau nanya-nanya soal menu, promo, booking meja, atau jam buka? Gaskeun aja tanya di bawah~ ✨';
} else {
    $sapaanAwal = 'Haii kak! 👋 Aku Yola, asisten AI YOLAZCAKE. '
        . 'Mau nanya-nanya soal menu, promo, booking meja, atau jam buka? Gaskeun aja tanya di bawah~ ✨ '
        . 'Oh iya, kalau nanti kakak tertarik buat pesan atau booking meja, aku saranin login/daftar dulu ya biar prosesnya lebih gampang dan poinnya kecatat 😉';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <link rel="stylesheet" href="../assets/css/lucide-icons.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pusat Bantuan – YOLAZCAKE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --gold:#D4AF37; --gold-l:#FFE88A;
      --rose:#EE2A7B; --purple:#8A2BE2;
      --bg1:#0d0520; --bg2:#1a0a3a; --bg3:#150830;
      --glass:rgba(255,255,255,.045); --gb:rgba(255,255,255,.10);
      --text:#fff; --muted:rgba(255,255,255,.5);
    }
    html,body{height:100%;}
    body{
      min-height:100vh;font-family:'Inter',sans-serif;
      background:linear-gradient(140deg,var(--bg1) 0%,var(--bg2) 50%,var(--bg3) 100%);
      color:var(--text);display:flex;align-items:center;justify-content:center;
      padding:24px;position:relative;overflow-x:hidden;
    }
    body::before{
      content:'';position:fixed;inset:0;pointer-events:none;
      background:radial-gradient(ellipse 60% 50% at 15% 15%,rgba(212,175,55,.10) 0%,transparent 55%),
                 radial-gradient(ellipse 55% 50% at 85% 85%,rgba(138,43,226,.12) 0%,transparent 55%);
      animation:drift 12s ease-in-out infinite alternate;
    }
    @keyframes drift{0%{opacity:.7;}100%{opacity:1;transform:scale(1.05);}}

    .app-shell{
      position:relative;z-index:2;width:100%;max-width:940px;height:min(760px,88vh);
      display:flex;gap:16px;
    }

    /* ── Sidebar: riwayat obrolan & obrolan baru ── */
    .chat-sidebar{
      width:250px;flex-shrink:0;display:flex;flex-direction:column;gap:12px;
      background:var(--glass);backdrop-filter:blur(24px);
      border:1px solid var(--gb);border-radius:22px;padding:16px;
      opacity:0;transform:translateX(-16px);animation:cardIn .7s cubic-bezier(.22,.68,0,1.2) forwards .1s;
    }
    .new-chat-btn{
      display:flex;align-items:center;justify-content:center;gap:8px;
      padding:11px 14px;border-radius:14px;border:1px solid rgba(212,175,55,.4);
      background:linear-gradient(135deg,rgba(212,175,55,.18),rgba(238,42,123,.14));
      color:#FFE9B8;font-family:'Inter',sans-serif;font-size:.85em;font-weight:600;
      cursor:pointer;transition:.2s;
    }
    .new-chat-btn:hover{background:linear-gradient(135deg,rgba(212,175,55,.3),rgba(238,42,123,.22));transform:translateY(-1px);}
    .sidebar-label{
      font-size:.68em;letter-spacing:.6px;text-transform:uppercase;color:var(--muted);
      padding:2px 4px;margin-top:4px;
    }
    .history-list{flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:6px;}
    .history-list::-webkit-scrollbar{width:5px;}
    .history-list::-webkit-scrollbar-thumb{background:rgba(212,175,55,.3);border-radius:99px;}
    .history-item{
      position:relative;display:flex;align-items:center;gap:8px;
      padding:10px 12px;border-radius:12px;cursor:pointer;
      background:rgba(255,255,255,.03);border:1px solid transparent;
      transition:.18s;
    }
    .history-item:hover{background:rgba(255,255,255,.07);}
    .history-item.active{background:rgba(212,175,55,.14);border-color:rgba(212,175,55,.35);}
    .history-item .h-title{flex:1;font-size:.78em;color:#EDE9FF;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .history-item .h-del{
      opacity:0;flex-shrink:0;background:none;border:none;color:var(--muted);cursor:pointer;
      padding:3px;border-radius:6px;transition:.15s;
    }
    .history-item:hover .h-del{opacity:1;}
    .history-item .h-del:hover{color:#F6577A;background:rgba(246,87,122,.12);}
    .history-empty{font-size:.76em;color:var(--muted);padding:8px 6px;line-height:1.5;}

    .sidebar-toggle{
      display:none;background:none;border:1px solid rgba(255,255,255,.14);color:var(--muted);
      width:34px;height:34px;border-radius:10px;cursor:pointer;align-items:center;justify-content:center;
    }
    .sidebar-overlay{display:none;}

    @media (max-width:820px){
      .app-shell{max-width:640px;}
      .chat-sidebar{
        position:fixed;top:0;left:0;height:100%;width:78vw;max-width:300px;
        z-index:50;border-radius:0 20px 20px 0;transform:translateX(-105%);
        transition:transform .3s ease;
      }
      .chat-sidebar.open{transform:translateX(0);}
      .sidebar-toggle{display:flex;}
      .sidebar-overlay{
        display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:40;
      }
      .sidebar-overlay.open{display:block;}
    }

    .chat-wrap{
      position:relative;flex:1;min-width:0;height:100%;
      display:flex;flex-direction:column;
      background:var(--glass);backdrop-filter:blur(24px);
      border:1px solid var(--gb);border-radius:26px;overflow:hidden;
      box-shadow:0 30px 80px rgba(0,0,0,.45);
      opacity:0;transform:translateY(24px);animation:cardIn .7s cubic-bezier(.22,.68,0,1.2) forwards .1s;
    }
    @keyframes cardIn{to{opacity:1;transform:translateY(0);}}

    .chat-head{
      display:flex;align-items:center;gap:14px;padding:20px 24px;
      border-bottom:1px solid var(--gb);
      background:linear-gradient(90deg,rgba(212,175,55,.08),transparent);
      position:relative;
    }
    .chat-avatar{
      width:46px;height:46px;border-radius:14px;flex-shrink:0;overflow:hidden;
      background:linear-gradient(135deg,var(--purple),var(--rose) 60%,var(--gold));
      display:flex;align-items:center;justify-content:center;font-size:1.3em;
      box-shadow:0 4px 14px rgba(0,0,0,.35);
    }
    .chat-avatar img{
      width:100%;height:100%;object-fit:cover;object-position:center;
      transform:scale(1.55);transform-origin:center;
    }
    .chat-head h1{font-family:'Playfair Display',serif;font-size:1.15em;font-weight:700;}
    .chat-head .sub{font-size:.72em;color:var(--muted);display:flex;align-items:center;gap:5px;margin-top:2px;}
    .dot-online{width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 6px #22c55e;flex-shrink:0;}
    .chat-back{
      margin-left:auto;color:var(--muted);text-decoration:none;font-size:.78em;
      display:flex;align-items:center;gap:6px;padding:7px 12px;border-radius:10px;
      border:1px solid rgba(255,255,255,.1);transition:.2s;
    }
    .chat-back:hover{color:#fff;background:rgba(255,255,255,.06);}

    .chat-body{flex:1;overflow-y:auto;padding:22px 20px;display:flex;flex-direction:column;gap:14px;}
    .chat-body::-webkit-scrollbar{width:6px;}
    .chat-body::-webkit-scrollbar-thumb{background:rgba(212,175,55,.3);border-radius:99px;}

    .msg{max-width:82%;padding:12px 16px;border-radius:16px;font-size:.88em;line-height:1.55;
         opacity:0;animation:msgIn .35s forwards;white-space:pre-wrap;}
    @keyframes msgIn{to{opacity:1;}}
    .msg.bot{
      align-self:flex-start;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);
      border-bottom-left-radius:4px;
    }
    .msg.user{
      align-self:flex-end;color:#1a0533;font-weight:500;
      background:linear-gradient(135deg,var(--gold),var(--rose));
      border-bottom-right-radius:4px;
    }
    .msg.typing{display:flex;gap:5px;align-items:center;padding:14px 18px;}
    .typing-dot{width:6px;height:6px;border-radius:50%;background:var(--muted);animation:typingBounce 1.2s infinite;}
    .typing-dot:nth-child(2){animation-delay:.15s;}
    .typing-dot:nth-child(3){animation-delay:.3s;}
    @keyframes typingBounce{0%,60%,100%{transform:translateY(0);opacity:.4;}30%{transform:translateY(-5px);opacity:1;}}

    .quick-chips{display:flex;flex-wrap:wrap;gap:8px;padding:0 20px 14px;}
    .chip{
      font-size:.72em;padding:7px 13px;border-radius:99px;cursor:pointer;
      background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.28);color:var(--gold-l);
      transition:.2s;white-space:nowrap;
    }
    .chip:hover{background:rgba(212,175,55,.2);}
    .chip-login{background:rgba(238,42,123,.12);border-color:rgba(238,42,123,.32);color:#FFD1E6;}
    .chip-login:hover{background:rgba(238,42,123,.22);}

    .chat-input-bar{
      display:flex;gap:10px;padding:16px 18px;border-top:1px solid var(--gb);
      background:rgba(255,255,255,.02);
    }
    .chat-input-bar input{
      flex:1;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);
      border-radius:14px;padding:12px 16px;color:#fff;font-family:'Inter',sans-serif;font-size:.9em;outline:none;
      transition:border-color .2s;
    }
    .chat-input-bar input:focus{border-color:rgba(212,175,55,.5);}
    .chat-input-bar input::placeholder{color:rgba(255,255,255,.35);}
    .send-btn{
      width:46px;height:46px;border-radius:14px;border:none;cursor:pointer;flex-shrink:0;
      background:linear-gradient(135deg,var(--gold),var(--rose));color:#1a0533;
      display:flex;align-items:center;justify-content:center;transition:transform .2s;
    }
    .send-btn:hover{transform:scale(1.06);}
    .send-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}

    /* ── Toast premium: loading & sukses hapus riwayat ── */
    .top-toast{
      position:fixed;top:-90px;left:50%;transform:translateX(-50%);
      z-index:999;display:flex;align-items:center;gap:11px;
      padding:13px 24px;border-radius:999px;overflow:visible;
      font-family:'Inter',sans-serif;font-weight:600;font-size:.86em;letter-spacing:.2px;
      white-space:nowrap;
      box-shadow:0 16px 44px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.08) inset;
      transition:top .55s cubic-bezier(.22,.68,0,1.2),opacity .4s;
      opacity:0;pointer-events:none;
      background:linear-gradient(135deg,rgba(20,12,38,.96),rgba(45,20,70,.94));
      color:#F3EEFF;border:1px solid rgba(212,175,55,.35);
    }
    .top-toast.show{top:26px;opacity:1;}
    .top-toast.success{
      background:linear-gradient(135deg,var(--gold),var(--rose));
      color:#1a0533;border-color:rgba(255,255,255,.4);
    }
    .toast-spinner{
      width:17px;height:17px;border-radius:50%;flex-shrink:0;
      border:2.5px solid rgba(255,255,255,.22);border-top-color:#FFE88A;
      animation:toastSpin .65s linear infinite;
    }
    @keyframes toastSpin{to{transform:rotate(360deg);}}
    .toast-check{
      width:21px;height:21px;border-radius:50%;flex-shrink:0;
      background:rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;
      animation:toastCheckPop .45s cubic-bezier(.34,1.56,.64,1);
    }
    .toast-check .lucide-ic{width:13px;height:13px;stroke:#1a0533;stroke-width:3;}
    @keyframes toastCheckPop{0%{transform:scale(0);}60%{transform:scale(1.3);}100%{transform:scale(1);}}
    .toast-sparkles{position:absolute;inset:0;pointer-events:none;}
    .spk{
      position:absolute;width:5px;height:5px;border-radius:50%;background:#fff;
      top:50%;left:50%;opacity:0;
      animation:spkBurst .85s ease-out forwards;
      animation-delay:calc(var(--i) * .045s);
    }
    @keyframes spkBurst{
      0%{opacity:1;transform:translate(-50%,-50%) rotate(calc(var(--i) * 45deg)) translateX(0) scale(1);}
      100%{opacity:0;transform:translate(-50%,-50%) rotate(calc(var(--i) * 45deg)) translateX(42px) scale(0);}
    }

    @media (max-width:480px){
      .chat-wrap{height:92vh;border-radius:20px;}
      .msg{max-width:88%;}
    }
  </style>
</head>
<body>

<div class="app-shell">
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <aside class="chat-sidebar" id="chatSidebar">
    <button class="new-chat-btn" id="newChatBtn"><i data-lucide="plus" class="lucide-ic"></i> Obrolan Baru</button>
    <div class="sidebar-label">Riwayat Obrolan</div>
    <div class="history-list" id="historyList">
      <div class="history-empty">Belum ada riwayat obrolan.</div>
    </div>
  </aside>

  <div class="chat-wrap">
    <div class="chat-head">
      <button class="sidebar-toggle" id="sidebarToggle" aria-label="Riwayat obrolan"><i data-lucide="menu" class="lucide-ic"></i></button>
      <div class="chat-avatar"><img src="../assets/img/logo/yola-ai-icon.png" alt="Yola AI"></div>
      <div>
        <h1>Yola · Pusat Bantuan</h1>
        <div class="sub"><span class="dot-online"></span> Siap bantu soal YOLAZCAKE</div>
      </div>
      <a href="../index.php" class="chat-back"><i data-lucide="arrow-left" class="lucide-ic"></i> Kembali</a>
    </div>

    <div class="chat-body" id="chatBody">
      <div class="msg bot"><?= $sapaanAwal ?></div>
    </div>

    <div class="quick-chips" id="quickChips">
      <div class="chip" data-q="Menu apa aja yang ada?">🍰 Menu apa aja?</div>
      <div class="chip" data-q="Ada promo apa sekarang?">🎁 Promo sekarang</div>
      <div class="chip" data-q="Jam buka jam berapa?">🕒 Jam buka</div>
      <div class="chip" data-q="Cara booking meja gimana?">📅 Cara booking</div>
      <?php if (!$sudahLogin): ?>
      <div class="chip chip-login" onclick="window.location.href='../auth/login.php'">🔐 Login dulu yuk</div>
      <?php endif; ?>
    </div>

    <form class="chat-input-bar" id="chatForm">
      <input type="text" id="chatInput" placeholder="Tulis pertanyaan kamu..." autocomplete="off">
      <button type="submit" class="send-btn" id="sendBtn"><i data-lucide="send" class="lucide-ic"></i></button>
    </form>
  </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
if (window.lucide) lucide.createIcons();

const chatBody     = document.getElementById('chatBody');
const chatForm     = document.getElementById('chatForm');
const chatInput    = document.getElementById('chatInput');
const sendBtn      = document.getElementById('sendBtn');
const quickChips   = document.getElementById('quickChips');
const historyList  = document.getElementById('historyList');
const newChatBtn   = document.getElementById('newChatBtn');
const sidebarEl    = document.getElementById('chatSidebar');
const sidebarToggle= document.getElementById('sidebarToggle');
const sidebarOverlay = document.getElementById('sidebarOverlay');

let history   = []; // {role: 'user'|'bot', text: '...'} — punya sesi yang lagi aktif
let idSesiAktif = null; // null = belum tersimpan / obrolan baru

const SAPAAN_AWAL = <?= json_encode($sapaanAwal) ?>;

function addMessage(role, text) {
  const el = document.createElement('div');
  el.className = 'msg ' + role;
  el.textContent = text;
  chatBody.appendChild(el);
  chatBody.scrollTop = chatBody.scrollHeight;
  return el;
}

function formatWaktuRiwayat(iso) {
  const d = new Date(iso.replace(' ', 'T'));
  if (isNaN(d)) return '';
  const now = new Date();
  const sameDay = d.toDateString() === now.toDateString();
  return sameDay
    ? d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    : d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
}

function escapeHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

function sparkleSpans() {
  let out = '';
  for (let i = 0; i < 8; i++) out += `<span class="spk" style="--i:${i}"></span>`;
  return out;
}

function showTopToast(state, text) {
  let toast = document.getElementById('topToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'topToast';
    document.body.appendChild(toast);
  }
  toast.className = 'top-toast show ' + state;
  toast.innerHTML = state === 'loading'
    ? `<span class="toast-spinner"></span><span>${text}</span>`
    : `<span class="toast-check"><i data-lucide="check" class="lucide-ic"></i></span><span>${text}</span><span class="toast-sparkles">${sparkleSpans()}</span>`;
  if (window.lucide) lucide.createIcons();
}

function hideTopToast(delay = 1500) {
  const toast = document.getElementById('topToast');
  if (!toast) return;
  setTimeout(() => toast.classList.remove('show'), delay);
}

function tunggu(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function renderHistoryList(daftar) {
  historyList.innerHTML = '';
  if (!daftar || daftar.length === 0) {
    historyList.innerHTML = '<div class="history-empty">Belum ada riwayat obrolan. Mulai chat buat nyimpen riwayat pertamamu ✨</div>';
    return;
  }
  daftar.forEach(sesi => {
    const item = document.createElement('div');
    item.className = 'history-item' + (String(sesi.id_sesi) === String(idSesiAktif) ? ' active' : '');
    item.dataset.id = sesi.id_sesi;
    const waktu = formatWaktuRiwayat(sesi.diperbarui_pada || '');
    item.innerHTML = `
      <span class="h-title">${escapeHtml(sesi.judul)}${waktu ? ' <span style="color:var(--muted);font-size:.85em;">· '+escapeHtml(waktu)+'</span>' : ''}</span>
      <button type="button" class="h-del" title="Hapus obrolan" data-id="${sesi.id_sesi}">
        <i data-lucide="trash-2" class="lucide-ic" style="width:14px;height:14px;"></i>
      </button>`;
    item.addEventListener('click', (e) => {
      if (e.target.closest('.h-del')) return;
      muatSesi(sesi.id_sesi);
      closeSidebarMobile();
    });
    item.querySelector('.h-del').addEventListener('click', async (e) => {
      e.stopPropagation();
      if (!confirm('Hapus obrolan ini?')) return;

      showTopToast('loading', 'Menghapus obrolan...');
      const [hasil] = await Promise.all([
        fetch('chatbot_riwayat.php?aksi=hapus&id_sesi=' + sesi.id_sesi),
        tunggu(500) // biar animasi loading kelihatan, tidak kedip doang
      ]);

      if (String(sesi.id_sesi) === String(idSesiAktif)) mulaiObrolanBaru();
      await muatDaftarRiwayat();

      showTopToast('success', 'Obrolan berhasil dihapus ✨');
      hideTopToast();
    });
    historyList.appendChild(item);
  });
  if (window.lucide) lucide.createIcons();
}

async function muatDaftarRiwayat() {
  try {
    const res = await fetch('chatbot_riwayat.php?aksi=daftar');
    const data = await res.json();
    renderHistoryList(data.daftar || []);
  } catch (err) {
    // diamkan, sidebar tetap kosong kalau gagal ambil riwayat
  }
}

async function muatSesi(idSesi) {
  try {
    const res = await fetch('chatbot_riwayat.php?aksi=ambil&id_sesi=' + idSesi);
    const data = await res.json();
    if (data.error) return;

    idSesiAktif = idSesi;
    history = [];
    chatBody.innerHTML = '';
    (data.pesan || []).forEach(p => {
      const role = p.peran === 'user' ? 'user' : 'bot';
      addMessage(role, p.isi);
      history.push({ role, text: p.isi });
    });
    if (quickChips) quickChips.style.display = 'none';
    document.querySelectorAll('.history-item').forEach(el => {
      el.classList.toggle('active', String(el.dataset.id) === String(idSesi));
    });
  } catch (err) {
    addMessage('bot', 'Gagal memuat obrolan ini. Coba lagi ya 🙏');
  }
}

function mulaiObrolanBaru() {
  idSesiAktif = null;
  history = [];
  chatBody.innerHTML = '';
  addMessage('bot', SAPAAN_AWAL);
  if (quickChips) quickChips.style.display = 'flex';
  document.querySelectorAll('.history-item').forEach(el => el.classList.remove('active'));
  chatInput.focus();
}

function openSidebarMobile() {
  sidebarEl.classList.add('open');
  sidebarOverlay.classList.add('open');
}
function closeSidebarMobile() {
  sidebarEl.classList.remove('open');
  sidebarOverlay.classList.remove('open');
}
if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebarMobile);
if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebarMobile);
if (newChatBtn) newChatBtn.addEventListener('click', () => { mulaiObrolanBaru(); closeSidebarMobile(); });

muatDaftarRiwayat();

function showTyping() {
  const el = document.createElement('div');
  el.className = 'msg bot typing';
  el.id = 'typingIndicator';
  el.innerHTML = '<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>';
  chatBody.appendChild(el);
  chatBody.scrollTop = chatBody.scrollHeight;
}
function hideTyping() {
  const el = document.getElementById('typingIndicator');
  if (el) el.remove();
}

async function sendMessage(text) {
  text = text.trim();
  if (!text) return;

  addMessage('user', text);
  history.push({ role: 'user', text });
  chatInput.value = '';
  sendBtn.disabled = true;
  if (quickChips) quickChips.style.display = 'none';

  showTyping();

  try {
    const res = await fetch('chatbot_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text, history, id_sesi: idSesiAktif })
    });
    const data = await res.json();
    hideTyping();

    if (data.reply) {
      addMessage('bot', data.reply);
      history.push({ role: 'bot', text: data.reply });
      if (data.id_sesi) {
        idSesiAktif = data.id_sesi;
        muatDaftarRiwayat(); // refresh biar sesi ini naik ke urutan teratas & judulnya update
      }
    } else {
      addMessage('bot', 'Waduh, ada gangguan nih: ' + (data.error || 'Coba lagi ya.') + ' 🙏');
    }
  } catch (err) {
    hideTyping();
    addMessage('bot', 'Gagal terhubung ke server. Coba lagi bentar ya 🙏');
  }

  sendBtn.disabled = false;
  chatInput.focus();
}

chatForm.addEventListener('submit', function (e) {
  e.preventDefault();
  sendMessage(chatInput.value);
});

quickChips.querySelectorAll('.chip').forEach(chip => {
  chip.addEventListener('click', () => sendMessage(chip.dataset.q));
});
</script>
</body>
</html>
