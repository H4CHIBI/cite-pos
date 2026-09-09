<?php
$baseFolder = $baseFolder ?? '/pos-cite';
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

            <main class="flex-1 px-4 py-7 sm:px-8">
                <div class="mx-auto max-w-350">
                    <div class="mb-7">
                        <p class="mb-1 text-sm font-semibold text-[#f7941d]">Administration</p>
                        <h2 class="text-3xl font-bold text-[#071947]">Audit Logs</h2>
                        <p class="mt-2 text-sm text-slate-500">Review system activity and changes.</p>
                    </div>

                    <form class="mb-6 flex gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" method="get">
                        <input
                            class="min-h-11 flex-1 rounded-xl border border-slate-200 px-3"
                            name="search"
                            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Search action, module, or user"
                        >
                        <button class="min-h-11 rounded-xl bg-[#123fa5] px-6 text-sm font-bold text-white" type="submit">
                            Search
                        </button>
                    </form>

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-225 text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3">User</th>
                                        <th class="px-6 py-3">Action</th>
                                        <th class="px-6 py-3">Module</th>
                                        <th class="px-6 py-3">Record</th>
                                        <th class="px-6 py-3">Details</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (!$logs): ?>
                                        <tr>
                                            <td class="px-6 py-10 text-center text-slate-400" colspan="6">
                                                No audit logs found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php foreach ($logs as $log): ?>
                                        <?php
                                        $rawDetails = $log['new_value'] ?: $log['old_value'];
                                        $details = json_decode((string) $rawDetails, true);
                                        ?>
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-xs">
                                                <?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?= htmlspecialchars($log['full_name'] ?: ($log['username'] ?: 'System'), ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-6 py-4 font-semibold">
                                                <?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?= htmlspecialchars($log['module'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?= $log['record_id'] ? (int) $log['record_id'] : '-' ?>
                                            </td>
                                            <td class="max-w-96 px-6 py-4 text-xs text-slate-600">
                                                <?php if (is_array($details)): ?>
                                                    <div class="space-y-1">
                                                        <?php foreach ($details as $key => $value): ?>
                                                            <?php $label = ucwords(str_replace('_', ' ', $key)); ?>
                                                            <?php if ($key === 'items' && is_array($value)): ?>
                                                                <p>
                                                                    <span class="font-semibold">Items:</span>
                                                                    <?= count($value) ?> line(s)
                                                                </p>
                                                            <?php elseif (is_array($value)): ?>
                                                                <p>
                                                                    <span class="font-semibold">
                                                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>:
                                                                    </span>
                                                                    <?= htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8') ?>
                                                                </p>
                                                            <?php else: ?>
                                                                <p>
                                                                    <span class="font-semibold">
                                                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>:
                                                                    </span>
                                                                    <?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>
                                                                </p>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <?= htmlspecialchars((string) ($rawDetails ?: '-'), ENT_QUOTES, 'UTF-8') ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div class="mt-5 flex flex-col items-center justify-between gap-3 sm:flex-row">
                        <p class="text-xs text-slate-500">
                            Page <?= (int) $page ?> of <?= (int) $pages ?>
                        </p>

                        <?php if ($pages > 1): ?>
                            <nav class="flex flex-wrap justify-center gap-2" aria-label="Audit log pages">
                                <?php if ($page > 1): ?>
                                    <a
                                        class="rounded-lg bg-white px-3 py-2 text-sm font-bold text-[#123fa5] ring-1 ring-slate-200"
                                        href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>"
                                    >
                                        Previous
                                    </a>
                                <?php endif; ?>

                                <?php for ($number = 1; $number <= $pages; $number++): ?>
                                    <a
                                        class="rounded-lg px-3 py-2 text-sm font-bold <?= $number === $page ? 'bg-[#123fa5] text-white' : 'bg-white text-[#123fa5] ring-1 ring-slate-200' ?>"
                                        href="?search=<?= urlencode($search) ?>&page=<?= $number ?>"
                                        <?= $number === $page ? 'aria-current="page"' : '' ?>
                                    >
                                        <?= $number ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $pages): ?>
                                    <a
                                        class="rounded-lg bg-white px-3 py-2 text-sm font-bold text-[#123fa5] ring-1 ring-slate-200"
                                        href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>"
                                    >
                                        Next
                                    </a>
                                <?php endif; ?>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>

            <?php require __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>

    <script>
        const sidebar = document.querySelector('[data-sidebar]');
        const overlay = document.querySelector('[data-menu-overlay]');
        const toggle = document.querySelector('[data-sidebar-toggle]');

        document.querySelectorAll('[data-mobile-menu]').forEach((button) => {
            button.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        const setCollapsed = (collapsed) => {
            sidebar.classList.toggle('w-66', !collapsed);
            sidebar.classList.toggle('w-19', collapsed);
            sidebar.querySelectorAll('.sidebar-label').forEach((label) => {
                label.classList.toggle('hidden', collapsed);
            });
            localStorage.setItem('cite-pos-sidebar-collapsed', collapsed ? '1' : '0');
        };

        setCollapsed(localStorage.getItem('cite-pos-sidebar-collapsed') === '1');
        toggle.addEventListener('click', () => {
            setCollapsed(sidebar.classList.contains('w-66'));
        });
    </script>
</body>

</html>
