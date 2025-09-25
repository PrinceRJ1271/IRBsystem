<?php
// ai/chat_widget.php
// Drop-in widget. Include once (e.g., from includes/footer.php).
?>
<div id="aiChat" class="ai-chat ai-elevate">
  <div class="ai-chat__header">
    <div class="ai-chat__brand">
      <i class="mdi mdi-robot"></i>
      <span>Assistant</span>
      <small class="badge">IRB System</small>
    </div>
    <div class="ai-chat__actions">
      <button class="ai-btn ai-btn-icon" id="aiMinimize" title="Minimize"><i class="mdi mdi-minus"></i></button>
      <button class="ai-btn ai-btn-icon" id="aiClose" title="Close"><i class="mdi mdi-close"></i></button>
    </div>
  </div>

  <div class="ai-chat__body" id="aiBody">
    <div class="ai-chat__bubble ai-chat__bubble--assistant">
      <div class="ai-chat__bubble-in">
        <p>
          Hi! I can help with letters, follow-ups, KPIs and quick searches.
          Try:
        </p>
        <ul class="ai-list">
          <li>“Which companies need follow-ups?”</li>
          <li>“Show latest 8 letters.”</li>
          <li>“How many pending follow-ups now?”</li>
          <li>“Find client ‘Acme’.”</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="ai-chat__footer">
    <div class="ai-chipbar" id="aiChips">
      <button class="ai-chip" data-suggest="Which companies need follow-ups?">Which companies need follow-ups?</button>
      <button class="ai-chip" data-suggest="Show latest 8 letters">Latest 8 letters</button>
      <button class="ai-chip" data-suggest="How many pending follow-ups now?">Pending follow-ups</button>
    </div>
    <div class="ai-input">
      <input id="aiInput" type="text" placeholder="Type a question..." autocomplete="off" />
      <button id="aiSend" class="ai-btn ai-btn-primary"><i class="mdi mdi-send"></i></button>
    </div>
    <div class="ai-resize" id="aiResize"></div>
  </div>
</div>

<!-- Floating launcher (for minimized mode) -->
<button id="aiLauncher" class="ai-launcher ai-elevate" aria-label="Open Assistant">
  <i class="mdi mdi-message-text-outline"></i>
</button>
