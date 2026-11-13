<a href="{{ route('beranda') }}" class="flex items-center gap-2.5">
  <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-brand to-brand-600 text-white shadow-lg shadow-brand/30">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/></svg>
  </span>
  <span class="leading-tight"><span class="font-bold text-slate-900">Jejak<span class="text-brand">Karier</span></span>
    @isset($sub)<span class="block text-[9px] font-semibold uppercase tracking-wider text-slate-400">{{ $sub }}</span>@endisset</span>
</a>
