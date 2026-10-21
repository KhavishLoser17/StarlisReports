<nav class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-4">
                <!-- Burger Button -->
                <button onclick="toggleSidebar()" aria-label="Toggle sidebar" class="p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-[#0A3D62] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <span class="hidden sm:block text-sm font-medium text-slate-400">@yield('title', 'Dashboard')</span>
            </div>

            <div class="flex items-center gap-3">
                <!-- Notifications -->
                <button class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-[#0A3D62] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <div class="w-px h-6 bg-slate-200"></div>

                <!-- Profile Dropdown -->
                <div class="relative" id="profileDropdown">
                    <button onclick="toggleProfileDropdown()" class="flex items-center gap-2 p-1.5 pr-3 rounded-lg hover:bg-slate-100 transition-colors">
                        <!-- Changed bg to Brand Navy -->
                        <div class="w-8 h-8 bg-[#0A3D62] rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-semibold text-white">TS</span>
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-medium text-slate-900 leading-tight">Touchstar IT TEAM</p>
                            <p class="text-xs text-slate-500 leading-tight">touchstarIT</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profileDropdownMenu" class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-50 hidden">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-medium text-slate-900">Touchstar IT TEAM</p>
                            <p class="text-xs text-slate-500 truncate">touchstarIT</p>
                        </div>

                        <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-[#0A3D62] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile Settings
                        </a>

                        <div class="border-t border-slate-100 mt-1 pt-1">
                            <button onclick="handleLogout()" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleProfileDropdown() {
        const menu = document.getElementById('profileDropdownMenu');
        menu.classList.toggle('hidden');
    }

    function handleLogout() {
        alert('Logout clicked - Add your logout logic here');
    }

    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profileDropdown');
        const menu = document.getElementById('profileDropdownMenu');
        if (dropdown && !dropdown.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
</script>