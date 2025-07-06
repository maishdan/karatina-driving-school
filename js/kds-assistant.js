// Elements
const chatForm = document.getElementById('chat-form');
const userInput = document.getElementById('user-input');
const chatMessages = document.getElementById('chat-messages');

// Simulated smart response
function getBotResponse(message) {
  message = message.toLowerCase();

  if (message.includes("hello") || message.includes("hi")) {
    return "Hello! 👋 I'm KDS Assistant. How can I help you today?";
  } else if (message.includes("book")) {
    return "Sure! You can book a driving lesson via the 'Book Appointment' button or visit book.html.";
  } else if (message.includes("fees") || message.includes("cost")) {
    return "Our Class B course costs KES 14,500. Let me know if you'd like a full brochure.";
  } else if (message.includes("location")) {
    return "We're located in Karatina Town, Nyeri County. View us on the map above.";
  } else if (message.includes("balance") || message.includes("my account")) {
    return "Please log in first so I can check your student account securely.";
  } else if (message.includes("requirements") || message.includes("documents")) {
    return "You'll need your National ID, NTSA TIMS Account, and a passport photo.";
  } else if (message.includes("language") || message.includes("swahili")) {
    return "Tunaweza ongea Kiswahili! Uliza swali lako.";
  } else if (message.includes("goodbye") || message.includes("bye")) {
    return "Goodbye! Stay safe on the road. 🚗💨";
  } else {
    return "I'm still learning. Please ask about lessons, costs, requirements, or booking!";
  }
}

// Add message to chat window
function addMessage(sender, text) {
  const msg = document.createElement('div');
  msg.classList.add('chat-message', sender);
  msg.innerHTML = `<p>${text}</p>`;
  chatMessages.appendChild(msg);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Event listener for form
chatForm.addEventListener('submit', (e) => {
  e.preventDefault();
  const message = userInput.value.trim();
  if (!message) return;

  addMessage('user', message);
  userInput.value = '';
  
  // Bot typing simulation
  setTimeout(() => {
    const botReply = getBotResponse(message);
    addMessage('bot', botReply);
  }, 800);
});
