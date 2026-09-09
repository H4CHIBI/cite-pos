<div class="fixed inset-0 z-50 hidden items-center justify-center bg-[#071947]/60 p-4" data-transaction-modal>
    <form class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" method="post" data-transaction-action-form>
        <input type="hidden" name="action" value="void_transaction" data-transaction-action>
        <input type="hidden" name="transaction_id" data-transaction-id>
        <h2 class="text-xl font-bold text-[#071947]" data-transaction-modal-title>Void transaction</h2>
        <p class="mt-2 text-sm text-slate-500" data-transaction-modal-message>Admin password is required for this action.</p>
        <label class="mt-4 block text-sm font-semibold text-slate-600">Admin password<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal" name="admin_password" type="password" required autocomplete="current-password"></label>
        <div class="mt-6 flex gap-3"><button class="flex-1 rounded-xl border border-slate-200 py-3 font-bold text-slate-600" type="button" data-transaction-cancel>Cancel</button><button class="flex-1 rounded-xl bg-[#123fa5] py-3 font-bold text-white" type="submit">Continue</button></div>
    </form>
</div>
