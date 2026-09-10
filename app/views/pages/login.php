<?php
$errorMessage = $errorMessage ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#071947">
    <title>Sign in | CITE POS</title>
    <link rel="icon" type="image/png" href="/pos-cite/public/assets/images/logo_it.png">
    <link rel="stylesheet" href="/pos-cite/public/assets/css/style.css">
</head>
<body class="m-0 min-h-screen bg-[#f5f7fc] font-sans text-[#17213a] flex flex-col">
    <main class="flex flex-1 flex-col items-center justify-center bg-[radial-gradient(circle_at_10%_10%,rgba(247,148,29,.12),transparent_26rem),radial-gradient(circle_at_95%_90%,rgba(18,63,165,.12),transparent_28rem),#f5f7fc] p-4 sm:p-8">
        <section class="grid min-h-[620px] w-full max-w-[1050px] grid-cols-[minmax(0,1.04fr)_minmax(360px,.96fr)] overflow-hidden rounded-[24px] border border-[#071947]/[0.08] bg-white shadow-[0_24px_70px_rgba(7,25,71,.16)] max-[760px]:block max-[760px]:min-h-0 max-[760px]:rounded-[18px]" aria-label="CITE POS sign in">
            <div class="relative flex flex-col justify-center overflow-hidden bg-[linear-gradient(145deg,#071947,#09245f_62%,#123b92)] p-8 text-white before:absolute before:-right-[210px] before:-bottom-[200px] before:h-[420px] before:w-[420px] before:rounded-full before:border before:border-[#f7941d]/30 after:absolute after:-right-[135px] after:-bottom-[135px] after:h-[290px] after:w-[290px] after:rounded-full after:border after:border-[#f7941d]/30 sm:p-16 max-[760px]:min-h-[320px] max-[760px]:p-10">
                <div class="relative z-[1]">
                    <img class="mb-7 block h-auto w-full max-w-[420px] sm:mb-[42px] max-[760px]:max-w-[320px]" src="/pos-cite/public/assets/images/logo.png" alt="College of Information Technology Education logo">
                    <p class="mb-[15px] inline-flex items-center gap-[9px] text-[.76rem] font-extrabold uppercase tracking-[.14em] text-[#ffb44b] before:h-[3px] before:w-6 before:rounded-full before:bg-[#f7941d]">IT Department</p>
                    <h1 class="m-0 max-w-[410px] text-[2rem] font-bold leading-[1.08] tracking-[-.04em] sm:text-[clamp(2rem,3.4vw,3rem)]">Point of Sale, made simple.</h1>
                    <p class="mt-3 max-w-[430px] text-[.9rem] leading-[1.7] text-white/70 sm:mt-[18px] sm:text-base">Manage department sales, student ownership, and releases from one secure workspace.</p>
                </div>
            </div>

            <div class="flex items-center px-8 py-[42px] sm:px-[clamp(32px,6vw,72px)] sm:py-16">
                <form class="w-full" method="post" action="">
                    <h2 class="m-0 text-[2rem] font-bold tracking-[-.035em] text-[#071947]">Welcome back</h2>
                    <p class="mb-[34px] mt-2.5 text-[.95rem] text-[#65708a]">Sign in to continue to CITE POS.</p>

                    <?php if ($errorMessage): ?>
                        <p class="mb-[22px] rounded-[9px] border border-[#f2c1bc] bg-[#fff3f1] p-3 text-[.87rem] leading-[1.4] text-[#a83228]" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <div class="mb-5">
                        <label class="mb-2 block text-[.83rem] font-bold text-[#071947]" for="username">Username</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-[15px] top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-[#8a96ad]" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M20 21a8 8 0 0 0-16 0"></path><circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <input class="min-h-[52px] w-full rounded-[11px] border border-[#dfe5f2] bg-[#fbfcff] px-4 pl-[46px] text-inherit outline-none transition focus:border-[#123fa5] focus:bg-white focus:ring-4 focus:ring-[#123fa5]/[0.12]" id="username" name="username" type="text" autocomplete="username" placeholder="Enter your username" required autofocus>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="mb-2 block text-[.83rem] font-bold text-[#071947]" for="password">Password</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-[15px] top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-[#8a96ad]" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            </svg>
                            <input class="min-h-[52px] w-full rounded-[11px] border border-[#dfe5f2] bg-[#fbfcff] px-4 pl-[46px] pr-12 text-inherit outline-none transition focus:border-[#123fa5] focus:bg-white focus:ring-4 focus:ring-[#123fa5]/[0.12]" id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" required>
                            <button class="absolute right-[13px] top-1/2 -translate-y-1/2 cursor-pointer border-0 bg-transparent p-[5px] text-[#7a8499] hover:text-[#123fa5]" type="button" aria-label="Show password" aria-controls="password">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.5"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button class="mt-[7px] min-h-[52px] w-full cursor-pointer rounded-[11px] border-0 bg-gradient-to-r from-[#d96d00] to-[#f7941d] font-bold text-white shadow-[0_9px_20px_rgba(217,109,0,.2)] transition hover:-translate-y-px hover:shadow-[0_12px_24px_rgba(217,109,0,.3)]" type="submit">Sign in to CITE POS</button>
                    <p class="mt-7 text-center text-[.78rem] leading-[1.6] text-[#8992a6]">Authorized IT Department personnel only.<br><strong class="text-[#123fa5]">CITE POS</strong> &middot; Secure sales management</p>
                </form>
            </div>
        </section>

        <footer class="mt-8 text-center text-xs text-slate-400 sm:mt-12 w-full">
            CITE POS &middot; IT Department &middot; <?= date('Y') ?> &middot; Developed by <a href="https://github.com/VNR2WO" target="_blank" rel="noopener noreferrer" class="font-medium hover:text-slate-600 transition-colors">Vinardo Galula Butil</a>
        </footer>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.querySelector('button[aria-controls="password"]');

        passwordToggle.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            passwordToggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    </script>
</body>
</html>