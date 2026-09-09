<?php $baseFolder = $baseFolder ?? '/pos-cite'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction #<?= (int) $transaction['transaction_id'] ?> | CITE POS</title>
    <link rel="stylesheet" href="<?= $baseFolder ?>/assets/css/style.css">
</head>
<body>
    <section class="receipt-print">
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
                    <strong>Transaction #:</strong> <?= (int) $transaction['transaction_id'] ?><br>
                    <strong>Date:</strong> <?= htmlspecialchars($transaction['transaction_date'], ENT_QUOTES, 'UTF-8') ?><br>
                    <strong>Ordering person:</strong> <?= htmlspecialchars($transaction['representative_name'], ENT_QUOTES, 'UTF-8') ?><br>
                    <strong>Position:</strong> <?= htmlspecialchars($transaction['representative_position'], ENT_QUOTES, 'UTF-8') ?>
                </p>
                <table>
                    <thead><tr><th>Product / Owner</th><th>Qty</th><th>Amount</th></tr></thead>
                    <tbody>
                        <?php foreach ($transaction['items'] as $item): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?><br>
                                    <small>Owner: <?= htmlspecialchars(trim($item['first_name'] . ' ' . $item['last_name']), ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                                <td><?= (int) $item['quantity'] ?></td>
                                <td>₱<?= number_format((float) $item['price_at_purchase'] * (int) $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <h2>Total: ₱<?= number_format((float) $transaction['total_amount'], 2) ?></h2>
                <p>
                    Amount paid: ₱<?= number_format((float) $transaction['amount_paid'], 2) ?><br>
                    Change: ₱<?= number_format((float) $transaction['change_amount'], 2) ?>
                </p>
                <div class="signatures">
                    <span><strong><?= htmlspecialchars($transaction['representative_name'], ENT_QUOTES, 'UTF-8') ?></strong><br>Ordering person signature</span>
                    <span><strong><?= htmlspecialchars($transaction['cashier'], ENT_QUOTES, 'UTF-8') ?></strong><br>Cashier signature</span>
                </div>
            </div>
        <?php endfor; ?>
    </section>
    <?php if (($_GET['auto'] ?? '1') !== '0'): ?><script>window.addEventListener('load', () => window.print());</script><?php endif; ?>
    <style>
        @page { size: 8.5in 13in; margin: 0 }
        body { margin: 0; background: #fff; color: #111 }
        .receipt-copy { height: 13in; box-sizing: border-box; padding: .55in; border-bottom: 1px dashed #888; font: 12pt Arial; page-break-after: always }
        .receipt-copy:last-child { border-bottom: 0; page-break-after: auto }
        .receipt-copy > div:first-child { text-align: center }
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
        @media screen {
            body { padding: 1rem }
            .receipt-copy { height: auto; min-height: 10in; margin-bottom: 1rem; border: 1px dashed #888 }
        }
    </style>
</body>
</html>
