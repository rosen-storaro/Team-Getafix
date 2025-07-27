<?php
$title = 'User Management - School Inventory Management System';
ob_start();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">User Management</h1>
                    <p class="text-muted">Manage user accounts and permissions</p>
                </div>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle me-2"></i>Add User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">All Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="usersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading users...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="firstName" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role *</label>
                        <select class="form-select" id="role" name="role_id" required>
                            <option value="">Select Role</option>
                            <option value="1">User</option>
                            <option value="2">Admin</option>
                            <?php if (($_SESSION['role_name'] ?? '') === 'Super-admin'): ?>
                            <option value="3">Super-admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Password must be at least 8 characters with uppercase, lowercase, number, and special character.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$scripts = '<script>
document.addEventListener("DOMContentLoaded", function() {
    loadUsers();
    
    document.getElementById("addUserForm").addEventListener("submit", function(e) {
        e.preventDefault();
        addUser();
    });
});

async function loadUsers() {
    try {
        const response = await fetch("/api/auth/users", {
            credentials: "same-origin"
        });
        if (response.ok) {
            const data = await response.json();
            renderUsersTable(data.users || []);
        } else {
            console.error("Failed to load users");
        }
    } catch (error) {
        console.error("Error loading users:", error);
    }
}

function renderUsersTable(users) {
    const tbody = document.querySelector("#usersTable tbody");
    if (users.length === 0) {
        tbody.innerHTML = "<tr><td colspan=\"7\" class=\"text-center text-muted\">No users found</td></tr>";
        return;
    }
    
    tbody.innerHTML = users.map(user => 
        "<tr>" +
            "<td>" + escapeHtml(user.first_name + " " + user.last_name) + "</td>" +
            "<td>" + escapeHtml(user.username) + "</td>" +
            "<td>" + escapeHtml(user.email) + "</td>" +
            "<td><span class=\"badge bg-primary\">" + escapeHtml(user.role_name || user.role) + "</span></td>" +
            "<td><span class=\"badge bg-" + getStatusColor(user.status) + "\">" + user.status + "</span></td>" +
            "<td>" + formatDate(user.created_at) + "</td>" +
            "<td>" +
                "<button class=\"btn btn-sm btn-outline-primary me-1\" onclick=\"editUser(" + user.id + ")\">Edit</button>" +
                (user.status === "Pending" ? 
                    "<button class=\"btn btn-sm btn-success me-1\" onclick=\"approveUser(" + user.id + ")\">Approve</button>" : "") +
                "<button class=\"btn btn-sm btn-outline-danger\" onclick=\"deleteUser(" + user.id + ")\">Delete</button>" +
            "</td>" +
        "</tr>"
    ).join("");
}

function getStatusColor(status) {
    switch(status) {
        case "Active": return "success";
        case "Pending": return "warning";
        case "Inactive": return "secondary";
        default: return "secondary";
    }
}

async function addUser() {
    const formData = new FormData(document.getElementById("addUserForm"));
    const userData = Object.fromEntries(formData);
    
    try {
        const response = await fetch("/api/auth/register", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            credentials: "same-origin",
            body: JSON.stringify(userData)
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById("addUserModal")).hide();
            document.getElementById("addUserForm").reset();
            loadUsers();
            showAlert("User added successfully", "success");
        } else {
            const error = await response.json();
            showAlert(error.error || "Failed to add user", "danger");
        }
    } catch (error) {
        console.error("Error adding user:", error);
        showAlert("Failed to add user", "danger");
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString();
}

function showAlert(message, type) {
    const alertDiv = document.createElement("div");
    alertDiv.className = "alert alert-" + type + " alert-dismissible fade show";
    alertDiv.innerHTML = message + 
        "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>";
    
    const container = document.querySelector(".container-fluid");
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>';

include __DIR__ . '/../layout.php';
?>

