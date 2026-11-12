/* Keranjang & detail produk: tombol +/- dan hitung total tampilan (server tetap menghitung ulang) */
(function () {
  const rp = n => 'Rp' + Math.round(n).toLocaleString('id-ID');
  document.querySelectorAll('.qty').forEach(q => {
    const inp = q.querySelector('input'), min = +inp.min || 1, max = +inp.max || 50;
    const form = inp.closest('form');
    q.querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
      let v = (+inp.value || min) + (b.dataset.d === '+' ? 1 : -1);
      inp.value = Math.min(max, Math.max(min, v));
      inp.dispatchEvent(new Event('change', { bubbles: true }));
    }));
    if (form && form.dataset.auto !== undefined) inp.addEventListener('change', () => form.submit());
  });
  document.querySelectorAll('form[data-auto] select, form[data-auto] .note').forEach(el => el.addEventListener('change', () => el.closest('form').submit()));

  // Halaman detail produk: total dinamis
  const box = document.querySelector('[data-detail]');
  if (box) {
    const base = +box.dataset.price, extra = JSON.parse(box.dataset.extra || '{}');
    const out = box.querySelector('[data-total]'), qty = box.querySelector('input[name="qty"]');
    const calc = () => {
      const s = box.querySelector('input[name="size"]:checked');
      out.textContent = rp((base + (s ? (extra[s.value] || 0) : 0)) * (+qty.value || 1));
    };
    box.addEventListener('change', calc); box.addEventListener('click', () => setTimeout(calc, 0)); calc();
  }
})();
