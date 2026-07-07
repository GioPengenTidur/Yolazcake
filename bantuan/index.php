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

    .chat-wrap{
      position:relative;z-index:2;width:100%;max-width:640px;height:min(760px,88vh);
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
      width:46px;height:46px;border-radius:14px;flex-shrink:0;
      background:linear-gradient(135deg,var(--purple),var(--rose) 60%,var(--gold));
      display:flex;align-items:center;justify-content:center;font-size:1.3em;
      box-shadow:0 4px 14px rgba(0,0,0,.35);
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

    @media (max-width:480px){
      .chat-wrap{height:92vh;border-radius:20px;}
      .msg{max-width:88%;}
    }
  </style>
</head>
<body>

<div class="chat-wrap">
  <div class="chat-head">
    <div class="chat-avatar"><i data-lucide="sparkles" class="lucide-ic"></i></div>
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

<script src="https://unpkg.com/lucide@latest"></script>
<script>
if (window.lucide) lucide.createIcons();

const chatBody   = document.getElementById('chatBody');
const chatForm   = document.getElementById('chatForm');
const chatInput  = document.getElementById('chatInput');
const sendBtn    = document.getElementById('sendBtn');
const quickChips = document.getElementById('quickChips');

let history = []; // {role: 'user'|'bot', text: '...'}

function addMessage(role, text) {
  const el = document.createElement('div');
  el.className = 'msg ' + role;
  el.textContent = text;
  chatBody.appendChild(el);
  chatBody.scrollTop = chatBody.scrollHeight;
  return el;
}

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
      body: JSON.stringify({ message: text, history })
    });
    const data = await res.json();
    hideTyping();

    if (data.reply) {
      addMessage('bot', data.reply);
      history.push({ role: 'bot', text: data.reply });
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
