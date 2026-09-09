<?php if ($receipt): ?>
    <section class="receipt-print" data-receipt>
        <?php for ($copy = 1; $copy <= 2; $copy++): ?>
            <div class="receipt-copy">
                <div class="text-center">
                    <div class="receipt-logos">
                        <img src="<?= $baseFolder ?>/assets/images/logo_golden.png" alt="Goldenstate College" class="receipt-logo">
                        <img src="<?= $baseFolder ?>/assets/images/logo_it.png" alt="College of Information Technology Education" class="receipt-logo">
                    </div>
                    <h1>Goldenstate College</h1>
                    <p>College of Information Technology Education</p>
                    <p>CITE POS · Sales Receipt</p>
                    <p>Copy <?= $copy ?> of 2</p>
                </div>
                <hr>
                <p>
                    <strong>Transaction #:</strong> <?= (int) $receipt['id'] ?><br>
                    <strong>Date:</strong> <?= htmlspecialchars($receipt['date'], ENT_QUOTES, 'UTF-8') ?><br>
                    <strong>Ordering person:</strong> <?= htmlspecialchars($receipt['representative'], ENT_QUOTES, 'UTF-8') ?><br>
                    <strong>Position:</strong> <?= htmlspecialchars($receipt['position'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <table>
                    <thead><tr><th>Product / Owner</th><th>Qty</th><th>Amount</th></tr></thead>
                    <tbody>
                        <?php foreach ($receipt['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?><br><small>Owner: <?= htmlspecialchars($item['owner'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                <td><?= (int) $item['quantity'] ?></td>
                                <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h2>Total: ₱<?= number_format($receipt['total'], 2) ?></h2>
                <p>Amount paid: ₱<?= number_format($receipt['paid'], 2) ?><br>Change: ₱<?= number_format($receipt['change'], 2) ?></p>
                <div class="signatures">
                    <span><strong><?= htmlspecialchars($receipt['representative'], ENT_QUOTES, 'UTF-8') ?></strong><br>Ordering person signature</span>
                    <span><strong><?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username'] ?? 'Cashier', ENT_QUOTES, 'UTF-8') ?></strong><br>Cashier signature</span>
                </div>
            </div>
        <?php endfor; ?>
    </section>
    <?php require __DIR__ . '/../modals/receipt-complete.php'; ?>
<?php endif; ?>

<style>
@page { size: 8.5in 13in; margin: 0 }
@media print {
    body > *:not(.receipt-print) { display: none !important }
    .receipt-print { display: block !important }
    .receipt-copy { height: 13in; box-sizing: border-box; padding: .55in; border-bottom: 1px dashed #888; font: 12pt Arial; color: #111; page-break-after: always }
    .receipt-copy:last-child { border-bottom: 0; page-break-after: auto }
    .receipt-logos { display: flex; justify-content: space-between; align-items: center; width: 100% }
    .receipt-logo { width: 75px; height: 75px; object-fit: contain }
    .receipt-copy h1 { margin: 4px 0 }
    .receipt-copy p { line-height: 1.5 }
    .receipt-copy table { width: 100%; border-collapse: collapse; margin: 18px 0 }
    .receipt-copy th, .receipt-copy td { border-bottom: 1px solid #bbb; padding: 7px; text-align: left }
    .receipt-copy th:last-child, .receipt-copy td:last-child { text-align: right }
    .signatures { display: flex; gap: 1in; margin-top: 1in }
    .signatures span { border-top: 1px solid #111; padding-top: 7px; flex: 1; text-align: center; font-size: 10pt }
    .signatures strong { display: block; min-height: 18px; margin-bottom: 3px }
}
@media screen { .receipt-print { display: none } }
</style>
