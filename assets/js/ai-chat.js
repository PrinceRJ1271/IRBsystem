// assets/js/ai-chat.js
(() => {
  const $ = (sel, root=document) => root.querySelector(sel);
  const chat    = $('#irb-chat');
  const fab     = $('#irbChatFab');
  const sendBtn = $('#irbChatSend');
  const input   = $('#irbChatInput');
  const body    = $('.irb-chat__body', chat);
  const typing  = $('#irbTyping');
  const minBtn  = $('#irbChatMinBtn');
  const closeBtn= $('#irbChatCloseBtn');

  const SUGGESTION_CLASS = 'chip';
  const API_URL = '/ai/chat_ai.php'; // server endpoint

  let history = []; // [{role:'user'|'assistant', content:string}]

  function open()  { chat.classList.add('open'); }
  function close() { chat.classList.remove('open'); }
  fab.addEventListener('click', open);
  closeBtn.addEventListener('click', close);
  minBtn.addEventListener('click', () => chat.classList.toggle('open'));

  // suggested chips
  body.addEventListener('click', e => {
    const chip = e.target.closest('.'+SUGGESTION_CLASS);
    if (!chip) return;
    input.value = chip.dataset.q || chip.textContent.trim();
    input.focus();
  });

  function addMsg(role, text, isError=false) {
    const wrap = document.createElement('div');
    wrap.className = `irb-msg irb-msg--${role}`;
    wrap.innerHTML = `
      <div class="irb-msg__avatar">
        <i class="mdi ${role === 'user' ? 'mdi-account-circle' : 'mdi-robot'}"></i>
      </div>
      <div class="irb-msg__bubble ${isError ? 'irb-error':''}"></div>
    `;
    wrap.querySelector('.irb-msg__bubble').textContent = text;
    body.appendChild(wrap);
    body.scrollTop = body.scrollHeight;
  }

  async function ask(msg) {
    history.push({ role:'user', content: msg });
    addMsg('user', msg);
    input.value = '';
    typing.hidden = false;
    body.scrollTop = body.scrollHeight;

    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ message: msg, history })
      });

      const data = await res.json().catch(() => ({}));

      typing.hidden = true;

      if (!data || !data.ok) {
        const err = data && data.error ? data.error : `Network error (${res.status})`;
        addMsg('assistant', err, true);
        return;
      }

      history.push({ role:'assistant', content: data.reply });
      addMsg('assistant', data.reply);
    } catch (e) {
      typing.hidden = true;
      addMsg('assistant', 'Network error. Please try again.', true);
    }
  }

  sendBtn.addEventListener('click', () => {
    const msg = input.value.trim();
    if (msg) ask(msg);
  });
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      const msg = input.value.trim();
      if (msg) ask(msg);
    }
  });
})();
