<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$baseFolder = $baseFolder ?? '/pos-cite';
$userName = $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'User';
$userRole = ucfirst($_SESSION['role'] ?? 'cashier');
?>
<header class="sticky top-0 z-30 flex h-[72px] items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-8">
    <div class="flex items-center gap-3">
        <button class="rounded-lg p-2 text-[#123fa5] transition hover:bg-blue-50 lg:hidden" type="button" data-mobile-menu aria-label="Open navigation">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.16em] text-[#f7941d]">CITE POS</p>
            <h1 class="text-lg font-bold text-[#071947]"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div class="hidden text-right sm:block">
            <p class="text-sm font-bold text-[#071947]"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-xs capitalize text-slate-500"><?= htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#e9efff] font-bold text-[#123fa5]"><?= htmlspecialchars(strtoupper(substr($userName, 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
</header>
