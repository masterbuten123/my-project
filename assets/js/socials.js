// ===== REPORT MODAL LOGIC =====
const reportModalEl = document.getElementById('reportModal');
const reportModal = reportModalEl ? new bootstrap.Modal(reportModalEl) : null;
const reportForm = document.getElementById('reportForm');
const reportPostId = document.getElementById('reportPostId');
const reportReason = document.getElementById('reportReason');
const otherBox = document.getElementById('otherReasonBox');
const otherReason = document.getElementById('otherReason');

document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.report-post-btn');
    if (!btn) return;
    const postId = btn.dataset.postid;
    reportPostId.value = postId;
    reportReason.value = '';
    otherReason.value = '';
    otherBox.classList.add('d-none');
    reportModal.show();
});

reportReason.addEventListener('change', () => {
    otherBox.classList.toggle('d-none', reportReason.value !== 'Other');
});

reportForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    let reason = reportReason.value;
    if (!reason) return;

    if (reason === 'Other') {
        reason = otherReason.value.trim();
        if (!reason) { otherReason.focus(); return; }
    }

    const params = new URLSearchParams({ report_post: 1, post_id: reportPostId.value, reason });

    try {
        const res = await fetch('network_backend.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        });
        const text = await res.text();
        let msg = text, ok = true;
        try { const json = JSON.parse(text); msg = json.message || text; ok = (json.status === 'success'); } catch {}

        if (typeof Swal !== 'undefined') {
            Swal.fire(ok ? 'Reported' : 'Oops', msg, ok ? 'success' : 'error');
        } else alert(msg);

        if (ok) {
            const btn = document.querySelector(`.report-post-btn[data-postid="${reportPostId.value}"]`);
            if (btn) {
                btn.disabled = true;
                btn.classList.add('btn-secondary');
                btn.innerHTML = `<i class="bi bi-flag-fill"></i> Reported`;
                btn.title = 'You already reported this post';
            }
            reportModal.hide();
        }
    } catch (err) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Something went wrong sending your report.', 'error');
        } else alert('Something went wrong sending your report.');
    }
});

// ===== DOCUMENT READY =====
document.addEventListener('DOMContentLoaded', () => {
    // ---------- ELEMENTS ----------
    const postsContainer = document.getElementById('postsContainer');
    const connectionsContainer = document.getElementById('connectionsContainer');
    const invitationsContainer = document.getElementById('invitationsContainer');
    const suggestedContainer = document.getElementById('suggestedContainer');
    const friendsContainer = document.getElementById('connectionsList');
    const searchInput = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");
    const chatBox = document.getElementById('chatBox');
    const chatBoxBody = document.getElementById('chatBoxBody');
    const chatBoxUser = document.getElementById('chatBoxUser');
    const chatInput = document.getElementById('chatInput');
    const postImage = document.getElementById('postImage');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');
    let currentChatUser = null;

    // EDIT POST MODAL
    const editPostModalEl = document.getElementById('editPostModal');
    const editPostModal = new bootstrap.Modal(editPostModalEl);
    const editPostForm = document.getElementById('editPostForm');
    const editPostImage = document.getElementById('editPostImage');
    const editPreviewImage = document.getElementById('editPreviewImage');
    const editFileName = document.getElementById('editFileName');
    const editUploadBtn = document.getElementById('editUploadBtn');

    // ---------- CLICK HANDLERS ----------
    document.body.addEventListener('click', e => {
        const target = e.target;

        // ===== EDIT POST =====
        const editBtn = target.closest('.edit-post-btn');
        if (editBtn) {
            const postBox = editBtn.closest('.main-box');
            const postId = editBtn.dataset.postid;
            const content = postBox.querySelector('p').innerText;

            document.getElementById('editPostContent').value = content;
            document.getElementById('editPostId').value = postId;
            editPreviewImage.classList.add('d-none');
            editFileName.textContent = '';
            editPostModal.show();
        }

        // ===== FRIEND ACTIONS =====
        if (target.classList.contains('addFriendBtn')) postAction(`send_request=1&target_id=${target.dataset.id}`, () => { loadSuggested(); loadInvitations(); });
        if (target.classList.contains('acceptBtn')) postAction(`accept_request=1&request_id=${target.dataset.id}`, () => { loadConnections(); loadInvitations(); });
        if (target.classList.contains('rejectBtn')) postAction(`reject_request=1&request_id=${target.dataset.id}`, loadInvitations);
        if (target.classList.contains('removeFriendBtn')) postAction(`remove_friend=1&friend_id=${target.dataset.id}`, () => { loadConnections(); loadSuggested(); });

        // ===== LIKE POST =====
        const likeBtn = target.closest('.like-post-btn');
        if (likeBtn) {
            const postId = likeBtn.dataset.postid;
            postAction(`like_post=1&post_id=${postId}`, data => {
                if (data.status === 'success') {
                    Swal.fire('Liked!', data.message, 'success');
                    likeBtn.classList.toggle('btn-primary');
                    likeBtn.classList.toggle('btn-outline-primary');
                    if (likeBtn.querySelector('.like-count')) likeBtn.querySelector('.like-count').textContent = data.likes_count || 0;
                } else Swal.fire('Error', data.message, 'error');
            }, true);
        }

        // ===== SHARE POST =====
        const shareBtn = target.closest('.share-post-btn');
        if (shareBtn) {
            const postId = shareBtn.dataset.postid;
            navigator.clipboard.writeText(window.location.href + '?post=' + postId)
                .then(() => Swal.fire('Copied!', 'Post URL copied to clipboard.', 'success'));
        }

        // ===== CHAT USER =====
        const chatCard = target.closest('.chat-user-card');
        if (chatCard) {
            currentChatUser = chatCard.dataset.userid;
            chatBoxUser.textContent = chatCard.querySelector('span').textContent;
            chatBox.style.display = 'flex';
            loadMessages(currentChatUser);
        }

        // CLOSE CHAT
        if (target.id === 'closeChatBtn') chatBox.style.display = 'none';
    });

    // ===== IMAGE UPLOAD PREVIEW =====
    document.getElementById('uploadBtn').addEventListener('click', () => postImage.click());
    postImage.addEventListener('change', () => {
        const file = postImage.files[0];
        if (file) {
            fileName.textContent = file.name;
            const reader = new FileReader();
            reader.onload = e => {
                previewImage.src = e.target.result;
                previewImage.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            previewImage.classList.add('d-none');
            fileName.textContent = '';
        }
    });
    document.getElementById('addPostModal').addEventListener('hidden.bs.modal', () => {
        previewImage.classList.add('d-none');
        fileName.textContent = '';
        document.getElementById('addPostForm').reset();
    });

    // ===== EDIT POST IMAGE =====
    editUploadBtn.addEventListener('click', () => editPostImage.click());
    editPostImage.addEventListener('change', () => {
        const file = editPostImage.files[0];
        if (!file) return;
        editFileName.textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => {
            editPreviewImage.src = e.target.result;
            editPreviewImage.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });

    // ===== FETCH HELPERS =====
    const fetchData = (body, callback) => fetch('network_backend.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body }).then(r => r.text()).then(callback).catch(console.error);
    const fetchJson = (body, callback) => fetch('network_backend.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body }).then(r => r.json()).then(callback).catch(console.error);
    const postAction = (body, callback, expectJson = false) => fetch('network_backend.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body }).then(r => expectJson ? r.json() : r.text()).then(callback).catch(console.error);

    // ===== LOAD DATA =====
    const loadPosts = () => fetchData('load_posts=1', html => postsContainer.innerHTML = html);
    const loadConnections = () => fetchData('load_connections=1', html => connectionsContainer.innerHTML = html);
    const loadInvitations = () => fetchData('load_friends_dropdown=1', html => invitationsContainer.innerHTML = html);
    const loadSuggested = () => fetchData('load_suggested=1', html => suggestedContainer.innerHTML = html);
    const notificationsContainer = document.getElementById('notificationsDropdown');
    const notifBadge = document.getElementById('notifBadge');
    const loadFriends = () => fetchJson('load_chat_friends=1', data => {
        friendsContainer.innerHTML = '';
        if (data.status === 'success' && data.data.length) {
            data.data.forEach(f => {
                friendsContainer.innerHTML += `<div class="chat-user-card" data-userid="${f.account_id}">
                    <img src="uploads/profiles/${f.image || '2.jpg'}" alt="Profile">
                    <span>${f.name}</span>
                </div>`;
            });
        } else friendsContainer.innerHTML = "<p>No friends found.</p>";
    });
    const loadNotifications = () => fetchData('load_notifications=1', html => {
        notificationsContainer.innerHTML = html;
    });

    // Mark notifications as read when dropdown opens
    document.getElementById('notificationsDropdown').addEventListener('show.bs.dropdown', () => {
        fetch('network_backend.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'mark_notifications_read=1'
        }).then(() => loadNotifications());
    });
    const loadNotifCount = () => fetch('network_backend.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'count_notifications=1'
}).then(r => r.json()).then(data => {
    notifBadge.textContent = data.count > 0 ? data.count : '';
});


    // INITIAL LOAD
    loadPosts(); loadConnections(); loadInvitations(); loadSuggested(); loadFriends(); loadNotifications();

        setInterval(() => {
        loadPosts();
        loadNotifications(); // refresh notifications too
    }, 60000);

    // ===== ADD POST =====
    document.getElementById("addPostForm").addEventListener("submit", function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        fetch('network_backend.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    postsContainer.insertAdjacentHTML('afterbegin', data.post_html);
                    Swal.fire('Success', data.message, 'success');
                    this.reset();
                    previewImage.classList.add('d-none');
                    fileName.textContent = '';
                    bootstrap.Modal.getInstance(document.getElementById('addPostModal')).hide();
                } else Swal.fire('Error', data.message, 'error');
            }).catch(console.error);
    });

    // ===== SEND CHAT =====
    document.getElementById('sendChatBtn').addEventListener('click', () => {
        const msg = chatInput.value.trim();
        if (!msg || !currentChatUser) return;
        const form = new URLSearchParams({ send_message: 1, receiver_id: currentChatUser, message: msg });
        fetch('network_backend.php', { method: 'POST', body: form }).then(r => r.json()).then(data => {
            if (data.status === 'success') { chatInput.value = ''; loadMessages(currentChatUser); } else alert(data.message);
        }).catch(console.error);
    });

    function loadMessages(friendId) {
        const form = new URLSearchParams({ load_messages: 1, chat_with: friendId });
        fetch('network_backend.php', { method: 'POST', body: form }).then(r => r.json()).then(data => {
            if (data.status === 'success') {
                chatBoxBody.innerHTML = '';
                data.messages.forEach(msg => {
                    let div = document.createElement('div');
                    div.textContent = msg.message;
                    div.className = msg.sender_id == currentChatUser ? 'message-received' : 'message-sent';
                    chatBoxBody.appendChild(div);
                });
                chatBoxBody.scrollTop = chatBoxBody.scrollHeight;
            }
        }).catch(console.error);
    }

    // ===== LIVE SEARCH =====
    searchInput.addEventListener('keyup', () => {
        const keyword = searchInput.value.trim();
        if (keyword.length < 2) { searchResults.style.display = 'none'; searchResults.innerHTML = ''; return; }
        fetchJson('search_user=1&keyword=' + encodeURIComponent(keyword), data => {
            searchResults.innerHTML = '';
            if (data.status === 'success' && data.data.length) {
                data.data.forEach(user => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `<a href="profile.php?id=${user.account_id}" class="text-decoration-none flex-grow-1">${user.name}</a>
                        <button class="btn btn-sm btn-success addFriendBtn" data-id="${user.account_id}">Add</button>`;
                    searchResults.appendChild(item);
                });
            } else searchResults.innerHTML = `<div class="list-group-item">No users found</div>`;
            searchResults.style.display = 'block';
        });
    });
    document.addEventListener('click', e => { if (!document.getElementById('searchForm').contains(e.target)) { searchResults.style.display = 'none'; } });
});

// REMOVE leftover modal backdrop
document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
