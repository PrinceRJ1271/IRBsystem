// assets/js/ai-chat.js

(function () {
  const el = {
    root:    document.getElementById('aiChat'),
    body:    document.getElementById('aiBody'),
    input:   document.getElementById('aiInput'),
    send:    document.getElementById('aiSend'),
    chips:   document.getElementById('aiChips'),
    close:   document.getElementById('aiClose'),
    minimize:document.getElementById('aiMinimize'),
    launcher:document.getElementById('aiLauncher'),
    resize:  document.getElementById('aiResize'),
  };

  if (!el.root) return;

  const API_URL = '/ai/chat_api.php';  // absolute path so it works on every page
  const history = [];

  // ---------- Helpers ----------
  function scrollToBottom() {
    el.body.scrollTop = el.body.scrollHeight;
  }

  function bubble(role, html) {
    const wrap = document.createElement('div');
    wrap.className = `ai-chat__bubble ai-chat__bubble--${role}`;
    wrap.innerHTML = `<div class="ai-chat__bubble-in">${html}</div>`;
    el.body.appendChild(wrap);
    scrollToBottom();
  }

  function esc(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function setBusy(busy) {
    el.input.disabled = busy;
    el.send.disabled  = busy;
  }

  function typingOn() {
    const wrap = document.createElement('div');
    wrap.id = 'aiTypingRow';
    wrap.className = 'ai-chat__bubble ai-chat__bubble--assistant';
    wrap.innerHTML = `<div class="ai-chat__bubble-in"><span class="ai-typing">
      <span class="dot"></span><span class="dot"></span><span class="dot"></span>
    </span></div>`;
    el.body.appendChild(wrap);
    scrollToBottom();
  }
  function typingOff() {
    const n = document.getElementById('aiTypingRow');
    if (n) n.remove();
  }

  // ---------- Send ----------
  async function ask(msg) {
    if (!msg) return;
    bubble('user', esc(msg));
    setBusy(true);
    typingOn();

    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ message: msg, history })
      });

      const isJson = res.headers.get('content-type')?.includes('application/json');
      const data = isJson ? await res.json() : { ok:false, error:`HTTP ${res.status}` };

      typingOff();
      setBusy(false);

      if (!res.ok || !data.ok) {
        const reason = data.error || `HTTP ${res.status}`;
        bubble('assistant', `<div style="color:#c0392b"><strong>Sorry</strong> — ${esc(reason)}.</div>`);
        return;
      }

      history.push({ role:'user', content: msg });
      history.push({ role:'assistant', content: data.reply });

      bubble('assistant', esc(data.reply).replace(/\n/g,'<br>'));
    } catch (e) {
      typingOff();
      setBusy(false);
      bubble('assistant', `<div style="color:#c0392b">Network error: ${esc(e.message || e)}</div>`);
    }
  }

  el.send.addEventListener('click', () => {
    const msg = el.input.value.trim();
    if (!msg) return;
    el.input.value = '';
    ask(msg);
  });

  el.input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      el.send.click();
    }
  });

  // Suggestion chips
  if (el.chips) {
    el.chips.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-suggest]');
      if (!btn) return;
      const t = btn.getAttribute('data-suggest');
      ask(t);
    });
  }

  // Minimize / Close / Launcher
  el.minimize.addEventListener('click', () => {
    el.root.style.display = 'none';
    el.launcher.style.display = 'inline-flex';
  });
  el.close.addEventListener('click', () => {
    el.root.style.display = 'none';
    el.launcher.style.display = 'inline-flex';
  });
  el.launcher.addEventListener('click', () => {
    el.root.style.display = 'flex';
    el.launcher.style.display = 'none';
  });

  // Simple height resize
  (function enableResize() {
    let startY = 0;
    let startH = 0;
    function onMove(e) {
      const dy = (e.touches ? e.touches[0].clientY : e.clientY) - startY;
      const newH = Math.max(360, startH + dy * -1);
      el.root.style.height = newH + 'px';
    }
    function end() {
      window.removeEventListener('mousemove', onMove);
      window.removeEventListener('touchmove', onMove);
      window.removeEventListener('mouseup', end);
      window.removeEventListener('touchend', end);
    }
    el.resize.addEventListener('mousedown', (e) => {
      startY = e.clientY; startH = el.root.getBoundingClientRect().height;
      window.addEventListener('mousemove', onMove);
      window.addEventListener('mouseup', end);
    });
    el.resize.addEventListener('touchstart', (e) => {
      startY = e.touches[0].clientY; startH = el.root.getBoundingClientRect().height;
      window.addEventListener('touchmove', onMove, {passive:false});
      window.addEventListener('touchend', end);
    });
  })();

})();
