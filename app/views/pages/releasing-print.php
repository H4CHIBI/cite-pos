<?php $baseFolder = $baseFolder ?? '/pos-cite'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Releasing List</title>
    <style>
        @page {
            size: landscape;
            margin: .45in
        }

        body {
            font: 11pt Arial;
            color: #111;
            margin: 0
        }

        h1 {
            text-align: center;
            margin: 0 0 4px
        }

        p {
            text-align: center;
            margin: 3px 0 18px;
            color: #444
        }

        .print-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 28px;
            margin-bottom: 8px
        }

        .print-logo {
            width: 72px;
            height: 72px;
            object-fit: contain
        }

        .print-title {
            text-align: center
        }

        .print-title h1 {
            margin: 0 0 4px
        }

        .print-title p {
            margin: 3px 0;
            color: #444
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left
        }

        th {
            background: #eee
        }

        .signature {
            width: 1.6in;
            height: .3in
        }

        @media screen {
            body {
                padding: 24px
            }

            .print-button {
                margin-bottom: 20px;
                padding: 10px 16px
            }
        }

        @media print {
            .print-button {
                display: none
            }
        }
    </style>
</head>

<body><button class="print-button" onclick="window.print()">Print / Save as PDF</button>
    <header class="print-header"><img class="print-logo" src="<?= $baseFolder ?>/assets/images/logo_golden.png"
            alt="Goldenstate College">
        <div class="print-title">
            <h1>Items for Releasing</h1>
            <p>Goldenstate College · CITE POS</p>
        </div><img class="print-logo" src="<?= $baseFolder ?>/assets/images/logo_it.png" alt="IT Department">
    </header>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Year</th>
                <th>Section</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Signature</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>    <?php $academic = preg_split('/\s*[-|,]\s*/', (string) ($item['grade_section'] ?? ''), 2); ?>
                <tr>
                    <td><?= htmlspecialchars(trim($item['first_name'] . ' ' . $item['last_name']), ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td><?= htmlspecialchars($item['department_code'] . ' - ' . $item['department_name'], ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td><?= htmlspecialchars($academic[0] ?: 'Not set', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($academic[1] ?? 'Not set', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td class="signature"></td>
                </tr><?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>