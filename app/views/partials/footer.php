<footer class="mt-auto border-t border-slate-200 px-4 py-5 text-center text-xs text-slate-400 sm:px-8">
    CITE POS &middot; IT Department &middot; <?= date('Y') ?> &middot; Developed by <a href="https://github.com/VNR2WO" target="_blank" rel="noopener noreferrer" class="font-medium hover:text-slate-600 transition-colors">Vinardo Galula Butil</a>
</footer>
<?php require __DIR__ . '/../modals/confirmation.php'; ?>
<script>
let pendingConfirmation = null;
const confirmationModal = document.querySelector('[data-confirmation-modal]');
const closeConfirmation = () => { pendingConfirmation = null; confirmationModal.classList.add('hidden'); confirmationModal.classList.remove('flex'); };
document.querySelectorAll('[data-confirm="logout"]').forEach((link) => link.addEventListener('click', (event) => {
    event.preventDefault();
    pendingConfirmation = link;
    confirmationModal.querySelector('[data-confirmation-title]').textContent = 'Sign out?';
    confirmationModal.querySelector('[data-confirmation-message]').textContent = 'Are you sure you want to sign out?';
    confirmationModal.classList.remove('hidden');
    confirmationModal.classList.add('flex');
}));
confirmationModal.querySelector('[data-confirmation-cancel]').addEventListener('click', closeConfirmation);
confirmationModal.querySelector('[data-confirmation-accept]').addEventListener('click', () => {
    if (!pendingConfirmation) return;
    if (typeof pendingConfirmation.submit === 'function') {
        pendingConfirmation.submit();
        return;
    }
    window.location.href = pendingConfirmation.href;
});
</script>