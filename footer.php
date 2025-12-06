<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Library Management System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    /* Footer Styles */
    footer {
      flex-shrink: 0;
      background: #333;
      color: #eee;
      text-align: center;
      font-size: 14px;
      padding: 15px 0;
      margin-top: 10px;
    }

    /* Chat Icon */
    #aichatbot-icon {
      position: fixed;
      bottom: 60px;
      right: 24px;
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
      border-radius: 50%;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
      cursor: pointer;
      z-index: 1001;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      transition: all 0.3s;
      border: none;
      overflow: hidden;
    }
    
    #aichatbot-icon img {
      width: 35px;
      height: 35px;
      object-fit: cover;
    }
    
    #aichatbot-icon:hover { 
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    /* Chat Window */
    #aichatbot-window {
      display: none;
      flex-direction: column;
      position: fixed;
      bottom: 130px;
      right: 24px;
      width: 380px;
      height: 550px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.15);
      z-index: 1001;
      overflow: hidden;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Header */
    #aichatbot-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      padding: 18px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-shrink: 0;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    #aichatbot-header-text {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    #aichatbot-header-text img {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,0.3);
    }
    
    #aichatbot-close {
      background: none;
      border: none;
      color: #fff;
      font-size: 20px;
      cursor: pointer;
      padding: 0;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: background 0.2s;
    }
    
    #aichatbot-close:hover {
      background: rgba(255,255,255,0.2);
    }

    /* Clear Messages Button */
    #aichatbot-clear {
      background: none;
      border: none;
      color: rgba(255,255,255,0.8);
      font-size: 16px;
      cursor: pointer;
      padding: 0;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: all 0.2s;
      margin-right: 8px;
    }

    #aichatbot-clear:hover {
      background: rgba(255,255,255,0.2);
      color: #fff;
    }

    #aichatbot-clear i {
      font-size: 14px;
    }

    /* Messages Container */
    #aichatbot-messages {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    /* Message Wrapper with Avatar */
    .message-wrapper {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
      from { 
        opacity: 0; 
        transform: translateY(10px); 
      }
      to { 
        opacity: 1; 
        transform: translateY(0); 
      }
    }

    .message-wrapper.user {
      flex-direction: row-reverse;
    }

    .message-wrapper.bot {
      flex-direction: row;
    }

    /* Avatar Styling */
    .message-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 600;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .message-avatar img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }

    .user .message-avatar {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
    }

    .bot .message-avatar {
      background: #fff;
      border: 2px solid #667eea;
    }

    /* Message Content */
    .message-content {
      max-width: 70%;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .message-name {
      font-size: 12px;
      color: #6c757d;
      font-weight: 500;
      margin-left: 4px;
    }

    .user .message-name {
      text-align: right;
      margin-right: 4px;
      margin-left: 0;
    }

    .message-bubble {
      padding: 12px 16px;
      border-radius: 18px;
      word-wrap: break-word;
      font-size: 14px;
      line-height: 1.5;
      position: relative;
    }

    .user .message-bubble {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff;
      border-bottom-right-radius: 4px;
      box-shadow: 0 2px 5px rgba(102, 126, 234, 0.3);
    }

    .bot .message-bubble {
      background: #fff;
      color: #333;
      border-bottom-left-radius: 4px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    }

    .message-time {
      font-size: 11px;
      color: #9ca3af;
      margin-top: 2px;
      margin-left: 4px;
    }

    .user .message-time {
      text-align: right;
      margin-right: 4px;
      margin-left: 0;
    }

    /* Typing indicator */
    .typing-indicator {
      display: flex;
      gap: 4px;
      padding: 12px 16px;
      background: #fff;
      border-radius: 18px;
      border-bottom-left-radius: 4px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.08);
      width: fit-content;
    }

    .typing-indicator span {
      width: 8px;
      height: 8px;
      background: #667eea;
      border-radius: 50%;
      animation: typing 1.4s infinite;
    }

    .typing-indicator span:nth-child(2) {
      animation-delay: 0.2s;
    }

    .typing-indicator span:nth-child(3) {
      animation-delay: 0.4s;
    }

    @keyframes typing {
      0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.5;
      }
      30% {
        transform: translateY(-10px);
        opacity: 1;
      }
    }

    /* Input Container */
    #aichatbot-input-container {
      flex-shrink: 0;
      padding: 15px 20px;
      background: #fff;
      border-top: 1px solid #e0e0e0;
      display: flex;
      gap: 10px;
      align-items: center;
    }

    /* Input Box */
    #aichatbot-input-box {
      flex: 1;
      padding: 12px 18px;
      border: 2px solid #e0e0e0;
      border-radius: 25px;
      outline: none;
      font-size: 14px;
      transition: all 0.3s;
      background: #f8f9fa;
    }
    
    #aichatbot-input-box:focus {
      border-color: #667eea;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    #aichatbot-input-box::placeholder {
      color: #9ca3af;
    }

    /* Send Button */
    #aichatbot-send-btn {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      transition: all 0.3s;
      box-shadow: 0 2px 5px rgba(102, 126, 234, 0.3);
    }
    
    #aichatbot-send-btn:hover:not(:disabled) {
      transform: scale(1.1);
      box-shadow: 0 4px 10px rgba(102, 126, 234, 0.4);
    }
    
    #aichatbot-send-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    /* Welcome Message */
    .welcome-message {
      text-align: center;
      padding: 30px 20px;
      color: #6c757d;
      font-size: 14px;
      background: white;
      border-radius: 12px;
      margin: 20px 0;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .welcome-message i {
      font-size: 48px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 15px;
    }

    /* Error Message */
    .error-message {
      background: #fff5f5;
      color: #e53e3e;
      padding: 12px;
      border-radius: 8px;
      margin: 10px 0;
      font-size: 13px;
      border-left: 4px solid #e53e3e;
    }

    /* Scrollbar Styling */
    #aichatbot-messages::-webkit-scrollbar {
      width: 6px;
    }
    #aichatbot-messages::-webkit-scrollbar-track {
      background: transparent;
    }
    #aichatbot-messages::-webkit-scrollbar-thumb {
      background: #cbd5e0;
      border-radius: 3px;
    }
    #aichatbot-messages::-webkit-scrollbar-thumb:hover {
      background: #a0aec0;
    }

    /* Responsive Design */
    @media (max-width: 480px) {
      #aichatbot-window {
        width: calc(100vw - 20px);
        height: calc(100vh - 100px);
        right: 10px;
        bottom: 80px;
      }
    }
  </style>
</head>
<body>

<main>
  <!-- Your page content here -->
</main>

<!-- AI Chatbot -->
<div id="aichatbot-icon" title="<?= $lang['chat_with_ai'] ?? 'Chat with Library Assistant' ?>">
  <img src="images/ai.png" alt="AI Assistant" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-comments\'></i>';">
</div>

<div id="aichatbot-window">
  <div id="aichatbot-header">
    <div id="aichatbot-header-text">
      <img src="images/ai.png" alt="AI" onerror="this.style.display='none';">
      <span><?= $lang['chat_title'] ?? 'Library Assistant' ?></span>
    </div>
    <div style="display: flex; align-items: center;">
      <button id="aichatbot-clear" aria-label="Clear messages" title="Clear all messages">
        <i class="fas fa-trash"></i>
      </button>
      <button id="aichatbot-close" aria-label="Close chat">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>
  
  <div id="aichatbot-messages">
    <div class="welcome-message">
      <i class="fas fa-robot"></i>
      <h3></h3>
      <p><?= $lang['welcome_message'] ?? 'I can help you with borrowing books, library hours, fines, or any library-related questions.' ?></p>
    </div>
  </div>
  
  <div id="aichatbot-input-container">
    <input type="text" id="aichatbot-input-box" placeholder="<?= $lang['chat_placeholder'] ?? 'Type your message...' ?>" autocomplete="off">
    <button id="aichatbot-send-btn" aria-label="Send message">
      <i class="fas fa-paper-plane"></i>
    </button>
  </div>
</div>

<footer>
  <p>© <?= date('Y') ?> <?= $lang['footer_text'] ?? 'Library Management System' ?></p>
</footer>

<script>
(function() {
  const icon = document.getElementById('aichatbot-icon');
  const windowEl = document.getElementById('aichatbot-window');
  const closeBtn = document.getElementById('aichatbot-close');
  const clearBtn = document.getElementById('aichatbot-clear');
  const input = document.getElementById('aichatbot-input-box');
  const sendBtn = document.getElementById('aichatbot-send-btn');
  const messages = document.getElementById('aichatbot-messages');
  
  let isProcessing = false;

  // Get user avatar from PHP session (you'll need to pass this from PHP)
  const userAvatar = '<?= htmlspecialchars($userAvatarPath ?? "images/default_avatar.png") ?>';
  const username = '<?= htmlspecialchars($username ?? "You") ?>';

  // Format time
  function formatTime(date) {
    return date.toLocaleTimeString('en-US', { 
      hour: 'numeric', 
      minute: '2-digit',
      hour12: true 
    });
  }

  // Create message element with avatar
  function createMessage(text, type) {
    const wrapper = document.createElement('div');
    wrapper.classList.add('message-wrapper', type);
    
    // Create avatar
    const avatar = document.createElement('div');
    avatar.classList.add('message-avatar');
    
    if (type === 'user') {
      // User avatar
      if (userAvatar && userAvatar !== 'images/default_avatar.png') {
        avatar.innerHTML = `<img src="${userAvatar}" alt="User">`;
      } else {
        // Fallback to initial
        avatar.textContent = username.charAt(0).toUpperCase();
      }
    } else {
      // Bot avatar
      avatar.innerHTML = `<img src="images/ai.png" alt="AI" onerror="this.style.display='none'; this.parentElement.innerHTML='AI';">`;
    }
    
    // Create message content container
    const content = document.createElement('div');
    content.classList.add('message-content');
    
    // Add name
    const name = document.createElement('div');
    name.classList.add('message-name');
    name.textContent = type === 'user' ? username : 'Library Assistant';
    
    // Add message bubble
    const bubble = document.createElement('div');
    bubble.classList.add('message-bubble');
    bubble.textContent = text;
    
    // Add timestamp
    const time = document.createElement('div');
    time.classList.add('message-time');
    time.textContent = formatTime(new Date());
    
    // Assemble message
    content.appendChild(name);
    content.appendChild(bubble);
    content.appendChild(time);
    
    wrapper.appendChild(avatar);
    wrapper.appendChild(content);
    
    return wrapper;
  }

  // Save chat messages to localStorage
  function saveMessages() {
    const msgs = [];
    document.querySelectorAll('#aichatbot-messages .message-wrapper').forEach(msg => {
      const bubble = msg.querySelector('.message-bubble');
      const name = msg.querySelector('.message-name');
      const time = msg.querySelector('.message-time');
      if (bubble) {
        msgs.push({
          type: msg.classList.contains('user') ? 'user' : 'bot',
          text: bubble.textContent,
          name: name ? name.textContent : '',
          time: time ? time.textContent : ''
        });
      }
    });
    localStorage.setItem('aichatbotMessages', JSON.stringify(msgs));
  }

  // Load chat messages from localStorage
  function loadMessages() {
    const msgs = JSON.parse(localStorage.getItem('aichatbotMessages') || '[]');
    msgs.forEach(msg => {
      const messageEl = createMessage(msg.text, msg.type);
      // Update saved time if available
      const timeEl = messageEl.querySelector('.message-time');
      if (timeEl && msg.time) {
        timeEl.textContent = msg.time;
      }
      messages.appendChild(messageEl);
    });
    messages.scrollTop = messages.scrollHeight;
  }

  // Clear messages function
  function clearMessages() {
    // Remove all message wrappers
    const messageWrappers = messages.querySelectorAll('.message-wrapper');
    messageWrappers.forEach(msg => msg.remove());
    
    // Remove any error messages
    const errorMessages = messages.querySelectorAll('.error-message');
    errorMessages.forEach(msg => msg.remove());
    
    // Add back the welcome message
const welcomeDiv = document.createElement('div');
welcomeDiv.className = 'welcome-message';
welcomeDiv.innerHTML = `
  <i class="fas fa-robot"></i>
  <?= isset($lang['welcome_message']) ? $lang['welcome_message'] : '' ?>
  <h3></h3>
  <p><?= isset($lang['help_text']) ? $lang['help_text'] : 'I can help you with borrowing books, library hours, fines, or any library-related questions.' ?></p>
`;
messages.appendChild(welcomeDiv);
// Clear localStorage
    localStorage.removeItem('aichatbotMessages');
    
    // Reset scroll
    messages.scrollTop = 0;
  }

  // Save chat window state
  function saveChatState(isOpen) {
    localStorage.setItem('aichatbotOpen', isOpen ? '1' : '0');

  }

  // Load chat window state
  function loadChatState() {
    const state = localStorage.getItem('aichatbotOpen');
    if (state === '1') {
      windowEl.style.display = 'flex';
      input.focus();
      const welcome = messages.querySelector('.welcome-message');
      if (welcome && messages.children.length > 1) {
        welcome.remove();
      }
    }
  }

  // Load messages and state on page load
  loadMessages();
  loadChatState();

  // Toggle chat window
  if (icon && windowEl) {
    icon.addEventListener('click', () => {
      const isVisible = windowEl.style.display === 'flex';
      windowEl.style.display = isVisible ? 'none' : 'flex';
      saveChatState(!isVisible);
      if (!isVisible) input.focus();
      const welcome = messages.querySelector('.welcome-message');
      if (welcome && messages.children.length > 1) welcome.remove();
    });
  }

  // Close button
  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      windowEl.style.display = 'none';
      saveChatState(false);
    });
  }

  // Clear button event listener
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      if (confirm('Are you sure you want to clear all messages?')) {
        clearMessages();
      }
    });
  }

  // Send message function
  async function sendMessage() {
    const userText = input.value.trim();
    if (!userText || isProcessing) return;
    
    isProcessing = true;
    sendBtn.disabled = true;
    
    const welcome = messages.querySelector('.welcome-message');
    if (welcome) welcome.remove();
    
    // Add user message with avatar
    const userMessage = createMessage(userText, 'user');
    messages.appendChild(userMessage);
    saveMessages();
    
    input.value = '';
    
    // Add typing indicator with bot avatar
    const typingWrapper = document.createElement('div');
    typingWrapper.classList.add('message-wrapper', 'bot');
    
    const botAvatar = document.createElement('div');
    botAvatar.classList.add('message-avatar');
    botAvatar.innerHTML = `<img src="images/ai.png" alt="AI" onerror="this.style.display='none'; this.parentElement.innerHTML='AI';">`;
    
    const typingContent = document.createElement('div');
    typingContent.classList.add('message-content');
    typingContent.innerHTML = `
      <div class="message-name">Library Assistant</div>
      <div class="typing-indicator">
        <span></span><span></span><span></span>
      </div>
    `;
    
    typingWrapper.appendChild(botAvatar);
    typingWrapper.appendChild(typingContent);
    messages.appendChild(typingWrapper);
    messages.scrollTop = messages.scrollHeight;
    
    try {
      const response = await fetch('aichatbot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: userText })
      });
      const data = await response.json();
      
      typingWrapper.remove();
      
      // Add bot message with avatar
      const botMessage = createMessage(
        data.reply || 'Sorry, I could not process your request.',
        'bot'
      );
      messages.appendChild(botMessage);
      saveMessages();
      
    } catch (error) {
      typingWrapper.remove();
      const errorDiv = document.createElement('div');
      errorDiv.classList.add('error-message');
      errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Connection error. Please try again.';
      messages.appendChild(errorDiv);
    }
    
    messages.scrollTop = messages.scrollHeight;
    isProcessing = false;
    sendBtn.disabled = false;
    input.focus();
  }

  // Send on button click
  if (sendBtn) sendBtn.addEventListener('click', sendMessage);

  // Send on Enter key
  if (input) {
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });
  }

  // Escape key to close chat
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && windowEl.style.display === 'flex') {
      windowEl.style.display = 'none';
      saveChatState(false);
    }
  });
})();
</script>

<script src="js/profile.js" defer></script>
</body>
</html>