<?php
// ai/chat_widget.php
?>
<div id="aiChat" class="ai-chat shadow">
  <div class="ai-head">
    <div class="ai-title">
      <i class="mdi mdi-robot"></i> Assistant
      <small class="text-muted ms-2">IRB System</small>
    </div>
    <div class="ai-actions">
      <button type="button" class="ai-btn ai-min" title="Minimize"><i class="mdi mdi-window-minimize"></i></button>
      <button type="button" class="ai-btn ai-close" title="Close"><i class="mdi mdi-close"></i></button>
    </div>
  </div>

  <div class="ai-body">
    <div class="ai-msg ai-msg-bot">
      <div class="ai-bubble">
        Hi! I can help with letters, follow-ups, KPIs and quick searches.
        Ask things like:
        <ul class="mb-0">
          <li>“Which companies need follow-ups?”</li>
          <li>“Show latest 8 letters.”</li>
          <li>“How many pending follow-ups now?”</li>
          <li>“Find client ‘Acme’.”</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="ai-footer">
    <input id="aiInput" class="form-control" type="text" placeholder="Type a question..." />
    <button id="aiSend" class="btn btn-primary"><i class="mdi mdi-send"></i></button>
  </div>
</div>

<button id="aiFab" class="ai-fab btn btn-primary shadow">
  <i class="mdi mdi-message-text-outline"></i>
</button>
