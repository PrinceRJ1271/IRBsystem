// assets/js/ai-chat.js
(function () {
  const fab   = document.getElementById('aiFab');
  const card  = document.getElementById('aiCard');
  const close = document.getElementById('aiClose');
  const body  = document.getElementById('aiBody');
  const input = document.getElementById('aiInput');
  const send  = document.getElementById('aiSend');

  // Keep small chat history in memory (not persisted)
  const history = [];

  function scrollToBottom() {
    body.scrollTop = body.scrollHeight;
  }
  function addMsg(role, text) {
    const wrap = document.createElement('div');
    wrap.className = 'ai-msg ' + (role === 'user' ? 'user' : 'bot');
    const b = document.createElement('div');
    b.className = 'bubble';
    b.textContent = text;
    wrap.appendChild(b);
    body.appendChild(wrap);
    scrollToBottom();
  }

  function toggle(open) {
    card.classList[open ? 'add' : 'remove']('active');
    if (open) input.focus();
  }

  async function ask() {
    const msg = input.value.trim();
    if (!msg) return;
    input.value = '';
    addMsg('user', msg);

    // Optimistic typing indicator
    const typing = document.createElement('div');
    typing.className = 'ai-msg bot';
    typing.innerHTML = '<div class="bubble">Thinking…</div>';
    body.appendChild(typing);
    scrollToBottom();

    try {
      const res = await fetch('/ai/chat_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ message: msg, history })
      });
      const data = await res.json();
      typing.remove();

      if (!res.ok || data.error) {
        addMsg('bot', 'Sorry, I hit an error. Please try again.');
        console.error(data);
        return;
      }

      // Save turns
      history.push({ role: 'user', content: msg });
      history.push({ role: 'assistant', content: data.reply });

      addMsg('bot', data.reply);
    } catch (err) {
      typing.remove();
      addMsg('bot', 'Network error. Please try again.');
      console.error(err);
    }
  }

  fab.addEventListener('click', () => toggle(true));
  close.addEventListener('click', () => toggle(false));
  send.addEventListener('click', ask);
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') ask();
  });
})();
