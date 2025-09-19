<!-- Floating Chat Button -->
<button id="toggleChat" class="btn btn-primary position-fixed bottom-0 end-0 m-4" style="z-index: 1050;">
    Chat Support
</button>

<!-- Hidden Chat Widget -->
<div id="chatWidgetBox" class="card shadow-lg position-fixed bottom-0 end-0 m-4" style="width: 350px; display: none; z-index: 1050;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Support Chat</strong>
        <button id="closeChat" class="btn btn-sm btn-danger">×</button>
    </div>
    <div class="card-body p-2" style="height: 350px; overflow-y: auto;">
        <div class="row">
            <div class="col-12">
                <h6 id="chatWith" class="mt-2">Chat</h6>
                <div id="chatBox" class="chat-box mb-2 bg-light rounded" style="height: 200px; overflow-y: auto;"></div>
                <div class="input-group">
                    <input type="text" id="adminMessage" class="form-control" placeholder="Type your reply...">
                    <button class="btn btn-primary" id="sendReply">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Sound -->
<audio id="notificationSound" src="assets/notify.mp3" preload="auto"></audio>

<script>let selectedEmail = null;
let lastMessageCount = 0;

document.getElementById('toggleChat').addEventListener('click', () => {
    document.getElementById('chatWidgetBox').style.display = 'block';
    loadUserList();
});

document.getElementById('closeChat').addEventListener('click', () => {
    document.getElementById('chatWidgetBox').style.display = 'none';
});

// Load Artists Only for Admin
function loadUserList() {
    fetch('admin_chat_backend.php?user_list=1') // Fetch only artist emails
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('userList');
            list.innerHTML = '';
            data.forEach(email => {
                const li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action';
                li.textContent = email;
                li.onclick = () => {
                    selectedEmail = email;
                    document.getElementById('chatWith').textContent = `Chat with: ${email}`;
                    loadChat();
                };
                list.appendChild(li);
            });
        });
}

// Load Chat for Selected Artist
function loadChat() {
    if (!selectedEmail) return;
    fetch(`admin_chat_backend.php?user_email=${selectedEmail}`) // Get messages from the selected artist
        .then(res => res.json())
        .then(data => {
            const chatBox = document.getElementById('chatBox');
            const currentCount = data.length;
            const isNewMessage = currentCount > lastMessageCount;

            chatBox.innerHTML = '';
            data.forEach(msg => {
                const div = document.createElement('div');
                div.className = `chat-message p-2 mb-1 rounded ${msg.sender === 'admin' ? 'bg-primary text-white text-end' : 'bg-white border text-start'}`;
                div.textContent = msg.message;
                chatBox.appendChild(div);
            });

            if (isNewMessage && lastMessageCount > 0) {
                document.getElementById('notificationSound').play();
            }

            lastMessageCount = currentCount;
            chatBox.scrollTop = chatBox.scrollHeight;
        });
}

// Send Reply (Admin only replies to artist messages)
document.getElementById('sendReply').addEventListener('click', () => {
    const message = document.getElementById('adminMessage').value.trim();  // Get the trimmed message
    if (!message || !selectedEmail) {
        alert("Please type a message before sending.");
        return;  // If the message is empty, exit the function
    }

    fetch('admin_chat_backend.php', {
        method: 'POST',
        body: JSON.stringify({ message, email: selectedEmail }),  // Send the message with email
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('adminMessage').value = '';  // Clear input after sending
            loadChat();  // Refresh the chat
        } else {
            alert("Failed to send message. Please try again.");
        }
    })
    .catch(error => {
        console.error("Error sending message:", error);
        alert("An error occurred. Please try again.");
    });
});

// Auto Refresh Chat Every 5 Seconds (for new messages)
setInterval(() => {
    if (selectedEmail) loadChat();
}, 5000);

</script>
