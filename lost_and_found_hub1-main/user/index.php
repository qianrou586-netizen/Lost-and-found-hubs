<?php
require_once __DIR__ . '/../includes/bootstrap.php';
ui_header('Home', 'home');
?>

<!-- Hero Section (Palette A - Non-White Deep Cobalt & Slate Architecture) -->
<section class="bg-gradient-to-b from-[#0f172a] via-[#1e3a8a] to-[#0f172a] text-white py-24 px-4 text-center relative overflow-hidden border-b border-[#cbd5e1]/40">
    
    <!-- Background Ambient Glow -->
    <div class="absolute inset-0 -z-10 flex items-center justify-center opacity-30 pointer-events-none">
        <div class="w-96 h-96 bg-blue-600 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-3xl mx-auto space-y-6 relative z-10">
        <!-- Badge -->
        <span class="inline-block text-[11px] tracking-widest font-bold text-blue-200 uppercase bg-blue-900/60 border border-blue-400/30 px-4 py-1.5 rounded-full backdrop-blur-md">
            Smart Matching for Lost &amp; Found
        </span>
        
        <!-- Title -->
        <h1 class="font-serif text-5xl md:text-6xl font-normal leading-tight text-white tracking-tight">
            Find What You <br><span class="italic font-light text-blue-300">Lost</span>
        </h1>
        
        <!-- Subtitle -->
        <p class="text-slate-300/90 text-sm md:text-base max-w-xl mx-auto font-normal leading-relaxed">
            Report lost or found items. Our algorithm automatically matches them based on category, location, and timing.
        </p>

        <!-- Action Buttons (Soft Crimson & Fresh Emerald) -->
        <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
            <a href="report.php?type=lost" class="px-6 py-3 rounded-full bg-[#ef4444] hover:bg-red-600 text-white text-sm font-semibold shadow-lg shadow-red-950/40 transition-all duration-200 hover:-translate-y-0.5 flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>I Lost Something</span>
            </a>
            
            <a href="report.php?type=found" class="px-6 py-3 rounded-full bg-[#10b981] hover:bg-emerald-600 text-white text-sm font-semibold shadow-lg shadow-emerald-950/40 transition-all duration-200 hover:-translate-y-0.5 flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-white"></span>
                <span>I Found Something</span>
            </a>
        </div>

        <!-- Search Input Bar -->
        <div class="pt-6 max-w-md mx-auto">
            <form action="items.php" method="get" class="relative">
                <input type="text" name="q" placeholder="Search by name, description..." 
                       class="w-full pl-11 pr-5 py-3.5 rounded-full bg-slate-900/80 hover:bg-slate-900 text-white placeholder-slate-400 border border-slate-700 focus:outline-none focus:border-[#3b82f6] focus:ring-2 focus:ring-blue-500/20 text-sm backdrop-blur-md transition shadow-inner">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-4 text-slate-400 text-sm"></i>
            </form>
        </div>
    </div>
</section>

<?php ui_footer(); ?>