<?php
// ai/chat_widget.php
// The floating widget that mounts on every page (included in footer)
?>
<div id="irb-chat" class="irb-chat shadow">
  <div class="irb-chat__head">
    <div class="irb-chat__title">
      <i class="mdi mdi-robot"></i> Assistant
    </div>
    <div class="irb-chat__actions">
      <button class="irb-icon-btn" id="irbChatMinBtn" title="Minimize"><i class="mdi mdi-window-minimize"></i></button>
      <button class="irb-icon-btn" id="irbChatCloseBtn" title="Close"><i class="mdi mdi-close"></i></button>
    </div>
  </div>

  <div class="irb-chat__body">
    <div class="irb-msg irb-msg--assistant">
      <div class="irb-msg__avatar"><i class="mdi mdi-robot"></i></div>
      <div class="irb-msg__bubble">
        Hi! I can help you navigate the IRB Letter System, summarize steps, and answer questions about letters, follow-ups, and delivery workflow.
      </div>
    </div>

    <div class="irb-suggestions">
      <button class="chip" data-q="Which companies need follow-ups?">Which companies need follow-ups?</button>
      <button class="chip" data-q="How do I record a letter delivery?">How do I record a letter delivery?</button>
      <button class="chip" data-q="Export last month’s letters to Excel">Export last month’s letters to Excel</button>
    </div>
    <div class="irb-typing" id="irbTyping" hidden>
      <span class="dot"></span><span class="dot"></span><span class="dot"></span>
    </div>
  </div>

  <div class="irb-chat__input">
    <input id="irbChatInput" type="text" class="form-control" placeholder="Type a question…" />
    <button id="irbChatSend" class="btn btn-primary"><i class="mdi mdi-send"></i></button>
  </div>
</div>

<button id="irbChatFab" class="irb-fab btn btn-primary shadow-lg" aria-label="Open assistant">
  <i class="mdi mdi-message-processing-outline"></i>
</button>
