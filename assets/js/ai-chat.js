// assets/js/ai-chat.js
(function(){
  const $chat  = document.getElementById('aiChat');
  const $fab   = document.getElementById('aiFab');
  const $body  = $chat?.querySelector('.ai-body');
  const $input = document.getElementById('aiInput');
  const $send  = document.getElementById('aiSend');
  const $min   = $chat?.querySelector('.ai-min');
  const $close = $chat?.querySelector('.ai-close');

  function show(){ if ($chat){ $chat.style.display='block'; $fab.style.display='none'; $input?.focus(); } }
  function hide(){ if ($chat){ $chat.style.display='none'; $fab.style.display='inline-flex'; } }
  function minimize(){ $chat.classList.toggle('is-min'); if ($chat.classList.contains('is-min')){ $body.style.display='none'; } else { $body.style.display='block'; } }
  function scrollBottom(){ setTimeout(()=>{ $body.scrollTop = $body.scrollHeight; }, 10); }

  function addMsg(role, html){
    const el = document.createElement('div');
    el.className = 'ai-msg ' + (role==='user'?'ai-msg-user':'ai-msg-bot');
    el.innerHTML = `<div class="ai-bubble">${html}</div>`;
    $body.appendChild(el);
    scrollBottom();
  }

  async function ask(question){
    const content = (question||'').trim();
    if(!content) return;
    addMsg('user', escapeHtml(content));
    $input.value='';

    try{
      const res = await fetch('/ai/chat_api.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ messages: [{role:'user', content}] })
      });
      const json = await res.json();
      addMsg('bot', renderMarkdown(json.reply||'Sorry, no reply.'));
    }catch(e){
      addMsg('bot', 'Network error. Please try again.');
    }
  }

  // Simple MD -> HTML (just line breaks + bullets)
  function renderMarkdown(t){
    return escapeHtml(t)
      .replace(/\n\- (.+)/g,'<br>• $1')
      .replace(/\n/g,'<br>');
  }

  function escapeHtml(s){ return s.replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

  // events
  $fab?.addEventListener('click', show);
  $close?.addEventListener('click', hide);
  $min?.addEventListener('click', minimize);

  $send?.addEventListener('click', ()=>ask($input.value));
  $input?.addEventListener('keydown', e => {
    if (e.key==='Enter' && !e.shiftKey){ e.preventDefault(); ask($input.value); }
  });
})();
