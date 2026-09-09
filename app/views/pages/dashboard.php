<?php
$pageTitle = 'Dashboard';
$baseFolder = $baseFolder ?? '/pos-cite';
$isAdmin = strtolower($_SESSION['role'] ?? '') === 'admin';
$isOfficer = strtolower($_SESSION['role'] ?? '') === 'officer';
$sales = (float) $salesStatement->fetchColumn();
$soldQuantity = (int) $quantityStatement->fetchColumn();
$totalStocks = (int) $stockStatement->fetchColumn();
$batches = $batchStatement->fetchAll();
$selectedBatch = (int) ($_GET['batch_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#071947">
    <title>Dashboard | CITE POS</title>
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
                    <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                        <div>
                            <p class="mb-1 text-sm font-semibold text-[#f7941d]"><?= $isAdmin ? 'Overview' : 'Your daily overview' ?></p>
                            <h2 class="text-2xl font-bold tracking-tight text-[#071947] sm:text-3xl">Good day, <?= htmlspecialchars($_SESSION['fullname'] ?? 'there', ENT_QUOTES, 'UTF-8') ?>.</h2>
                            <p class="mt-2 text-sm text-slate-500"><?= $isAdmin ? 'Track sales and inventory performance across the IT Department.' : 'Here is your sales activity for today.' ?></p>
                        </div>
                        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-xs font-bold text-[#123fa5]">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span> System online
                        </span>
                    </div>

                    <?php if ($isAdmin): ?>
                        <form class="mb-7 grid gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3 sm:items-end" method="get">
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">
                                From date
                                <input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-normal text-slate-700 outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" type="date" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>">
                            </label>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">
                                To date
                                <input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-normal text-slate-700 outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" type="date" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>">
                            </label>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500">
                                Batch
                                <select class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-normal text-slate-700 outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="batch_id">
                                    <option value="0">All batches</option>
                                    <?php foreach ($batches as $batch): ?>
                                        <option value="<?= (int) $batch['batch_id'] ?>" <?= $selectedBatch === (int) $batch['batch_id'] ? 'selected' : '' ?>><?= htmlspecialchars($batch['batch_name'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <button class="min-h-11 rounded-xl bg-[#123fa5] px-5 text-sm font-bold text-white transition hover:bg-[#09245f] sm:col-span-3 sm:w-fit" type="submit">Apply filters</button>
                        </form>
                    <?php endif; ?>

                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <?php if (!$isOfficer): ?><article class="relative overflow-hidden rounded-2xl bg-[#123fa5] p-6 text-white shadow-lg shadow-blue-900/10">
                            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full border border-white/10"></div>
                            <div class="relative">
                                <div class="mb-6 flex items-center justify-between"><p class="text-sm font-semibold text-white/75">Total sales</p><span class="rounded-xl bg-white/15 p-2"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2v20M18 2v20M2 6h15a3 3 0 0 1 0 6H6a3 3 0 0 0 0 6h16"></path></svg></span></div>
                                <p class="text-3xl font-bold">₱<?= number_format($sales, 2) ?></p>
                                <p class="mt-2 text-xs text-white/65"><?= $isAdmin ? 'Filtered sales total' : 'Your sales today' ?></p>
                            </div>
                        </article><?php endif; ?>
                        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="mb-6 flex items-center justify-between"><p class="text-sm font-semibold text-slate-500">Total stocks</p><span class="rounded-xl bg-orange-50 p-2 text-[#f7941d]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m4 7 8-4 8 4-8 4-8-4Zm0 5 8 4 8-4M4 17l8 4 8-4"></path></svg></span></div>
                            <p class="text-3xl font-bold text-[#071947]"><?= number_format($totalStocks) ?></p>
                            <p class="mt-2 text-xs text-slate-400">Available units in inventory</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:col-span-2 xl:col-span-1">
                            <div class="mb-6 flex items-center justify-between"><p class="text-sm font-semibold text-slate-500">Products sold</p><span class="rounded-xl bg-blue-50 p-2 text-[#123fa5]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5M4 19h16M8 15l3-4 3 2 4-6"></path></svg></span></div>
                            <p class="text-3xl font-bold text-[#071947]"><?= number_format($soldQuantity) ?></p>
                            <p class="mt-2 text-xs text-slate-400"><?= $isAdmin ? 'Units sold for selected filters' : 'Units sold today' ?></p>
                        </article>
                    </div>

                    <section class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col justify-between gap-2 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center">
                            <div><h3 class="font-bold text-[#071947]">Current batch</h3><p class="mt-1 text-xs text-slate-400">Active batches are used for current POS orders.</p></div>
                            <?php $activeBatch = array_values(array_filter($batches, static fn ($batch) => (int) $batch['is_active'] === 1))[0] ?? null; ?>
                            <?php if ($activeBatch): ?><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600">Active</span><?php endif; ?>
                        </div>
                        <div class="px-6 py-6">
                            <?php if ($activeBatch): ?>
                                <div class="flex items-center gap-4"><div class="rounded-xl bg-orange-50 p-3 text-[#f7941d]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 8h10M7 12h10M7 16h6"></path></svg></div><div><p class="text-lg font-bold text-[#071947]"><?= htmlspecialchars($activeBatch['batch_name'], ENT_QUOTES, 'UTF-8') ?></p><p class="text-sm text-slate-500">Delivery date: <?= $activeBatch['delivery_date'] ? date('F j, Y', strtotime($activeBatch['delivery_date'])) : 'Not set' ?></p></div></div>
                            <?php else: ?>
                                <p class="text-sm text-slate-500">No active batch has been set.</p>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </main>
            <?php require __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>
    <script>
        const sidebar = document.querySelector('[data-sidebar]');
        const overlay = document.querySelector('[data-menu-overlay]');
        const toggle = document.querySelector('[data-sidebar-toggle]');
        const mobileButtons = document.querySelectorAll('[data-mobile-menu]');
        const storageKey = 'cite-pos-sidebar-collapsed';

        const setMobileMenu = (open) => {
            sidebar.classList.toggle('-translate-x-full', !open);
            overlay.classList.toggle('hidden', !open);
        };
        mobileButtons.forEach((button) => button.addEventListener('click', () => setMobileMenu(sidebar.classList.contains('-translate-x-full'))));
        overlay.addEventListener('click', () => setMobileMenu(false));

        const setCollapsed = (collapsed) => {
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            sidebar.classList.toggle('w-66', !collapsed);
            sidebar.classList.toggle('w-19', collapsed);
            sidebar.querySelectorAll('.sidebar-label').forEach((label) => label.classList.toggle('hidden', collapsed));
            toggle.querySelector('[data-toggle-icon]').classList.toggle('rotate-180', collapsed);
            localStorage.setItem(storageKey, collapsed ? '1' : '0');
        };
        setCollapsed(localStorage.getItem(storageKey) === '1');
        toggle.addEventListener('click', () => setCollapsed(!document.body.classList.contains('sidebar-collapsed')));
    </script>
</body>
</html>
