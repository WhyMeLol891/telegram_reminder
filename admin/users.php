<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Telegram User Management</h3>
    <button class="btn btn-primary" onclick="openUserModal()">+ Add User</button>
</div>

<div class="card p-3">
    <table class="table table-bordered" id="usersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Telegram Chat ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="userForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" id="userName" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telegram Chat ID</label>
                        <input type="text" id="chatId" name="chat_id" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let userModal;
$(document).ready(function() {
    userModal = new bootstrap.Modal(document.getElementById('userModal'));
    loadUsers();
});

function loadUsers() {
    $.get('../api/users.php?action=fetch', function(res) {
        let html = '';
        res.data.forEach(u => {
            html += `<tr>
                <td>${u.id}</td>
                <td>${u.name}</td>
                <td><code>${u.chat_id}</code></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="editUser(${u.id}, '${u.name}', '${u.chat_id}')">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})">Delete</button>
                </td>
            </tr>`;
        });
        $('#usersTable tbody').html(html);
    });
}

function openUserModal() {
    $('#user_id').val('');
    $('#userForm')[0].reset();
    userModal.show();
}

function editUser(id, name, chatId) {
    $('#user_id').val(id);
    $('#userName').val(name);
    $('#chatId').val(chatId);
    userModal.show();
}

$('#userForm').on('submit', function(e) {
    e.preventDefault();
    $.post('../api/users.php?action=save', $(this).serialize(), function(res) {
        if (res.status === 'success') {
            userModal.hide();
            loadUsers();
        } else {
            alert(res.message);
        }
    });
});

function deleteUser(id) {
    if (confirm("Delete this user?")) {
        $.post('../api/users.php?action=delete', { id: id }, function() {
            loadUsers();
        });
    }
}
</script>
</body>
</html>