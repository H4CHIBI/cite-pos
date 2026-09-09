<div class="fixed inset-0 z-50 hidden items-center justify-center bg-[#071947]/60 p-4" data-modal="checkout">
    <form class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" method="post">
        <h3 class="text-xl font-bold text-[#071947]">Payment details</h3><div data-hidden-items></div>
        <div class="mt-4 flex justify-between rounded-xl bg-blue-50 px-4 py-3 text-sm font-bold text-[#123fa5]"><span>Total due</span><span>₱<span data-payment-total>0.00</span></span></div>
        <label class="mt-5 block text-sm font-semibold text-slate-600">Ordering person<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="representative_name" placeholder="Name of person ordering" required></label>
        <label class="mt-4 block text-sm font-semibold text-slate-600">Position<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="representative_position" value="Student" required></label>
        <label class="mt-4 block text-sm font-semibold text-slate-600">Amount paid<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="amount_paid" type="number" min="0" step=".01" required></label>
        <p class="mt-3 hidden text-sm font-semibold text-red-600" data-payment-error>Amount paid must cover the total.</p>
        <p class="mt-3 hidden justify-between rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700" data-change><span>Change to give</span><span>₱<span data-change-amount>0.00</span></span></p>
        <div class="mt-6 flex gap-3"><button class="flex-1 rounded-xl border border-slate-200 py-3 font-bold text-slate-600" type="button" data-close>Cancel</button><button class="flex-1 rounded-xl bg-[#f7941d] py-3 font-bold text-white disabled:cursor-not-allowed disabled:opacity-50" type="submit" data-complete-payment disabled>Complete payment</button></div>
    </form>
</div>
