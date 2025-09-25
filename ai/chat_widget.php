<?php
// ai/chat_widget.php
?>
<link rel="stylesheet" href="/assets/vendors/mdi/css/materialdesignicons.min.css">
<style>
  /* Floating button */
  .ai-fab {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 1250;
    width: 56px; height: 56px;
    border: 0; border-radius: 16px;
    background: #4B49AC; color: #fff;
    box-shadow: 0 8px 24px rgba(75,73,172,.28);
    display: inline-flex; align-items: center; justify-content: center;
  }
  .ai-fab:hover { filter: brightness(1.06); }
  .ai-fab i { font-size: 24px; }

  /* Drawer card */
  .ai-card {
    position: fixed;
    right: 22px;
    bottom: 88px;
    width: 360px;
    max-height: 70vh;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0,0,0,.12);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 1250;
  }
  .ai-card.active { display: flex; }

  .ai-header {
    padding: .9rem 1rem;
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(90deg, #4B49AC, #6f7bf7);
    color: #fff;
  }
  .ai-header h6 { margin: 0; font-weight: 600; }
  .ai-close {
    background: transparent; border: 0; color: #fff; font-size: 20px;
  }
  .ai-body {
    padding: 1rem;
    overflow: auto;
    background: #fafbff;
  }
  .ai-msg { display: flex; margin-bottom: .75rem; }
  .ai-msg .bubble {
    padding: .6rem .75rem; border-radius: 12px; line-height: 1.35;
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
    max-width: 85%;
    white-space: pre-wrap;
  }
  .ai-msg.user .bubble {
    margin-left: auto;
    background: #e8ecff; color: #1b1f3b;
  }
  .ai-msg.bot .bubble {
    background: #fff; color: #111827; border: 1px solid #eef2ff;
  }
  .ai-footer {
    border-top: 1px solid #eef0f6;
    padding: .75rem;
    background: #fff;
  }
  .ai-footer .input-group > .form-control { border-radius: .75rem; }
  .ai-footer .btn { border-radius: .75rem; }
  @media (max-width: 480px) {
    .ai-card { right: 12px; left: 12px; width: auto; }
  }
</style>

<!-- Floating Button -->
<button class="ai-fab" id="aiFab" title="Chat with assistant">
  <i class="mdi mdi-robot-outline"></i>
</button>

<!-- Chat Drawer -->
<div class="ai-card" id="aiCard" role="dialog" aria-label="Assistant chat">
  <div class="ai-header">
    <h6 class="mb-0">Assistant</h6>
    <button class="ai-close" id="aiClose" aria-label="Close">
      <i class="mdi mdi-close"></i>
    </button>
  </div>
  <div class="ai-body" id="aiBody" aria-live="polite">
    <div class="ai-msg bot"><div class="bubble">
      Hi! I can help you navigate the IRB Letter System, summarize steps,
      and answer questions about letters, follow-ups, and delivery workflow.
    </div></div>
  </div>
  <div class="ai-footer">
    <div class="input-group">
      <input type="text" class="form-control" id="aiInput" placeholder="Type a question…" autocomplete="off">
      <button class="btn btn-primary" id="aiSend"><i class="mdi mdi-send"></i></button>
    </div>
  </div>
</div>

<script src="/assets/js/ai-chat.js"></script>
