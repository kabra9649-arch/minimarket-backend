<!-- ════════════════════════════════════════
     NEXSYS AI — Widget de Chat Flotante
     Incluir con: include 'views/layouts/nexsys_widget.php';
     ════════════════════════════════════════ -->
<style>
/* ── NEXSYS WIDGET ── */
#nexsys-btn {
  position: fixed; bottom: 24px; right: 24px; z-index: 9000;
  width: 58px; height: 58px; border-radius: 50%;
  background: linear-gradient(135deg, #1F4E79, #2E75B6);
  border: none; cursor: pointer;
  box-shadow: 0 4px 20px rgba(31,78,121,0.45);
  display: flex; align-items: center; justify-content: center;
  transition: all .3s;
  animation: nexsysPulse 3s ease-in-out infinite;
}
#nexsys-btn:hover { transform: scale(1.1); box-shadow: 0 6px 28px rgba(31,78,121,0.6); }
@keyframes nexsysPulse {
  0%,100%{box-shadow:0 4px 20px rgba(31,78,121,0.45)}
  50%{box-shadow:0 4px 28px rgba(46,117,182,0.7),0 0 0 8px rgba(46,117,182,0.1)}
}
#nexsys-btn svg { width: 26px; height: 26px; fill: #fff; }
#nexsys-badge {
  position: absolute; top: -4px; right: -4px;
  width: 18px; height: 18px; border-radius: 50%;
  background: #F59E0B; color: #fff;
  font-size: 10px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
}

#nexsys-panel {
  position: fixed; bottom: 92px; right: 24px; z-index: 9001;
  width: 340px; max-height: 520px;
  background: #fff; border-radius: 20px;
  box-shadow: 0 12px 48px rgba(0,0,0,0.18);
  display: none; flex-direction: column;
  overflow: hidden;
  animation: nexsysSlideIn .3s cubic-bezier(0.34,1.56,0.64,1);
}
#nexsys-panel.open { display: flex; }
@keyframes nexsysSlideIn {
  from { opacity:0; transform:scale(0.85) translateY(20px); }
  to   { opacity:1; transform:scale(1) translateY(0); }
}

.nexsys-header {
  background: linear-gradient(135deg, #1F4E79, #2E75B6);
  padding: 16px 18px; display: flex; align-items: center; gap: 12px;
}
.nexsys-avatar {
  width: 40px; height: 40px; border-radius: 50%;
  background: rgba(255,255,255,0.15);
  border: 2px solid rgba(255,255,255,0.3);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; flex-shrink: 0;
}
.nexsys-header-info { flex: 1; }
.nexsys-header-info .name { color: #fff; font-weight: 700; font-size: 14px; }
.nexsys-header-info .status { color: rgba(255,255,255,0.7); font-size: 11px; display: flex; align-items: center; gap: 4px; }
.nexsys-status-dot { width: 7px; height: 7px; border-radius: 50%; background: #10B981; animation: nexsysBlink 2s infinite; }
@keyframes nexsysBlink { 0%,100%{opacity:1} 50%{opacity:.3} }
.nexsys-close { background: none; border: none; color: rgba(255,255,255,0.7); font-size: 18px; cursor: pointer; padding: 4px; line-height: 1; }
.nexsys-close:hover { color: #fff; }

.nexsys-messages {
  flex: 1; overflow-y: auto; padding: 16px;
  display: flex; flex-direction: column; gap: 10px;
  min-height: 200px; max-height: 320px;
  background: #F8FAFC;
}
.nexsys-messages::-webkit-scrollbar { width: 4px; }
.nexsys-messages::-webkit-scrollbar-track { background: transparent; }
.nexsys-messages::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }

.msg-bubble {
  max-width: 85%; padding: 10px 14px;
  border-radius: 16px; font-size: 13px; line-height: 1.5;
  animation: bubbleIn .25s ease;
}
@keyframes bubbleIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
.msg-bubble.bot {
  background: #fff; color: #1a1a2e;
  border-radius: 4px 16px 16px 16px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
  align-self: flex-start;
}
.msg-bubble.user {
  background: linear-gradient(135deg, #1F4E79, #2E75B6);
  color: #fff;
  border-radius: 16px 4px 16px 16px;
  align-self: flex-end;
}
.msg-bubble.typing {
  background: #fff; align-self: flex-start;
  border-radius: 4px 16px 16px 16px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
  padding: 12px 16px;
}
.typing-dots { display: flex; gap: 4px; align-items: center; }
.typing-dots span {
  width: 7px; height: 7px; border-radius: 50%;
  background: #94A3B8; animation: typingAnim 1.2s infinite;
}
.typing-dots span:nth-child(2) { animation-delay: .2s; }
.typing-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes typingAnim { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }

.msg-time { font-size: 10px; opacity: .55; margin-top: 3px; }

.nexsys-input-area {
  padding: 12px 14px; border-top: 1px solid #E2E8F0;
  background: #fff; display: flex; gap: 8px; align-items: center;
}
.nexsys-input {
  flex: 1; border: 1.5px solid #E2E8F0; border-radius: 12px;
  padding: 9px 14px; font-size: 13px; outline: none;
  font-family: 'Segoe UI', sans-serif; resize: none;
  transition: border-color .2s;
}
.nexsys-input:focus { border-color: #2E75B6; }
.nexsys-send {
  width: 38px; height: 38px; border-radius: 50%; border: none;
  background: linear-gradient(135deg, #1F4E79, #2E75B6);
  color: #fff; cursor: pointer; display: flex;
  align-items: center; justify-content: center;
  flex-shrink: 0; transition: all .2s;
}
.nexsys-send:hover { transform: scale(1.1); }
.nexsys-send:disabled { opacity: .5; cursor: not-allowed; transform: none; }

.nexsys-footer {
  text-align: center; padding: 6px;
  font-size: 10px; color: #94A3B8;
  border-top: 1px solid #F1F5F9; background: #fff;
}
.nexsys-footer span { color: #1F4E79; font-weight: 600; }

/* Sugerencias rápidas */
.nexsys-suggestions {
  display: flex; flex-wrap: wrap; gap: 6px;
  padding: 0 16px 10px;
  background: #F8FAFC;
}
.nexsys-chip {
  font-size: 11px; padding: 5px 12px; border-radius: 20px;
  border: 1px solid #D6E4F0; background: #fff;
  color: #1F4E79; cursor: pointer; transition: all .2s;
  white-space: nowrap;
}
.nexsys-chip:hover { background: #1F4E79; color: #fff; border-color: #1F4E79; }

@media (max-width: 480px) {
  #nexsys-panel { width: calc(100vw - 32px); right: 16px; bottom: 84px; }
  #nexsys-btn { right: 16px; bottom: 16px; }
}
</style>

<!-- Botón flotante -->
<button id="nexsys-btn" onclick="toggleNexsys()" title="NEXSYS AI">
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.37 5.07L2 22l4.93-1.37C8.42 21.5 10.15 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm-1 14H7v-2h4v2zm6 0h-4v-2h4v2zm0-4H7V8h10v4z"/>
  </svg>
  <div id="nexsys-badge" style="display:none">1</div>
</button>

<!-- Panel de chat -->
<div id="nexsys-panel">
  <div class="nexsys-header">
    <div class="nexsys-avatar">🤖</div>
    <div class="nexsys-header-info">
      <div class="name">NEXSYS AI</div>
      <div class="status"><span class="nexsys-status-dot"></span>En línea · MiniMarket G2</div>
    </div>
    <button class="nexsys-close" onclick="toggleNexsys()">✕</button>
  </div>

  <div class="nexsys-messages" id="nexsys-msgs">
    <div class="msg-bubble bot">
      ¡Hola! 👋 Soy <strong>NEXSYS</strong>, tu asistente virtual de MiniMarket G2. ¿En qué puedo ayudarte hoy?
      <div class="msg-time">Ahora</div>
    </div>
  </div>

  <div class="nexsys-suggestions" id="nexsys-chips">
    <div class="nexsys-chip" onclick="sendChip(this)">📦 Ver productos</div>
    <div class="nexsys-chip" onclick="sendChip(this)">🚴 ¿Hacen delivery?</div>
    <div class="nexsys-chip" onclick="sendChip(this)">💰 ¿Costo de envío?</div>
    <div class="nexsys-chip" onclick="sendChip(this)">🕐 Horarios</div>
  </div>

  <div class="nexsys-input-area">
    <input
      type="text"
      class="nexsys-input"
      id="nexsys-input"
      placeholder="Escribe tu mensaje..."
      onkeypress="if(event.key==='Enter')sendNexsys()"
    >
    <button class="nexsys-send" id="nexsys-send-btn" onclick="sendNexsys()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13"></line>
        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
      </svg>
    </button>
  </div>
  <div class="nexsys-footer">Potenciado por <span>NEXSYS AI</span> · MiniMarket G2</div>
</div>

<script>
const NEXSYS_CONTEXT = '<?= isset($user) ? "admin" : "cliente" ?>';
const NEXSYS_API = '/api/nexsys.php';

function toggleNexsys() {
  const panel = document.getElementById('nexsys-panel');
  panel.classList.toggle('open');
  document.getElementById('nexsys-badge').style.display = 'none';
  if (panel.classList.contains('open')) {
    setTimeout(() => document.getElementById('nexsys-input').focus(), 300);
  }
}

function addMsg(text, role) {
  const msgs = document.getElementById('nexsys-msgs');
  const div = document.createElement('div');
  div.className = 'msg-bubble ' + role;
  const time = new Date().toLocaleTimeString('es-DO', {hour:'2-digit',minute:'2-digit'});
  div.innerHTML = text + '<div class="msg-time">' + time + '</div>';
  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
  return div;
}

function showTyping() {
  const msgs = document.getElementById('nexsys-msgs');
  const div = document.createElement('div');
  div.className = 'msg-bubble typing';
  div.id = 'nexsys-typing';
  div.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
}

function hideTyping() {
  const t = document.getElementById('nexsys-typing');
  if (t) t.remove();
}

function sendChip(el) {
  const text = el.textContent.trim().replace(/^[^\w]+/, '');
  document.getElementById('nexsys-chips').style.display = 'none';
  sendNexsys(text);
}

async function sendNexsys(msgOverride) {
  const input = document.getElementById('nexsys-input');
  const btn = document.getElementById('nexsys-send-btn');
  const msg = msgOverride || input.value.trim();
  if (!msg) return;

  addMsg(msg, 'user');
  input.value = '';
  btn.disabled = true;
  showTyping();

  try {
    const res = await fetch(NEXSYS_API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ mensaje: msg, contexto: NEXSYS_CONTEXT })
    });
    const data = await res.json();
    hideTyping();
    addMsg(data.respuesta || 'Lo siento, intenta de nuevo.', 'bot');
  } catch {
    hideTyping();
    addMsg('Hubo un problema de conexión. Intenta de nuevo.', 'bot');
  } finally {
    btn.disabled = false;
    input.focus();
  }
}
</script>
