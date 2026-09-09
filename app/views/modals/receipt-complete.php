<div class="fixed inset-0 z-50 flex items-center justify-center bg-[#071947]/60 p-4" data-receipt-actions>
    <div class="flex h-[90vh] w-full max-w-4xl flex-col rounded-2xl bg-white p-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-xl font-bold text-[#071947]">Print transaction</h2>
            <a class="rounded-lg px-3 py-2 text-2xl leading-none text-slate-400 hover:bg-slate-100" href="<?= $baseFolder ?>/pos" aria-label="Close">×</a>
        </div>
        <iframe class="mt-3 min-h-0 flex-1 rounded-xl border border-slate-200 bg-slate-50" title="Transaction receipt preview" src="<?= $baseFolder ?>/transactions/print?id=<?= (int) $receipt['id'] ?>&auto=0"></iframe>
        <div class="flex justify-end gap-3 border-t border-slate-100 pt-3">
            <a class="rounded-xl border border-slate-200 px-5 py-3 font-bold text-slate-600" href="<?= $baseFolder ?>/pos">Cancel</a>
            <button type="button" class="rounded-xl bg-[#123fa5] px-5 py-3 font-bold text-white" data-print-receipt>Print receipt</button>
        </div>
    </div>
</div>
