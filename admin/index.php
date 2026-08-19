<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Reminder Management</h3>
    <button class="btn btn-primary" onclick="openModal()">+ Create Reminder</button>
</div>

<!-- Search and Filters -->
<div class="card p-3 mb-4">
    <div class="row g-2">
        <div class="col-md-5">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by Title, Chat ID, Message...">
        </div>
        <div class="col-md-4">
            <select id="filterSelect" class="form-select">
                <option value="">All Statuses & Dates</option>
                <option value="today">Today's Reminders</option>
                <option value="pending">Pending</option>
                <option value="sent">Sent</option>
                <option value="failed">Failed</option>
                <option value="last7">Past 7 Days</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-secondary w-100" onclick="loadReminders()">Apply Filter</button>
        </div>
    </div>
</div>

<!-- Reminders Table -->
<div class="card p-3">
    <table class="table table-bordered table-striped" id="remindersTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Scheduled Time</th>
                <th>Recipients</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal Form -->
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="reminderForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Save Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reminder_id" name="reminder_id">
                    <div class="mb-3">
                        <label class="form-label">Reminder Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Schedule Date & Time</label>
                        <input type="datetime-local" id="scheduled_time" name="scheduled_time" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recipients (Select Multiple)</label>
                        <select multiple id="recipients" name="recipients[]" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Messages (In Sequence)</label>
                        <div id="messageContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addMessageRow()">+ Add Next Message</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Reminder</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let modal;
$(document).ready(function() {
    modal = new bootstrap.Modal(document.getElementById('reminderModal'));
    loadReminders();
    loadUsersSelect();
});

function loadReminders() {
    let s = $('#searchInput').val();
    let f = $('#filterSelect').val();
    $.get('../api/reminders.php?action=fetch', { search: s, filter: f }, function(res) {
        let html = '';
        if (res.data.length === 0) {
            html = '<tr><td colspan="6" class="text-center">No reminders found</td></tr>';
        } else {
            res.data.forEach(r => {
                let badge = 'bg-warning';
                if (r.status === 'sent') badge = 'bg-success';
                if (r.status === 'failed') badge = 'bg-danger';
                if (r.status === 'partial') badge = 'bg-info';

                html += `<tr>
                    <td>${r.id}</td>
                    <td><b>${escapeHtml(r.title)}</b></td>
                    <td>${r.scheduled_time}</td>
                    <td><small>${escapeHtml(r.recipients || 'None')}</small></td>
                    <td><span class="badge ${badge}">${r.status.toUpperCase()}</span></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editReminder(${r.id})">Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteReminder(${r.id})">Delete</button>
                    </td>
                </tr>`;
            });
        }
        $('#remindersTable tbody').html(html);
    });
}

function loadUsersSelect() {
    $.get('../api/users.php?action=fetch', function(res) {
        let options = '';
        res.data.forEach(u => {
            options += `<option value="${u.chat_id}">${escapeHtml(u.name)} (${u.chat_id})</option>`;
        });
        $('#recipients').html(options);
    });
}

function addMessageRow(val = '') {
    let row = `<div class="input-group mb-2 msg-row">
        <input type="text" name="messages[]" class="form-control" value="${escapeHtml(val)}" placeholder="Type message sequence item..." required>
        <button type="button" class="btn btn-danger" onclick="$(this).parent().remove()">X</button>
    </div>`;
    $('#messageContainer').append(row);
}

function openModal() {
    $('#reminder_id').val('');
    $('#reminderForm')[0].reset();
    $('#messageContainer').html('');
    addMessageRow();
    modal.show();
}

$('#reminderForm').on('submit', function(e) {
    e.preventDefault();
    $.post('../api/reminders.php?action=save', $(this).serialize(), function(res) {
        if (res.status === 'success') {
            modal.hide();
            loadReminders();
        } else {
            alert(res.message);
        }
    });
});

function editReminder(id) {
    $.get('../api/reminders.php?action=get', { id: id }, function(res) {
        if (res.status === 'success') {
            $('#reminder_id').val(res.reminder.id);
            $('#title').val(res.reminder.title);
            $('#scheduled_time').val(res.reminder.scheduled_time.replace(' ', 'T'));
            $('#recipients').val(res.recipients);
            $('#messageContainer').html('');
            res.messages.forEach(m => addMessageRow(m));
            modal.show();
        }
    });
}

function deleteReminder(id) {
    if (confirm("Are you sure you want to delete this reminder?")) {
        $.post('../api/reminders.php?action=delete', { id: id }, function() {
            loadReminders();
        });
    }
}

function escapeHtml(str) {
    return str ? String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') : '';
}
</script>
</body>
</html>