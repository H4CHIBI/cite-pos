<div class="fixed inset-0 z-50 hidden items-center justify-center bg-[#071947]/60 p-4" data-modal="item">
    <form class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" data-item-form>
        <h3 class="text-xl font-bold text-[#071947]">Add product</h3>
        <p class="mt-1 text-sm text-slate-500" data-item-name></p>
        <input type="hidden" name="product_id">
        <label class="mt-5 block text-sm font-semibold text-slate-600">Owner of the shirt<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="owner_name" placeholder="Type the owner's name" required></label>
        <label class="mt-4 block text-sm font-semibold text-slate-600">Department<select class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="department_id" required><option value="">Select department</option><?php foreach ($departments as $department): ?><option value="<?= (int) $department['department_id'] ?>"><?= htmlspecialchars($department['department_code'] . ' - ' . $department['department_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
        <label class="mt-4 block text-sm font-semibold text-slate-600">Year<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal" name="year" placeholder="e.g. 1st Year" required></label>
        <label class="mt-4 block text-sm font-semibold text-slate-600">Section <span class="font-normal text-slate-400">(optional)</span><input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal" name="section" placeholder="e.g. A"></label>
        <label class="mt-4 block text-sm font-semibold text-slate-600">Quantity<input class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 px-3 font-normal outline-none focus:border-[#123fa5] focus:ring-4 focus:ring-blue-100" name="quantity" type="number" min="1" value="1" required></label>
        <div class="mt-6 flex gap-3"><button class="flex-1 rounded-xl border border-slate-200 py-3 font-bold text-slate-600" type="button" data-close>Cancel</button><button class="flex-1 rounded-xl bg-[#123fa5] py-3 font-bold text-white" type="submit">Add to order</button></div>
    </form>
</div>
