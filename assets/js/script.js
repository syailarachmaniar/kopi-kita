/* KopiKita – interaksi website pelanggan */
(function () {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  document.documentElement.classList.add('js');

  // Navbar mobile
  const tg = $('.nav-toggle'), nl = $('.nav-links');
  if (tg && nl) tg.addEventListener('click', () => {
    const o = nl.classList.toggle('open'); tg.setAttribute('aria-expanded', o);
  });
  // Dropdown user
  const um = $('.user-menu');
  if (um) {
    $('.user-btn', um).addEventListener('click', e => { e.stopPropagation(); um.classList.toggle('open'); });
    document.addEventListener('click', () => um.classList.remove('open'));
  }
  // Tutup alert
  $$('.alert-x').forEach(b => b.addEventListener('click', () => b.parentElement.remove()));
  // Konfirmasi form
  $$('form[data-confirm]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  // Hero slider
  const slides = $$('.hero-slide');
  if (slides.length) {
    const dots = $$('.hero-dots button'); let i = 0, timer;
    const show = n => { i = (n + slides.length) % slides.length; slides.forEach((s, k) => s.classList.toggle('active', k === i)); dots.forEach((d, k) => d.classList.toggle('active', k === i)); };
    const auto = () => { clearInterval(timer); timer = setInterval(() => show(i + 1), 6500); };
    dots.forEach((d, k) => d.addEventListener('click', () => { show(k); auto(); }));
    auto();
  }

  // Carousel dengan tombol panah
  $$('.carousel').forEach(c => {
    const tr = $('.carousel-track', c);
    const step = () => Math.max(tr.clientWidth * 0.8, 260);
    const p = $('.prev', c), n = $('.next', c);
    if (p) p.addEventListener('click', () => tr.scrollBy({ left: -step(), behavior: 'smooth' }));
    if (n) n.addEventListener('click', () => tr.scrollBy({ left: step(), behavior: 'smooth' }));
  });

  // Filter chip (menu & galeri). data-filter-group menghubungkan chip dengan item.
  $$('[data-chips]').forEach(group => {
    const target = group.dataset.chips, attr = group.dataset.attr || 'cat';
    const items = $$('[data-item="' + target + '"]');
    const search = $('[data-search="' + target + '"]');
    const empty = $('[data-empty="' + target + '"]');
    let cur = 'all';
    const apply = () => {
      const q = search ? search.value.trim().toLowerCase() : '';
      let shown = 0;
      items.forEach(it => {
        const ok = (cur === 'all' || it.dataset[attr] === cur) && (!q || (it.dataset.name || '').includes(q));
        it.classList.toggle('hide', !ok); it.hidden = !ok; if (ok) shown++;
      });
      if (empty) empty.hidden = shown !== 0;
    };
    $$('.chip', group).forEach(ch => ch.addEventListener('click', () => {
      $$('.chip', group).forEach(x => x.classList.remove('active')); ch.classList.add('active'); cur = ch.dataset.value; apply();
    }));
    if (search) search.addEventListener('input', apply);
    // kategori awal dari URL (?cat=slug)
    const init = new URLSearchParams(location.search).get('cat');
    if (init) { const b = $$('.chip', group).find(x => x.dataset.value === init); if (b) b.click(); }
    apply();
  });
  // Pencarian tanpa chip (halaman menu per kategori: kategori sudah difilter server)
  $$('[data-search]').forEach(inp => {
    const t = inp.dataset.search;
    if ($('[data-chips="' + t + '"]')) return;
    const items = $$('[data-item="' + t + '"]'), empty = $('[data-empty="' + t + '"]');
    inp.addEventListener('input', () => {
      const q = inp.value.trim().toLowerCase(); let n = 0;
      items.forEach(it => { const ok = !q || (it.dataset.name || '').includes(q); it.classList.toggle('hide', !ok); it.hidden = !ok; if (ok) n++; });
      if (empty) empty.hidden = n !== 0;
    });
  });
  const fo = new URLSearchParams(location.search).get('focus');
  const si = $('[data-search]'); if (fo && si) si.focus();

  // Lightbox
  const lb = document.createElement('div'); lb.className = 'lightbox'; lb.innerHTML = '<button aria-label="Tutup">&times;</button><img alt="">';
  document.body.appendChild(lb);
  const lbi = $('img', lb);
  document.addEventListener('click', e => {
    const t = e.target.closest('[data-zoom]');
    if (t) { lbi.src = t.dataset.zoom; lb.classList.add('open'); return; }
    if (lb.classList.contains('open') && (e.target === lb || e.target.tagName === 'BUTTON')) lb.classList.remove('open');
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') lb.classList.remove('open'); });

  // Animasi muncul saat scroll
  const rv = $$('.reveal');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(es => es.forEach(en => { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } }), { threshold: .12 });
    rv.forEach(r => io.observe(r));
  } else rv.forEach(r => r.classList.add('in'));

  // Tampil/sembunyi password
  $$('.pw-toggle').forEach(b => b.addEventListener('click', () => {
    const inp = b.previousElementSibling; inp.type = inp.type === 'password' ? 'text' : 'password'; b.textContent = inp.type === 'password' ? 'Lihat' : 'Sembunyi';
  }));
})();
