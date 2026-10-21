<!DOCTYPE html>
<html lang="en">
<head>
    <title>Touchstar Medical Enterprises Inc. — @yield('title', 'Homepage')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=DM+Sans:400,500,600,700,800,900|Plus+Jakarta+Sans:400,500,600,700,800,900&amp;subset=latin">
    <link rel="stylesheet" href="/css/tailwind/tailwind.min.css">

   
    <link rel="icon" type="image/png" href="{{ asset('/logo/tmei.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> -->
  
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap"
          rel="stylesheet">
          <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style>
    /* Brand Colors from Logo */
    :root {
        --brand-navy: #1565c0;  /* From the logo's outer ring */
        --brand-blue: #1E5EFF;  /* From the logo's swoosh */
    }

    body { font-family: 'DM Sans', sans-serif; }
    .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }

    /* Sidebar transitions */
   .sidebar {
    width: 16rem;
    min-width: 16rem;
    height: 100vh;
    overflow-y: auto;
    flex-shrink: 0;
    background-color: var(--brand-navy) !important;
    transition: transform 0.25s ease-in-out,
                margin-left 0.25s ease-in-out;
}

/* Desktop: fully hide sidebar */
@media (min-width: 1024px) {
    .sidebar {
        position: relative;
        transform: translateX(0);
        margin-left: 0;
    }

    .sidebar.collapsed {
        margin-left: -16rem;
    }
}

/* Mobile and tablet */
@media (max-width: 1023px) {
    .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 50;
        width: 16rem;
        min-width: 16rem;
        transform: translateX(-100%);
        margin-left: 0;
    }

    .sidebar.open {
        transform: translateX(0);
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        z-index: 40;
    }

    .sidebar-overlay.active {
        display: block;
    }
}
</style>
</head>
<body class="antialiased bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay (mobile only) -->
        <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        @include('partials.sidebar')

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Navbar -->
            @include('partials.navbar')

            <!-- Main Content -->
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
                <div class="max-w-full mx-auto">
                    @yield('content')
                </div>
            </main>

            <!-- Footer -->
            @include('partials.footer')
        </div>
    </div>

    <script>
        // Single toggle that behaves correctly on mobile (drawer) and desktop (collapse)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isDesktop = window.matchMedia('(min-width: 1024px)').matches;

            if (isDesktop) {
                sidebar.classList.toggle('collapsed');
            } else {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            }
        }

        // Close mobile drawer after navigating
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarLinks = document.querySelectorAll('#sidebar a[href]:not([data-bs-toggle])');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function () {
                    if (!window.matchMedia('(min-width: 1024px)').matches) {
                        toggleSidebar();
                    }
                });
            });

            // Reset drawer state cleanly when crossing the breakpoint
            window.addEventListener('resize', function () {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (window.matchMedia('(min-width: 1024px)').matches) {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('active');
                } else {
                    sidebar.classList.remove('collapsed');
                }
            });
        });
    </script>
</body>
</html>
