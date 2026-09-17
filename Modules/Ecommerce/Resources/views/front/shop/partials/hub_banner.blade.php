@php
    $hub = $hub ?? null;
@endphp
@if($hub)
<section class="rounded-2xl overflow-hidden mb-8 text-white relative min-h-[180px]">
    <img src="{{ $hub['image'] }}" alt="" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
    <div class="relative p-6 md:p-10 max-w-2xl">
        <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color:#9ae6b4">{{ $hub['kicker'] }}</p>
        <h2 class="font-display text-3xl md:text-4xl font-bold mb-2">{{ $hub['title'] }}</h2>
        <p class="text-sm text-white/80 mb-4">{{ $hub['lede'] }}</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('ecommerce.compliance') }}" class="px-4 py-2 rounded-lg text-sm font-semibold border border-white/30">Full compliance & state map →</a>
            <a href="{{ route('ecommerce.register') }}" class="px-4 py-2 rounded-lg text-sm font-bold" style="background:#4CAF79">Apply for Account</a>
        </div>
    </div>
</section>
@endif
