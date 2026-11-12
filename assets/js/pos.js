/* POS kasir: keranjang di sisi klien, dikirim sebagai JSON; server menghitung ulang semua harga. */
(function () {
  const rp = n => 'Rp' + Math.round(n).toLocaleString('id-ID');
  const data = window.KK_POS; if (!data) return;
  const P = data.products, extra = data.sizeExtra, disc = data.discountMin, discAmt = data.discountAmt;
  let cart = [];
  const $ = s => document.querySelector(s);
  const lines = $('#pcLines'), jsonIn = $('#itemsJson');
  function totals() {
    let sub = 0; cart.forEach(l => { const p = P[l.id]; sub += (p.price + (p.size && l.size === 'large' ? extra.large : 0)) * l.qty; });
    const d = sub >= disc ? discAmt : 0; return { sub, d, total: sub - d };
  }
  function render() {
    lines.innerHTML = cart.length ? '' : '<div class="empty small">Pilih produk di sebelah kiri.</div>';
    cart.forEach((l, i) => {
      const p = P[l.id], u = p.price + (p.size && l.size === 'large' ? extra.large : 0), el = document.createElement('div'); el.className = 'pc-line';
      el.innerHTML = '<div><b>' + p.name + '</b><div class="small muted">' + rp(u) + '</div></div><div class="num"><b>' + rp(u * l.qty) + '</b></div>' +
        '<div class="q"><button type="button" data-a="m">−</button><span>' + l.qty + '</span><button type="button" data-a="p">+</button>' +
        (p.size ? '<select data-a="s"><option value="regular"' + (l.size === 'regular' ? ' selected' : '') + '>Regular</option><option value="large"' + (l.size === 'large' ? ' selected' : '') + '>Large (+' + extra.large / 1000 + 'rb)</option></select>' : '') + '</div>' +
        '<div class="num"><button type="button" class="btn btn-sm btn-danger" data-a="d">Hapus</button></div>';
      el.addEventListener('click', e => { const a = e.target.dataset.a; if (!a) return; if (a === 'p') l.qty = Math.min(50, l.qty + 1); if (a === 'm') l.qty--; if (a === 'd') l.qty = 0; if (l.qty <= 0) cart.splice(i, 1); render(); });
      const sel = el.querySelector('select'); if (sel) sel.addEventListener('change', () => { l.size = sel.value; merge(); render(); });
      lines.appendChild(el);
    });
    const t = totals();
    $('#sSub').textContent = rp(t.sub); $('#sDisc').textContent = '-' + rp(t.d); $('#sTotal').textContent = rp(t.total);
    jsonIn.value = JSON.stringify(cart.map(l => ({ id: l.id, qty: l.qty, size: l.size })));
    calcChange(); $('#btnPay').disabled = cart.length === 0;
  }
  function merge() { const m = {}; cart = cart.filter(l => { const k = l.id + l.size; if (m[k]) { m[k].qty += l.qty; return false; } m[k] = l; return true; }); }
  function calcChange() {
    const t = totals().total, m = $('#payMethod').value, paid = +$('#amountPaid').value || 0, box = $('#changeBox');
    $('#cashRow').hidden = m !== 'cash'; $('#refRow').hidden = m === 'cash';
    if (m === 'cash') { const c = paid - t; box.hidden = false; box.querySelector('b').textContent = c >= 0 ? rp(c) : 'Kurang ' + rp(-c); } else box.hidden = true;
  }
  document.querySelectorAll('.pos-item').forEach(b => b.addEventListener('click', () => {
    const id = b.dataset.id, p = P[id]; if (!p) return;
    const ex = cart.find(l => l.id == id && l.size === (p.size ? 'regular' : '')); if (ex) ex.qty = Math.min(50, ex.qty + 1); else cart.push({ id: +id, qty: 1, size: p.size ? 'regular' : '' });
    render();
  }));
  const f = $('#ordType'); const tb = $('#tableRow');
  const tog = () => tb.hidden = f.value !== 'dine_in'; f.addEventListener('change', tog); tog();
  ['payMethod', 'amountPaid'].forEach(i => $('#' + i).addEventListener('input', calcChange));
  document.querySelectorAll('[data-quick]').forEach(b => b.addEventListener('click', () => { $('#amountPaid').value = b.dataset.quick === 'pas' ? Math.round(totals().total) : b.dataset.quick; calcChange(); }));
  $('#posForm').addEventListener('submit', e => { if (!cart.length) e.preventDefault(); });
  // cari & filter produk
  const q = $('#posSearch'), cat = $('#posCats');
  let cc = 'all';
  const filt = () => document.querySelectorAll('.pos-item').forEach(b => { b.hidden = !((cc === 'all' || b.dataset.cat === cc) && (b.dataset.name || '').includes((q.value || '').toLowerCase())); });
  q.addEventListener('input', filt);
  cat.querySelectorAll('a').forEach(a => a.addEventListener('click', e => { e.preventDefault(); cat.querySelectorAll('a').forEach(x => x.classList.remove('active')); a.classList.add('active'); cc = a.dataset.cat; filt(); }));
  render();
})();
