<?php
$baseFolder = $baseFolder ?? '/pos-cite';
$message = $message ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale | CITE POS</title>
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
    <div class="mb-7 flex items-end justify-between"><div><p class="mb-1 text-sm font-semibold text-[#f7941d]">Cashier workspace</p><h2 class="text-3xl font-bold tracking-tight text-[#071947]">Point of Sale</h2><p class="mt-2 text-sm text-slate-500">Click a product to add it to the customer order.</p></div><span class="rounded-full bg-blue-50 px-4 py-2 text-xs font-bold text-[#123fa5]">Active batch: <?= htmlspecialchars($activeBatch['batch_name'] ?? 'None', ENT_QUOTES, 'UTF-8') ?></span></div>
    <?php if ($message): ?><div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"><?= htmlspecialchars($message['text'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <section><div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($products as $product): ?>
                <?php $available = $product['is_batch_tracked'] ? 'Batch stock' : (int) $product['stocks'] . ' in stock'; ?>
                <?php $productData = ['id' => (int) $product['product_id'], 'name' => $product['name'], 'price' => (float) $product['base_price'], 'batch' => (bool) $product['is_batch_tracked'], 'stock' => (int) $product['stocks']]; ?>
                <button class="group overflow-hidden rounded-2xl border border-slate-200 bg-white text-left shadow-sm transition hover:-translate-y-1 hover:border-[#123fa5] hover:shadow-lg" type="button" data-product='<?= htmlspecialchars(json_encode($productData), ENT_QUOTES, 'UTF-8') ?>'>
                    <?php if ($product['image_path']): ?><img class="h-36 w-full object-cover" src="<?= $baseFolder ?>/<?= htmlspecialchars($product['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt=""><?php else: ?><div class="flex h-36 items-center justify-center bg-blue-50 text-4xl font-bold text-[#123fa5]"><?= htmlspecialchars(strtoupper(substr($product['name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <div class="p-4"><h3 class="font-bold text-[#071947]"><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3><div class="mt-2 flex items-center justify-between"><span class="font-bold text-[#f7941d]">₱<?= number_format((float) $product['base_price'], 2) ?></span><span class="text-xs text-slate-400"><?= $available ?></span></div></div>
                </button>
            <?php endforeach; ?>
        </div></section>
        <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="mb-4 flex items-center justify-between"><h3 class="text-lg font-bold text-[#071947]">Current order</h3><span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-bold text-[#123fa5]" data-cart-count>0 items</span></div><div class="space-y-3" data-cart></div><div class="mt-5 border-t border-slate-100 pt-4"><div class="flex justify-between text-lg font-bold text-[#071947]"><span>Total</span><span>₱<span data-total>0.00</span></span></div><button class="mt-4 min-h-12 w-full rounded-xl bg-[#f7941d] px-4 font-bold text-white transition hover:bg-[#d96d00] disabled:cursor-not-allowed disabled:opacity-50" type="button" data-checkout disabled>Proceed to payment</button></div></aside>
    </div>
</div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</div></div>

<?php require __DIR__ . '/../modals/pos-item.php'; ?>
<?php require __DIR__ . '/../modals/pos-checkout.php'; ?>

<?php require __DIR__ . '/../partials/receipt.php'; ?>
<script>
const sidebar = document.querySelector('[data-sidebar]');
const overlay = document.querySelector('[data-menu-overlay]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const storageKey = 'cite-pos-sidebar-collapsed';

const setMobileMenu = (open) => {
    sidebar.classList.toggle('-translate-x-full', !open);
    overlay.classList.toggle('hidden', !open);
};

document.querySelectorAll('[data-mobile-menu]').forEach((button) => {
    button.addEventListener('click', () => setMobileMenu(sidebar.classList.contains('-translate-x-full')));
});
overlay.addEventListener('click', () => setMobileMenu(false));

const setSidebarCollapsed = (collapsed) => {
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    sidebar.classList.toggle('w-66', !collapsed);
    sidebar.classList.toggle('w-19', collapsed);
    sidebar.querySelectorAll('.sidebar-label').forEach((label) => label.classList.toggle('hidden', collapsed));
    sidebarToggle.querySelector('[data-toggle-icon]').classList.toggle('rotate-180', collapsed);
    sidebarToggle.querySelector('.sidebar-label').textContent = collapsed ? 'Show sidebar' : 'Hide sidebar';
    localStorage.setItem(storageKey, collapsed ? '1' : '0');
};

setSidebarCollapsed(localStorage.getItem(storageKey) === '1');
sidebarToggle.addEventListener('click', () => setSidebarCollapsed(!document.body.classList.contains('sidebar-collapsed')));
</script>
<script>
const cart = [], itemModal = document.querySelector('[data-modal="item"]'), checkoutModal = document.querySelector('[data-modal="checkout"]'), itemForm = document.querySelector('[data-item-form]');
document.querySelectorAll('[data-product]').forEach((button) => button.addEventListener('click', () => { const product = JSON.parse(button.dataset.product); itemForm.dataset.product = JSON.stringify(product); itemForm.querySelector('[name="product_id"]').value = product.id; itemForm.querySelector('[data-item-name]').textContent = product.name + ' · ₱' + product.price.toFixed(2); itemModal.classList.remove('hidden'); itemModal.classList.add('flex'); itemForm.querySelector('[name="owner_name"]').focus(); }));
document.querySelectorAll('[data-close]').forEach((button) => button.addEventListener('click', () => { button.closest('[data-modal]').classList.add('hidden'); button.closest('[data-modal]').classList.remove('flex'); }));
itemForm.addEventListener('submit', (event) => { event.preventDefault(); const product = JSON.parse(itemForm.dataset.product); cart.push({ ...product, owner: itemForm.querySelector('[name="owner_name"]').value, department: itemForm.querySelector('[name="department_id"]').value, year: itemForm.querySelector('[name="year"]').value, section: itemForm.querySelector('[name="section"]').value, quantity: Number(itemForm.querySelector('[name="quantity"]').value) }); itemForm.reset(); itemModal.classList.add('hidden'); itemModal.classList.remove('flex'); renderCart(); });
function renderCart(){ document.querySelector('[data-cart-count]').textContent = cart.reduce((sum,item)=>sum+item.quantity,0)+' items'; document.querySelector('[data-total]').textContent = cart.reduce((sum,item)=>sum+item.price*item.quantity,0).toFixed(2); document.querySelector('[data-cart]').innerHTML = cart.length ? cart.map((item,index)=>`<div class="rounded-xl bg-slate-50 p-3 text-sm"><div class="flex justify-between font-bold"><span>${item.name}</span><button type="button" onclick="cart.splice(${index},1);renderCart()" class="text-red-500">×</button></div><p class="mt-1 text-slate-500">${item.owner} · ${item.quantity} × ₱${item.price.toFixed(2)}</p></div>`).join('') : '<p class="py-8 text-center text-sm text-slate-400">No products added yet.</p>'; document.querySelector('[data-checkout]').disabled = !cart.length; }
const paymentInput = document.querySelector('[name="amount_paid"]'), completePayment = document.querySelector('[data-complete-payment]'), paymentError = document.querySelector('[data-payment-error]'), changeBox = document.querySelector('[data-change]');
function updatePaymentState() { const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0), hasPayment = paymentInput.value.trim() !== '', paid = Number(paymentInput.value) || 0, valid = hasPayment && paid >= total; document.querySelector('[data-payment-total]').textContent = total.toFixed(2); completePayment.disabled = !valid; paymentError.classList.toggle('hidden', !hasPayment || valid); changeBox.classList.toggle('hidden', !valid); changeBox.classList.toggle('flex', valid); document.querySelector('[data-change-amount]').textContent = Math.max(0, paid - total).toFixed(2); }
paymentInput.addEventListener('input', updatePaymentState);
document.querySelector('[data-checkout]').addEventListener('click', () => { const target = document.querySelector('[data-hidden-items]'); target.innerHTML = cart.map((item) => `<input type="hidden" name="product_id[]" value="${item.id}"><input type="hidden" name="owner_name[]" value="${item.owner.replace(/"/g,'&quot;')}"><input type="hidden" name="department_id[]" value="${item.department}"><input type="hidden" name="year[]" value="${item.year.replace(/"/g,'&quot;')}"><input type="hidden" name="section[]" value="${item.section.replace(/"/g,'&quot;')}"><input type="hidden" name="quantity[]" value="${item.quantity}">`).join(''); paymentInput.value = ''; updatePaymentState(); checkoutModal.classList.remove('hidden'); checkoutModal.classList.add('flex'); });
renderCart();
<?php if ($receipt): ?>document.querySelector('[data-print-receipt]').addEventListener('click', () => window.print());<?php endif; ?>
</script>
</body></html>
