<aside class="sidebar" id="sidebar">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 border-b border-white/10 h-auto py-4">
        <div class="flex flex-col items-center justify-center py-4">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}" 
                    alt="TouchStar PMS" 
                    class="w-[130px] h-[120px] rounded-full ml-10"> <!-- custom px size -->
            @else
                <div class="logo-placeholder w-[60px] h-[60px] flex items-center justify-center rounded-full bg-gray-700">
                    <span class="text-lg font-bold text-white">M</span>
                </div>
            @endif

            <div class="mt-2 text-center">
                <h1 class="text-white font-semibold text-base leading-tight ml-10">Touchstar Medical</h1>
                <p class="text-blue-200 text-xs ml-10">Enterprise Inc.</p>
            </div>
        </div>
        <!-- Mobile Close Button -->
        <button class="text-white/80 hover:text-white md:hidden" onclick="closeSidebar()">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>

    
    <!-- Navigation Menu -->
    <div class="px-4 py-6">
        <ul class="space-y-2">
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('tat-report') }}" class="nav-button active" onclick="setActiveNav(this)">
                    <div class="flex items-center">
                        <i class="fa-solid fa-stopwatch w-5 text-center text-white"></i>
                        <span class="ml-3 text-white">TAT REPORT</span>
                    </div>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('daily-result') }}" class="nav-button active" onclick="setActiveNav(this)">
                    <div class="flex items-center">
                        <i class="fa-solid fa-calendar-day w-5 text-center text-white"></i>
                        <span class="ml-3 text-white">Daily Report Summary</span>
                    </div>
                </a>
            </li>
        </ul>
    </div>
   
    <!-- User Profile Section -->
    {{-- <div class="user-profile">
        <div class="flex items-center">
            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold shadow-lg">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) : 'U' }}
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">
                    @auth
                        {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                    @else
                        Guest User
                    @endauth
                </p>
                <p class="text-xs text-blue-200 font-medium truncate">
                    @auth
                        {{ strtoupper(auth()->user()->role ?? 'USER') }}
                    @else
                        GUEST
                    @endauth
                </p>
            </div>
            @auth
                <form method="POST" action="" class="ml-2">
                    @csrf
                    <button type="submit" 
                            class="text-white/70 hover:text-white transition-colors duration-200 p-2 rounded-md hover:bg-white/10" 
                            title="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            @endauth
        </div>
    </div> --}}
</aside>
