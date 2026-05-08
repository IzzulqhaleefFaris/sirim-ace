<?php
session_start();
include "../../../include/config.php";
/** @var mysqli $conn */
include "../../../include/permissions.php";

require_admin();

$users = [];
$error = null;

if (isset($conn) && $conn instanceof mysqli) {
    $res = $conn->query("SELECT userId, nama, email, roleId, status FROM user ORDER BY nama ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
    } else {
        $error = 'Unable to load user list.';
    }
} else {
    $error = 'Database connection is not available.';
}

$flash = $_SESSION['msg'] ?? null;
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <base href="">
    <meta charset="utf-8" />
    <title>Manage Users | ATTENDANCE SYSTEM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="../../../assets/media/logos/ace.png" />

    <!-- Global Javascript -->
    <script src="../../../assets/plugins/global/plugins.bundle.js"></script>
    <script src="../../../assets/js/scripts.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />

    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../../../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../../../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!--end::Global Stylesheets Bundle-->
    <style>
        .admin-table-card {
            border: 1px solid #f0f0f0;
        }

        .admin-table-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
        }

        .admin-table thead th {
            white-space: nowrap;
            font-weight: 600;
        }

        .admin-table tbody tr:hover {
            background-color: #f9fbff;
        }

        .search-input {
            height: 35px;
            width: 280px;
            max-width: 100%;
        }
    </style>
</head>

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed toolbar-tablet-and-mobile-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <?php include "../../../include/header.php"; ?>

                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <?php include "../../../include/toolbar.php"; ?>

                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <div id="kt_content_container" class="container py-6">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h2 class="fw-bold mb-0">Manage Users</h2>
                                    <p class="text-muted mb-0">Update user roles and status.</p>
                                </div>
                                <a href="home.php" class="btn btn-light border">
                                    <i class="bi bi-arrow-left me-1"></i> Back
                                </a>
                            </div>

                            <?php if ($flash): ?>
                                <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($flash['text'] ?? '') ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php else: ?>
                                <div class="card shadow-sm admin-table-card">
                                    <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 py-3">
                                        <div>
                                            <div class="fw-bold">User List</div>
                                            <div class="text-muted small">Quickly update roles and status.</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <input id="userSearch" type="search" class="form-control form-control-sm search-input" placeholder="Search name, email, or ID" />
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="d-flex justify-content-end mb-2">
                                            <span class="text-muted ">User count:</span>
                                            <span class="text-muted ms-1" id="userCount">0</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle table-striped admin-table" id="userTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="p-3 ">User ID</th>
                                                        <th class="p-3">Name</th>
                                                        <th class="p-3">Email</th>
                                                        <th class="p-3">Role</th>
                                                        <th class="p-3">Status</th>
                                                        <th class="p-3">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($users as $user): ?>
                                                        <?php
                                                            $searchText = strtolower(($user['userId'] ?? '') . ' ' . ($user['nama'] ?? '') . ' ' . ($user['email'] ?? ''));
                                                        ?>
                                                        <tr data-search="<?= htmlspecialchars($searchText) ?>">
                                                            <td class="p-3"><?= htmlspecialchars($user['userId']) ?></td>
                                                            <td class="p-3"><?= htmlspecialchars($user['nama']) ?></td>
                                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                                            <td class="p-3">
                                                                <form class="d-flex gap-2" method="POST" action="users-update.php">
                                                                    <input type="hidden" name="userId" value="<?= htmlspecialchars($user['userId']) ?>" />
                                                                    <select name="roleId" class="form-select form-select-sm" style="width: 160px;">
                                                                        <option value="1" <?= $user['roleId'] == 1 ? 'selected' : '' ?>>Organizer</option>
                                                                        <option value="2" <?= $user['roleId'] == 2 ? 'selected' : '' ?>>Participant</option>
                                                                        <option value="3" <?= $user['roleId'] == 3 ? 'selected' : '' ?>>Admin</option>
                                                                    </select>
                                                            </td>
                                                            <td class="p-3">
                                                                    <select name="status" class="form-select form-select-sm" style="width: 120px;">
                                                                        <option value="A" <?= $user['status'] === 'A' ? 'selected' : '' ?>>Active</option>
                                                                        <option value="D" <?= $user['status'] === 'D' ? 'selected' : '' ?>>Inactive</option>
                                                                    </select>
                                                            </td>
                                                            <td class="p-3" >
                                                                    <button class="btn btn-primary btn-sm" type="submit">Save</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php include "../../../include/footer.php"; ?>
            </div>
        </div>
    </div>

    <?php include "../../../include/scrolltop.php"; ?>
    <script>
        (function () {
            const searchInput = document.getElementById('userSearch');
            const rows = Array.from(document.querySelectorAll('#userTable tbody tr'));
            const countLabel = document.getElementById('userCount');

            function updateCount(visible) {
                if (!countLabel) return;
                countLabel.textContent = String(visible);
            }

            function applyFilter() {
                const query = (searchInput?.value || '').trim().toLowerCase();
                let visible = 0;

                rows.forEach((row) => {
                    const haystack = row.dataset.search || row.textContent.toLowerCase();
                    const show = query === '' || haystack.includes(query);
                    row.style.display = show ? '' : 'none';
                    if (show) visible += 1;
                });

                updateCount(visible);
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyFilter);
            }

            updateCount(rows.length);
        })();
    </script>
</body>
</html>
