<?php
$pageTitle = $pageTitle ?? ucfirst($page);
$baseFolder = $baseFolder ?? '/pos-cite';
$message = $message ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#071947">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | CITE POS</title>
    <link rel="icon" type="image/png" href="<?= $baseFolder ?>/assets/images/logo_it.png">
    <link rel="stylesheet" href="<?= $baseFolder ?>/assets/css/style.css">
</head>
<body class="min-h-screen bg-[#f5f7fc] font-sans text-[#17213a]">
    <div class="flex min-h-screen">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>
        <div class="flex min-w-0 flex-1 flex-col">
            <?php require __DIR__ . '/../partials/header.php'; ?>
            <main class="flex-1 px-4 py-7 sm:px-8">
                <div class="mx-auto max-w-350">
                    <div class="mb-7">
                        <p class="mb-1 text-sm font-semibold text-[#f7941d]">IT Department</p>
                        <h2 class="text-3xl font-bold tracking-tight text-[#071947]"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="mt-2 text-sm text-slate-500">Manage your point-of-sale data securely.</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="mb-6 rounded-xl border px-4 py-3 text-sm <?= $message['type'] === 'success' ? 'border-emerald-200 text-emerald-700' : 'border-red-200 text-red-700' ?>" style="background-color: <?= $message['type'] === 'success' ? '#ecfdf5' : '#fef2f2' ?>" role="alert"><?= htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <?php if ($page === 'products'): ?>
                        <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
                            <form class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="create_product">
                                <h3 class="mb-5 text-lg font-bold text-[#071947]">Add product</h3>
                                <label class="mb-4 block text-sm font-semibold text-slate-600">Product name<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="name" required></label>
                                <label class="mb-4 block text-sm font-semibold text-slate-600">Description<textarea class="mt-2 min-h-24 w-full rounded-xl border border-slate-200 px-3 py-2 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="description"></textarea></label>
                                <label class="mb-4 block text-sm font-semibold text-slate-600">Base price<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="base_price" type="number" min="0" step="0.01" required></label>
                                <label class="mb-4 block text-sm font-semibold text-slate-600">Product image<input class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 p-3 text-sm font-normal" name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
                                <label class="mb-3 flex items-center gap-3 text-sm font-semibold text-slate-600"><input class="h-4 w-4 accent-[#123fa5]" name="is_batch_tracked" type="checkbox"> Track stock by batch</label>
                                <label class="mb-6 flex items-center gap-3 text-sm font-semibold text-slate-600"><input class="h-4 w-4 accent-[#123fa5]" name="requires_releasing" type="checkbox"> Requires releasing</label>
                                <button class="min-h-11 w-full rounded-xl bg-[#123fa5] px-4 text-sm font-bold text-white transition hover:bg-[#09245f]" type="submit">Save product</button>
                            </form>
                            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-100 px-6 py-5"><h3 class="font-bold text-[#071947]">Product catalog</h3></div>
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-200 text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-6 py-3">Product</th><th class="px-6 py-3">Price</th><th class="px-6 py-3">Stock</th><th class="px-6 py-3">Tracking</th><th class="px-6 py-3">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
                                        <?php foreach ($products as $product): ?><tr><td class="px-6 py-4"><div class="flex items-center gap-3"><?php if ($product['image_path']): ?><img class="h-10 w-10 rounded-lg object-cover" src="<?= $baseFolder ?>/<?= htmlspecialchars($product['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 font-bold text-[#123fa5]"><?= htmlspecialchars(strtoupper(substr($product['name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><span class="font-semibold text-[#071947]"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></span></div></td><td class="px-6 py-4">₱<?= number_format((float) $product['base_price'], 2) ?></td><td class="px-6 py-4"><?= (int) $product['stocks'] ?></td><td class="px-6 py-4"><?= $product['is_batch_tracked'] ? '<span class="rounded-full bg-orange-50 px-2 py-1 text-xs font-bold text-orange-600">Batch</span>' : '<span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-500">Standard</span>' ?></td><td class="px-6 py-4"><details><summary class="cursor-pointer font-bold text-[#123fa5]">Edit</summary><form class="mt-3 w-64 space-y-2" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="edit_product"><input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>"><input class="w-full rounded-lg border p-2" name="name" value="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>" required><input class="w-full rounded-lg border p-2" name="base_price" type="number" min="0" step=".01" value="<?= htmlspecialchars($product['base_price'], ENT_QUOTES, 'UTF-8') ?>" required><input class="w-full rounded-lg border p-2" name="image" type="file" accept="image/jpeg,image/png,image/webp"><label class="block text-xs"><input name="is_batch_tracked" type="checkbox" <?= $product['is_batch_tracked'] ? 'checked' : '' ?>> Batch tracked</label><button class="rounded-lg bg-[#123fa5] px-3 py-2 text-xs font-bold text-white" type="submit">Save</button></form></details><form class="mt-2" method="post" onsubmit="return confirm('Delete this product?')"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>"><button class="text-xs font-bold text-red-600" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
                                    </tbody></table>
                                </div>
                            </section>
                        </div>
                    <?php elseif ($page === 'batches'): ?>
                        <div class="grid gap-6 xl:grid-cols-2">
                            <form class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" method="post">
                                <input type="hidden" name="action" value="create_batch"><h3 class="mb-5 text-lg font-bold text-[#071947]">Create batch</h3>
                                <label class="mb-4 block text-sm font-semibold text-slate-600">Batch name<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="batch_name" placeholder="e.g. Batch 1" required></label>
                                <label class="mb-4 block text-sm font-semibold text-slate-600">Delivery date<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="delivery_date" type="date"></label>
                                <label class="mb-6 flex items-center gap-3 text-sm font-semibold text-slate-600"><input class="h-4 w-4 accent-[#123fa5]" name="is_active" type="checkbox"> Set as current active batch</label>
                                <button class="min-h-11 w-full rounded-xl bg-[#123fa5] px-4 text-sm font-bold text-white hover:bg-[#09245f]" type="submit">Create batch</button>
                            </form>
                        </div>
                        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-6 py-5"><h3 class="font-bold text-[#071947]">Batches</h3></div><div class="overflow-x-auto"><table class="w-full min-w-[850px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-6 py-3">Batch</th><th class="px-6 py-3">Delivery date</th><th class="px-6 py-3">Status</th><th class="px-6 py-3">Actions</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach ($batches as $batch): ?><tr><td class="px-6 py-4 font-semibold"><?= htmlspecialchars($batch['batch_name'], ENT_QUOTES, 'UTF-8') ?></td><td class="px-6 py-4"><?= $batch['delivery_date'] ? htmlspecialchars($batch['delivery_date'], ENT_QUOTES, 'UTF-8') : 'Not set' ?></td><td class="px-6 py-4"><?= $batch['is_active'] ? '<span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-600">Active</span>' : '<span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-500">Inactive</span>' ?></td><td class="px-6 py-4"><details><summary class="cursor-pointer font-bold text-[#123fa5]">Edit</summary><form class="mt-3 flex flex-wrap items-center gap-2" method="post"><input type="hidden" name="action" value="edit_batch"><input type="hidden" name="batch_id" value="<?= (int) $batch['batch_id'] ?>"><input class="rounded-lg border p-2" name="batch_name" value="<?= htmlspecialchars($batch['batch_name'], ENT_QUOTES, 'UTF-8') ?>" required><input class="rounded-lg border p-2" name="delivery_date" type="date" value="<?= htmlspecialchars($batch['delivery_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><label class="text-xs"><input name="is_active" type="checkbox" <?= $batch['is_active'] ? 'checked' : '' ?>> Active</label><button class="rounded-lg bg-[#123fa5] px-3 py-2 text-xs font-bold text-white" type="submit">Save</button></form></details><form class="mt-2" method="post" onsubmit="return confirm('Delete this batch?')"><input type="hidden" name="action" value="delete_batch"><input type="hidden" name="batch_id" value="<?= (int) $batch['batch_id'] ?>"><button class="text-xs font-bold text-red-600" type="submit">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
                    <?php elseif ($page === 'departments'): ?>
                        <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
                            <form class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" method="post">
                                <input type="hidden" name="action" value="create_department">
                                <h3 class="mb-5 text-lg font-bold text-[#071947]">Add department</h3>
                                <label class="mb-4 block text-sm font-semibold text-slate-600">Department code<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal" name="department_code" placeholder="e.g. CBA" required></label>
                                <label class="mb-6 block text-sm font-semibold text-slate-600">Department name<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal" name="department_name" required></label>
                                <button class="min-h-11 w-full rounded-xl bg-[#123fa5] px-4 text-sm font-bold text-white" type="submit">Add department</button>
                            </form>
                            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-100 px-6 py-5"><h3 class="font-bold text-[#071947]">Departments</h3></div>
                                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-6 py-3">Code</th><th class="px-6 py-3">Department</th><th class="px-6 py-3">Actions</th></tr></thead><tbody class="divide-y divide-slate-100">
                                <?php foreach ($departments as $department): ?><tr><td class="px-6 py-4 font-bold"><?= htmlspecialchars($department['department_code'], ENT_QUOTES, 'UTF-8') ?></td><td class="px-6 py-4"><?= htmlspecialchars($department['department_name'], ENT_QUOTES, 'UTF-8') ?></td><td class="px-6 py-4"><details><summary class="cursor-pointer text-xs font-bold text-[#123fa5]">Edit</summary><form class="mt-3 flex flex-wrap gap-2" method="post"><input type="hidden" name="action" value="edit_department"><input type="hidden" name="department_id" value="<?= (int) $department['department_id'] ?>"><input class="w-24 rounded-lg border p-2 text-sm" name="department_code" value="<?= htmlspecialchars($department['department_code'], ENT_QUOTES, 'UTF-8') ?>" required><input class="rounded-lg border p-2 text-sm" name="department_name" value="<?= htmlspecialchars($department['department_name'], ENT_QUOTES, 'UTF-8') ?>" required><button class="rounded-lg bg-[#123fa5] px-3 py-2 text-xs font-bold text-white" type="submit">Save</button></form></details><form class="mt-2" method="post" onsubmit="return confirm('Delete this department?')"><input type="hidden" name="action" value="delete_department"><input type="hidden" name="department_id" value="<?= (int) $department['department_id'] ?>"><button class="text-xs font-bold text-red-600" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
                                </tbody></table></div>
                            </section>
                        </div>
                    <?php else: ?>
                        <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
                            <form class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" method="post"><input type="hidden" name="action" value="assign_product"><h3 class="mb-5 text-lg font-bold text-[#071947]">Assign tracked product</h3><label class="mb-4 block text-sm font-semibold text-slate-600">Batch<select class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="batch_id" required><option value="">Select batch</option><?php foreach ($batches as $batch): ?><option value="<?= (int) $batch['batch_id'] ?>"><?= htmlspecialchars($batch['batch_name'], ENT_QUOTES, 'UTF-8') ?><?= $batch['is_active'] ? ' (Active)' : '' ?></option><?php endforeach; ?></select></label><label class="mb-4 block text-sm font-semibold text-slate-600">Product<select class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="product_id" required><option value="">Select tracked product</option><?php foreach ($trackedProducts as $product): ?><option value="<?= (int) $product['product_id'] ?>"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label><label class="mb-6 block text-sm font-semibold text-slate-600">Quantity<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="quantity" type="number" min="0" placeholder="Optional"></label><button class="min-h-11 w-full rounded-xl bg-[#f7941d] px-4 text-sm font-bold text-white hover:bg-[#d96d00]" type="submit">Assign product</button></form>
                            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="border-b border-slate-100 px-6 py-5"><h3 class="font-bold text-[#071947]">Batch assignments</h3></div><div class="overflow-x-auto"><table class="w-full min-w-[850px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-6 py-3">Batch</th><th class="px-6 py-3">Product</th><th class="px-6 py-3">Initial quantity</th><th class="px-6 py-3">Current quantity</th><th class="px-6 py-3">Actions</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach ($assignments as $assignment): ?><tr><td class="px-6 py-4 font-semibold"><?= htmlspecialchars($assignment['batch_name'], ENT_QUOTES, 'UTF-8') ?></td><td class="px-6 py-4"><?= htmlspecialchars($assignment['name'], ENT_QUOTES, 'UTF-8') ?></td><td class="px-6 py-4"><?= (int) $assignment['initial_quantity'] ?></td><td class="px-6 py-4"><form class="flex items-center gap-2" method="post"><input type="hidden" name="action" value="edit_assignment"><input type="hidden" name="product_batch_id" value="<?= (int) $assignment['product_batch_id'] ?>"><input class="w-20 rounded-lg border p-2" name="current_quantity" type="number" min="0" value="<?= (int) $assignment['current_quantity'] ?>"><button class="text-xs font-bold text-[#123fa5]" type="submit">Save</button></form></td><td class="px-6 py-4"><form method="post" onsubmit="return confirm('Delete this batch assignment?')"><input type="hidden" name="action" value="delete_assignment"><input type="hidden" name="product_batch_id" value="<?= (int) $assignment['product_batch_id'] ?>"><button class="text-xs font-bold text-red-600" type="submit">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></section>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
            <?php require __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>
    <script>
        const assignmentProducts = <?= json_encode($assignmentProducts ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const batchSelect = document.querySelector('select[name="batch_id"]');
        const productSelect = document.querySelector('select[name="product_id"]');
        if (batchSelect && productSelect) {
            const assignedByBatch = assignmentProducts.reduce((assigned, item) => {
                const batchId = String(item.batch_id);
                if (!assigned[batchId]) assigned[batchId] = new Set();
                assigned[batchId].add(String(item.product_id));
                return assigned;
            }, {});
            const updateProductOptions = () => {
                const assigned = assignedByBatch[batchSelect.value] || new Set();
                Array.from(productSelect.options).forEach((option) => {
                    if (!option.value) return;
                    const unavailable = assigned.has(option.value);
                    option.hidden = unavailable;
                    option.disabled = unavailable;
                });
                if (productSelect.selectedOptions[0] && productSelect.selectedOptions[0].disabled) productSelect.value = '';
            };
            batchSelect.addEventListener('change', updateProductOptions);
            updateProductOptions();
        }
        const sidebar = document.querySelector('[data-sidebar]'), overlay = document.querySelector('[data-menu-overlay]'), toggle = document.querySelector('[data-sidebar-toggle]');
        document.querySelectorAll('[data-mobile-menu]').forEach((button) => button.addEventListener('click', () => { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }));
        overlay.addEventListener('click', () => { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); });
        const setCollapsed = (collapsed) => { sidebar.classList.toggle('w-66', !collapsed); sidebar.classList.toggle('w-19', collapsed); sidebar.querySelectorAll('.sidebar-label').forEach((label) => label.classList.toggle('hidden', collapsed)); localStorage.setItem('cite-pos-sidebar-collapsed', collapsed ? '1' : '0'); };
        setCollapsed(localStorage.getItem('cite-pos-sidebar-collapsed') === '1');
        toggle.addEventListener('click', () => setCollapsed(sidebar.classList.contains('w-66')));
        document.querySelectorAll('form[onsubmit*="confirm"]').forEach((form) => { form.removeAttribute('onsubmit'); form.dataset.confirm = 'delete'; });
        document.querySelectorAll('form[data-confirm="delete"]').forEach((form) => form.addEventListener('submit', (event) => {
            event.preventDefault();
            pendingConfirmation = form;
            confirmationModal.querySelector('[data-confirmation-title]').textContent = 'Delete item?';
            confirmationModal.querySelector('[data-confirmation-message]').textContent = 'This action cannot be undone.';
            confirmationModal.classList.remove('hidden');
            confirmationModal.classList.add('flex');
        }));
        document.querySelector('[data-confirmation-cancel]').addEventListener('click', () => { pendingConfirmation = null; confirmationModal.classList.add('hidden'); confirmationModal.classList.remove('flex'); });
        document.querySelector('[data-confirmation-accept]').addEventListener('click', () => { if (pendingConfirmation) pendingConfirmation.submit(); });
    </script>
</body>
</html>
