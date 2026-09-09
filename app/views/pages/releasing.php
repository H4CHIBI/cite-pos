<?php
$baseFolder = $baseFolder ?? '/pos-cite';
$search = $search ?? '';
$batchId = $batchId ?? 0;
$pageStatus = $pageStatus ?? 'For Releasing';
$isReleased = $pageStatus === 'Released';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Releasing | CITE POS</title>
    <link rel="stylesheet" href="<?= $baseFolder ?>/assets/css/style.css">
</head>

<body class="min-h-screen bg-[#f5f7fc] font-sans text-[#17213a]">
    <div class="flex min-h-screen"><?php require __DIR__ . '/../partials/sidebar.php'; ?>
        <div class="flex min-w-0 flex-1 flex-col"><?php require __DIR__ . '/../partials/header.php'; ?>
            <main class="flex-1 px-4 py-7 sm:px-8">
                <div class="mx-auto max-w-350">
                    <div class="mb-7">
                        <p class="mb-1 text-sm font-semibold text-[#f7941d]">Inventory fulfillment</p>
                        <h2 class="text-3xl font-bold text-[#071947]"><?= $isReleased ? 'Released' : 'Releasing' ?></h2>
                        <p class="mt-2 text-sm text-slate-500">
                            <?= $isReleased ? 'Items already released to their owners.' : 'Items waiting to be released to their owners.' ?>
                        </p>
                    </div>
                    <?php if ($message): ?>
                        <div
                            class="mb-6 rounded-xl border px-4 py-3 text-sm <?= $message['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' ?>">
                            <?= htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <form
                        class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row"
                        method="get"><input class="min-h-11 flex-1 rounded-xl border border-slate-200 px-3"
                            name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Search owner, department, or product"><select
                            class="min-h-11 rounded-xl border border-slate-200 px-3" name="batch_id">
                            <option value="">Active batch</option><?php foreach ($batches as $batch): ?>
                                <option value="<?= (int) $batch['batch_id'] ?>" <?= $batchId === (int) $batch['batch_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($batch['batch_name'], ENT_QUOTES, 'UTF-8') ?>    <?= $batch['is_active'] ? ' (Active)' : '' ?>
                                </option><?php endforeach; ?>
                        </select><button class="min-h-11 rounded-xl bg-[#123fa5] px-6 text-sm font-bold text-white"
                            type="submit">Filter</button><button
                            class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-xl bg-orange-50 px-5 text-sm font-bold text-orange-700"
                            type="button" data-release-print>Print / PDF</button></form>
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h3 class="font-bold text-[#071947]"><?= $isReleased ? 'Released items' : 'For releasing' ?>
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[1100px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="px-6 py-3">Name</th>
                                        <th class="px-6 py-3">Department</th>
                                        <th class="px-6 py-3">Year</th>
                                        <th class="px-6 py-3">Section</th>
                                        <th class="px-6 py-3">Product</th>
                                        <th class="px-6 py-3">Qty</th>
                                        <th class="px-6 py-3">Batch</th>
                                        <th class="px-6 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100"><?php if (!$items): ?>
                                        <tr>
                                            <td class="px-6 py-10 text-center text-slate-400" colspan="8">No items found.
                                            </td>
                                        </tr>
                                    <?php endif; ?><?php foreach ($items as $item): ?>    <?php $studentName = trim($item['first_name'] . ' ' . $item['last_name']);
                                               $academic = preg_split('/\s*[-|,]\s*/', (string) ($item['grade_section'] ?? ''), 2); ?>
                                        <tr>
                                            <td class="px-6 py-4 font-semibold">
                                                <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="px-6 py-4">
                                                <?= htmlspecialchars($item['department_code'] . ' - ' . $item['department_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?= htmlspecialchars($academic[0] ?: 'Not set', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="px-6 py-4">
                                                <?= htmlspecialchars($academic[1] ?? 'Not set', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="px-6 py-4">
                                                <?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="px-6 py-4"><?= (int) $item['quantity'] ?></td>
                                            <td class="px-6 py-4">
                                                <?= htmlspecialchars($item['batch_name'] ?: 'Standard', ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <form class="release-status-form" method="post"
                                                    data-student-name="<?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="action" value="update_release_status"><input
                                                        type="hidden" name="item_id"
                                                        value="<?= (int) $item['item_id'] ?>"><button
                                                        class="cursor-pointer rounded-lg bg-<?= $isReleased ? 'orange' : 'emerald' ?>-50 px-3 py-2 text-xs font-bold text-<?= $isReleased ? 'orange' : 'emerald' ?>-700"
                                                        type="submit"><?= $isReleased ? 'Undo release' : 'Mark released' ?></button>
                                                </form>
                                            </td>
                                        </tr><?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <?php if ($pages > 1): ?>
                        <nav class="mt-5 flex flex-wrap justify-center gap-2">
                            <?php for ($number = 1; $number <= $pages; $number++): ?><a
                                    class="rounded-lg px-3 py-2 text-sm font-bold <?= $number === $page ? 'bg-[#123fa5] text-white' : 'bg-white text-[#123fa5] ring-1 ring-slate-200' ?>"
                                    href="?search=<?= urlencode($search) ?>&batch_id=<?= (int) $batchId ?>&page=<?= $number ?>"><?= $number ?></a><?php endfor; ?>
                        </nav><?php endif; ?>
                </div>
            </main><?php require __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>
    <div class="fixed inset-0 z-50 hidden items-center justify-center bg-[#071947]/60 p-4" data-release-print-modal>
        <div class="flex h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-bold text-[#071947]">Print preview</h2><button
                    class="cursor-pointer rounded-lg px-3 py-2 text-sm font-bold text-slate-500 hover:bg-slate-100"
                    type="button" data-release-print-close>Close</button>
            </div>
            <iframe class="min-h-0 flex-1" title="Releasing print preview" data-release-print-frame></iframe>
            <div class="flex justify-end border-t border-slate-200 px-5 py-4"><button
                    class="cursor-pointer rounded-xl bg-[#123fa5] px-5 py-3 text-sm font-bold text-white" type="button"
                    data-release-print-submit>Print / Save as PDF</button></div>
        </div>
    </div>
    <script>
        const sidebar = document.querySelector('[data-sidebar]'), overlay = document.querySelector('[data-menu-overlay]'), toggle = document.querySelector('[data-sidebar-toggle]');
        document.querySelectorAll('[data-mobile-menu]').forEach((button) => button.addEventListener('click', () => { sidebar.classList.toggle('-translate-x-full'); overlay.classList.toggle('hidden'); }));
        overlay.addEventListener('click', () => { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); });
        const setCollapsed = (collapsed) => { sidebar.classList.toggle('w-66', !collapsed); sidebar.classList.toggle('w-19', collapsed); sidebar.querySelectorAll('.sidebar-label').forEach((label) => label.classList.toggle('hidden', collapsed)); localStorage.setItem('cite-pos-sidebar-collapsed', collapsed ? '1' : '0'); };
        setCollapsed(localStorage.getItem('cite-pos-sidebar-collapsed') === '1');
        toggle.addEventListener('click', () => setCollapsed(sidebar.classList.contains('w-66')));
        document.querySelectorAll('.release-status-form').forEach((form) => form.addEventListener('submit', (event) => {
            event.preventDefault();
            pendingConfirmation = form;
            confirmationModal.querySelector('[data-confirmation-title]').textContent = 'Confirm status update';
            confirmationModal.querySelector('[data-confirmation-message]').textContent = 'Update the release status for ' + form.dataset.studentName + '?';
            confirmationModal.classList.remove('hidden');
            confirmationModal.classList.add('flex');
        }));
        const releasePrintModal = document.querySelector('[data-release-print-modal]');
        const releasePrintFrame = document.querySelector('[data-release-print-frame]');
        document.querySelector('[data-release-print]').addEventListener('click', () => {
            const params = new URLSearchParams({ status: '<?= htmlspecialchars($pageStatus, ENT_QUOTES, 'UTF-8') ?>', search: '<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>', batch_id: '<?= (int) $batchId ?>' });
            releasePrintFrame.src = '<?= $baseFolder ?>/releasing/print?' + params.toString();
            releasePrintModal.classList.remove('hidden');
            releasePrintModal.classList.add('flex');
        });
        const closeReleasePrint = () => { releasePrintModal.classList.add('hidden'); releasePrintModal.classList.remove('flex'); releasePrintFrame.src = 'about:blank'; };
        document.querySelector('[data-release-print-close]').addEventListener('click', closeReleasePrint);
        document.querySelector('[data-release-print-submit]').addEventListener('click', () => { if (releasePrintFrame.contentWindow) releasePrintFrame.contentWindow.print(); });
    </script>
</body>

</html>