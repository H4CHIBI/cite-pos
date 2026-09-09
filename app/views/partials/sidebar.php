<?php
$baseFolder = $baseFolder ?? '/pos-cite';
$currentRoute = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$currentPage = basename($currentRoute);
$isAdmin = strtolower($_SESSION['role'] ?? '') === 'admin';
$isCashier = strtolower($_SESSION['role'] ?? '') === 'cashier';
$isOfficer = strtolower($_SESSION['role'] ?? '') === 'officer';
$navItems = [
    ['Dashboard', 'dashboard', '<path d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-16v4h6V4h-6Z"></path>'],
    ['Point of Sale', 'pos', '<path d="M4 5h16v14H4zM8 9h8M8 13h3M14 13h2M8 16h8"></path>'],
    ['Products', 'products', '<path d="m20 7-8-4-8 4 8 4 8-4ZM4 12l8 4 8-4M4 17l8 4 8-4"></path>'],
    ['Inventory', 'inventory', '<path d="M4 7h16M4 12h16M4 17h16M6 4h.01M6 9h.01M6 14h.01"></path>'],
    ['Batches', 'batches', '<rect x="4" y="4" width="16" height="16" rx="2"></rect><path d="M8 8h8M8 12h8M8 16h5"></path>'],
    ['Departments', 'departments', '<path d="M4 21v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"></path>'],
    ['Releasing', 'releasing', '<path d="M4 5h16v14H4zM8 9h8M8 13h5"></path>'],
    ['Released', 'released', '<path d="m5 12 4 4L19 6"></path>'],
    ['Transactions', 'transactions', '<path d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h4"></path>'],
    ['Audit Logs', 'audit-logs', '<path d="M12 8v4l2.5 2.5M20 12a8 8 0 1 1-2.34-5.66"></path>'],
    ['User Management', 'users', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7v6m3-3h-6"></path>']
];
if (!$isAdmin) {
    $allowed = $isCashier ? ['dashboard', 'pos', 'transactions'] : ($isOfficer ? ['dashboard', 'releasing', 'released'] : ['dashboard', 'transactions']);
    $navItems = array_values(array_filter($navItems, static fn ($item) => in_array($item[1], $allowed, true)));
} else {
    $navItems = array_values(array_filter($navItems, static fn ($item) => $item[1] !== 'pos'));
}
?>
<aside class="group/sidebar fixed inset-y-0 left-0 z-40 flex w-66 -translate-x-full flex-col bg-[#071947] text-white shadow-xl transition-all duration-300 lg:relative lg:translate-x-0" data-sidebar>
    <div class="flex h-18 items-center border-b border-white/10 px-5">
        <a class="flex items-center gap-3 overflow-hidden" href="<?= $baseFolder ?>/dashboard">
            <img class="h-10 w-10 shrink-0 object-contain" src="<?= $baseFolder ?>/assets/images/logo_it.png" alt="CITE POS">
            <span class="sidebar-label whitespace-nowrap text-lg font-bold tracking-tight">CITE <span class="text-[#f7941d]">POS</span></span>
        </a>
        <button class="ml-auto rounded p-1 text-white/60 hover:bg-white/10 lg:hidden" type="button" data-mobile-menu aria-label="Close navigation">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"></path></svg>
        </button>
    </div>
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
        <?php foreach ($navItems as $item): ?>
            <a class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition hover:bg-white/10 hover:text-white <?= $currentPage === $item[1] ? 'bg-[#123fa5] text-white shadow-lg shadow-blue-950/30' : 'text-white/65' ?>" href="<?= $baseFolder ?>/<?= $item[1] ?>" title="<?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8') ?>">
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $item[2] ?></svg>
                <span class="sidebar-label whitespace-nowrap"><?= htmlspecialchars($item[0], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="border-t border-white/10 p-3">
        <button class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-white/65 transition hover:bg-white/10 hover:text-white" type="button" data-sidebar-toggle>
            <svg class="h-5 w-5 shrink-0 transition-transform" data-toggle-icon viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"></path></svg>
            <span class="sidebar-label whitespace-nowrap">Hide sidebar</span>
        </button>
        <a class="mt-1 flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-white/65 transition hover:bg-red-500/20 hover:text-red-200" href="<?= $baseFolder ?>/logout" data-confirm="logout">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 17l5-5-5-5M15 12H3M21 4v16"></path></svg>
            <span class="sidebar-label whitespace-nowrap">Sign out</span>
        </a>
    </div>
</aside>
<div class="fixed inset-0 z-30 hidden bg-[#071947]/50 lg:hidden" data-menu-overlay></div>
