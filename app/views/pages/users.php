<?php
$baseFolder = $baseFolder ?? '/pos-cite';
$message = $message ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | CITE POS</title>
    <link rel="icon" type="image/png" href="<?= $baseFolder ?>/assets/images/logo_it.png">
    <link rel="stylesheet" href="<?= $baseFolder ?>/assets/css/style.css">
</head>
<body class="min-h-screen bg-[#f5f7fc] font-sans text-[#17213a]">
<div class="flex min-h-screen">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="flex min-w-0 flex-1 flex-col">
        <?php require __DIR__ . '/../partials/header.php'; ?>
<main class="flex-1 px-4 py-7 sm:px-8"><div class="mx-auto max-w-350">
    <div class="mb-7"><p class="mb-1 text-sm font-semibold text-[#f7941d]">Administration</p><h2 class="text-3xl font-bold text-[#071947]">User Management</h2></div>
    <?php if ($message): ?><div class="mb-6 rounded-xl border px-4 py-3 text-sm <?= $message['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' ?>"><?= htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <div class="grid gap-6 xl:grid-cols-[340px_1fr]">
        <form class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" method="post">
            <input type="hidden" name="action" value="create_user"><h3 class="mb-5 text-lg font-bold text-[#071947]">Create account</h3>
            <label class="mb-4 block text-sm font-semibold text-slate-600">Username<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3" name="username" required></label>
            <label class="mb-4 block text-sm font-semibold text-slate-600">Full name<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3" name="full_name" required></label>
            <label class="mb-4 block text-sm font-semibold text-slate-600">Role<select class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3" name="role"><option value="cashier">Cashier</option><option value="officer">Officer</option><option value="admin">Admin</option></select></label>
            <label class="mb-4 block text-sm font-semibold text-slate-600">Password<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3" type="password" name="password" required></label>
            <label class="mb-6 block text-sm font-semibold text-slate-600">Confirm password<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3" type="password" name="confirm_password" required></label>
            <button class="min-h-11 w-full rounded-xl bg-[#123fa5] px-4 text-sm font-bold text-white" type="submit">Create account</button>
        </form>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-6 py-5"><h3 class="font-bold text-[#071947]">Accounts</h3></div>
            <div class="overflow-x-auto"><table class="w-full min-w-175 text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-6 py-3">Username</th><th class="px-6 py-3">Full name</th><th class="px-6 py-3">Role</th><th class="px-6 py-3">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
            <?php foreach ($users as $user): ?><tr><td class="px-6 py-4 font-semibold"><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td><td class="px-6 py-4"><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></td><td class="px-6 py-4 capitalize"><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></td><td class="px-6 py-4"><details><summary class="cursor-pointer font-bold text-[#123fa5]">Edit</summary><form class="mt-3 grid max-w-64 gap-2" method="post"><input type="hidden" name="action" value="edit_user"><input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>"><input class="rounded-lg border p-2" name="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" required><input class="rounded-lg border p-2" name="full_name" value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>" required><select class="rounded-lg border p-2" name="role"><?php foreach (['cashier', 'officer', 'admin'] as $role): ?><option value="<?= $role ?>" <?= $user['role'] === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option><?php endforeach; ?></select><input class="rounded-lg border p-2" type="password" name="password" placeholder="New password (optional)"><input class="rounded-lg border p-2" type="password" name="confirm_password" placeholder="Confirm new password"><button class="rounded-lg bg-[#123fa5] px-3 py-2 text-xs font-bold text-white" type="submit">Save</button></form></details><form class="mt-2" method="post" data-confirm="delete"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>"><button class="cursor-pointer text-xs font-bold text-red-600" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </section>
    </div>
</div></main>
        <?php require __DIR__ . '/../partials/footer.php'; ?>
    </div>
</div>
<script>
const sidebar = document.querySelector('[data-sidebar]'), overlay = document.querySelector('[data-menu-overlay]'), toggle = document.querySelector('[data-sidebar-toggle]');
document.querySelectorAll('[data-mobile-menu]').forEach((button) => button.addEventListener('click', () => { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }));
overlay.addEventListener('click', () => { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); });
const setCollapsed = (collapsed) => { sidebar.classList.toggle('w-66', !collapsed); sidebar.classList.toggle('w-19', collapsed); sidebar.querySelectorAll('.sidebar-label').forEach((label) => label.classList.toggle('hidden', collapsed)); localStorage.setItem('cite-pos-sidebar-collapsed', collapsed ? '1' : '0'); };
setCollapsed(localStorage.getItem('cite-pos-sidebar-collapsed') === '1');
toggle.addEventListener('click', () => setCollapsed(sidebar.classList.contains('w-66')));
document.querySelectorAll('form[data-confirm="delete"]').forEach((form) => form.addEventListener('submit', (event) => {
    event.preventDefault();
    pendingConfirmation = form;
    confirmationModal.querySelector('[data-confirmation-title]').textContent = 'Delete user?';
    confirmationModal.querySelector('[data-confirmation-message]').textContent = 'This action cannot be undone.';
    confirmationModal.classList.remove('hidden');
    confirmationModal.classList.add('flex');
}));
</script>
</body>
</html>
