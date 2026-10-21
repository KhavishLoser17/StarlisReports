<footer class="bg-[#1565c0] py-1 px-1 sm:px-4 lg:px-2 mt-auto">
    <div class="max-w-full mx-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <!-- Company Info -->
            <div class="flex items-center gap-3">
                <img src="logo/tmei.png" alt="TMEI Logo" class="h-7 w-auto">
                <!-- Changed divider to dark blue with low opacity -->
                <span class="text-[#0A3D62]/30">|</span> 
                <div class="flex flex-col">
                    <!-- Changed text to dark navy blue -->
                    <span class="text-sm text-[#0A3D62] font-medium">© <span id="currentYear"></span> Touchstar Medical Enterprises Inc.</span>
                    <span class="text-xs text-[#0A3D62]/70">Starlis Cloud - Laboratory Information System</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex items-center gap-6">
                <!-- Changed text to dark navy blue with hover to full black -->
                <a href="#" class="text-xs text-[#0A3D62]/80 hover:text-[#0A3D62] transition-colors font-medium">Privacy Policy</a>
                <a href="#" class="text-xs text-[#0A3D62]/80 hover:text-[#0A3D62] transition-colors font-medium">Terms of Service</a>
                <a href="#" class="text-xs text-[#0A3D62]/80 hover:text-[#0A3D62] transition-colors font-medium">Support</a>
            </div>

            <!-- Version Info -->
            <!-- Changed text to dark navy blue with lower opacity -->
            <div class="flex items-center gap-2 text-xs text-[#0A3D62]/60 font-medium">
                <span>Version 1.0.0</span>
                <span>•</span>
                <span>Build 2026.01</span>
            </div>
        </div>
    </div>
</footer>

<script>
    document.getElementById('currentYear').textContent = new Date().getFullYear();
</script>