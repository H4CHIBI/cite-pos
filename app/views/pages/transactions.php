<?php
$pageTitle = 'Transactions';
$baseFolder = $baseFolder ?? '/pos-cite';
$search = $search ?? '';
$page = $page ?? 1;
$pages = $pages ?? 1;
$message = $message ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#071947">
    <title>Transactions | CITE POS</title>
    <link rel="icon" type="image/png" href="<?= $baseFolder ?>/assets/images/logo_it.png">
    <link rel="stylesheet" href="<?= $baseFolder ?>/assets/css/style.css">
</head>

<body class="min-h-screen bg-[#f5f7fc] font-sans text-[#17213a]">
    <div class="flex min-h-screen"><?php require __DIR__ . '/../partials/sidebar.php'; ?>
        <div class="flex min-w-0 flex-1 flex-col"><?php require __DIR__ . '/../partials/header.php'; ?>
            <main class="flex-1 px-4 py-7 sm:px-8">
                <div class="mx-auto max-w-350">
                    <div class="mb-7">
                        <p class="mb-1 text-sm font-semibold text-[#f7941d]">Sales history</p>
                        <h2 class="text-3xl font-bold tracking-tight text-[#071947]">Transactions</h2>
                        <p class="mt-2 text-sm text-slate-500">Search, print, and manage completed sales.</p>
                    </div>
                    <?php if ($message): ?>
                        <div class="mb-6 rounded-xl border px-4 py-3 text-sm <?= $message['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' ?>"
                            role="alert"><?= htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <form
                        class="mb-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row"
                        method="get"><label class="sr-only" for="transaction-search">Search transactions</label><input
                            id="transaction-search"
                            class="min-h-11 flex-1 rounded-xl border border-slate-200 px-3 outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100"
                            name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Search transaction number, ordering person, or cashier"><button
                            class="min-h-11 rounded-xl bg-[#123fa5] px-6 text-sm font-bold text-white"
                            type="submit">Search</button></form>
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h3 class="font-bold text-[#071947]">Completed transactions</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-6 py-3">Transaction</th>
                                        <th class="px-6 py-3">Ordering person</th>
                                        <th class="px-6 py-3">Cashier</th>
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3">Items</th>
                                        <th class="px-6 py-3">Total</th>
                                        <th class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (!$transactions): ?>
                                        <tr>
                                            <td class="px-6 py-10 text-center text-slate-400" colspan="7">No transactions
                                                found.</td>
                                        </tr><?php endif; ?>
                                    <?php foreach ($transactions as $transaction): ?>    <?php $voided = (int) $transaction['is_void'] === 1; ?>
                                        <tr class="<?= $voided ? 'text-red-600 line-through' : '' ?>">
                                            <td class="px-6 py-4 font-bold">
                                                #<?= (int) $transaction['transaction_id'] ?><?= $voided ? ' (VOID)' : '' ?>
                                            </td>
                                            <td class="px-6 py-4"><span
                                                    class="font-semibold"><?= htmlspecialchars($transaction['representative_name'], ENT_QUOTES, 'UTF-8') ?></span><br><span
                                                    class="text-xs"><?= htmlspecialchars($transaction['representative_position'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?= htmlspecialchars($transaction['cashier'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?= htmlspecialchars($transaction['transaction_date'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="px-6 py-4"><?= (int) $transaction['item_quantity'] ?></td>
                                            <td class="px-6 py-4 font-bold">
                                                ₱<?= number_format((float) $transaction['total_amount'], 2) ?></td>
                                            <td class="px-6 py-4 no-underline">
                                                <div class="flex flex-wrap gap-2"><button
                                                        class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-[#123fa5]"
                                                        type="button" data-transaction-print
                                                        data-id="<?= (int) $transaction['transaction_id'] ?>">Print</button><?php if (!$voided): ?><button
                                                            class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600"
                                                            type="button" data-transaction-delete
                                                            data-id="<?= (int) $transaction['transaction_id'] ?>">Void</button><?php endif; ?>
                                                </div>
                                            </td>
                                        </tr><?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <?php if ($pages > 1): ?>
                        <nav class="mt-5 flex items-center justify-center gap-2" aria-label="Transaction pages">
                            <?php for ($number = 1; $number <= $pages; $number++): ?><a
                                    class="rounded-lg px-3 py-2 text-sm font-bold <?= $number === $page ? 'bg-[#123fa5] text-white' : 'bg-white text-[#123fa5] ring-1 ring-slate-200' ?>"
                                    href="?search=<?= urlencode($search) ?>&page=<?= $number ?>"><?= $number ?></a><?php endfor; ?>
                        </nav><?php endif; ?>
                </div>
            </main><?php require __DIR__ . '/../partials/footer.php'; ?>
        </div>
    </div>
    <?php require __DIR__ . '/../modals/transaction-action.php'; ?>
    <?php require __DIR__ . '/../modals/transaction-print.php'; ?>
    <script>
        const transactionModal = document.querySelector('[data-transaction-modal]');
        const transactionForm = document.querySelector('[data-transaction-action-form]');
        const printModal = document.querySelector('[data-transaction-print-modal]');
        const printFrame = document.querySelector('[data-transaction-print-frame]');
        const closePrintModal = () => { printModal.classList.add('hidden'); printModal.classList.remove('flex'); printFrame.src = 'about:blank'; };
        document.querySelectorAll('[data-transaction-print]').forEach((button) => button.addEventListener('click', () => {
            printFrame.src = '<?= $baseFolder ?>/transactions/print?id=' + encodeURIComponent(button.dataset.id) + '&auto=0';
            printModal.classList.remove('hidden');
            printModal.classList.add('flex');
        }));
        document.querySelector('[data-transaction-print-close]').addEventListener('click', closePrintModal);
        document.querySelector('[data-transaction-print-cancel]').addEventListener('click', closePrintModal);
        document.querySelector('[data-transaction-print-submit]').addEventListener('click', () => {
            if (printFrame.contentWindow) printFrame.contentWindow.print();
        });
        document.querySelectorAll('[data-transaction-delete]').forEach((button) => button.addEventListener('click', () => {
            transactionForm.querySelector('[data-transaction-action]').value = 'void_transaction';
            transactionForm.querySelector('[data-transaction-id]').value = button.dataset.id;
            transactionModal.querySelector('[data-transaction-modal-title]').textContent = 'Void transaction';
            transactionModal.querySelector('[data-transaction-modal-message]').textContent = 'This marks the transaction as void and restores released inventory. Enter an admin password to continue.';
            transactionModal.classList.remove('hidden'); transactionModal.classList.add('flex');
        }));
        const closeTransactionModal = () => { transactionModal.classList.add('hidden'); transactionModal.classList.remove('flex'); transactionForm.reset(); };
        transactionModal.querySelector('[data-transaction-cancel]').addEventListener('click', closeTransactionModal);
    </script>
</body>

</html>