<footer class="mt-20 bg-brand-50">
  <div class="mx-auto grid max-w-6xl gap-10 px-6 py-12 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
    <div>
      @include('partials.logo')
      <p class="mt-4 max-w-xs text-sm text-slate-600">Ekosistem karir masa depan untuk talenta muda, pekerja lepas kreatif, dan pelajar di pasar berkembang. Bangun JejakKarier pencapaian terverifikasi.</p>
      <span class="mt-4 inline-block rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-800">Komitmen SDG 8: Pekerjaan Layak &amp; Pertumbuhan Ekonomi</span>
    </div>
    @foreach (['Eksplorasi' => ['Katalog Portofolio', 'Jadwal Sesi Mentor', 'Peta Jalur Profesi', 'Uji Kompetensi Terbuka'],
               'Komunitas' => ['Menjadi Mentor', 'Program Kampus Mitra', 'Grup Diskusi Talenta', 'Cerita Sukses Alumni'],
               'Legal & Bantuan' => ['Pusat Dukungan', 'Pedoman Komunitas', 'Kebijakan Privasi', 'Syarat Layanan']] as $judul => $tautan)
      <div><h4 class="font-bold text-slate-900">{{ $judul }}</h4>
        <ul class="mt-4 space-y-2.5 text-sm text-slate-600">@foreach ($tautan as $t)<li><a href="#" class="hover:text-brand">{{ $t }}</a></li>@endforeach</ul></div>
    @endforeach
  </div>
  <div class="mx-auto flex max-w-6xl justify-between px-6 pb-8 text-xs text-slate-500">
    <span>© {{ date('Y') }} JejakKarier. Hak Cipta Dilindungi Undang-Undang.</span><span class="font-semibold">Mendorong Pemerataan Peluang Karier Digital</span>
  </div>
</footer>
