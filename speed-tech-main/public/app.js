/* Last Solution storefront — progressive enhancement over server-rendered pages. */
const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
const csrf = $('meta[name="csrf-token"]')?.content;
let toastTimer;

function showToast(message) {
  const toast = $('#toast');
  if (!toast) return;
  clearTimeout(toastTimer);
  toast.textContent = message;
  toast.classList.add('show');
  toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
}

async function send(form) {
  const response = await fetch(form.action, {
    method: 'POST',
    body: new FormData(form),
    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
  });
  let data = {};
  try { data = await response.json(); } catch { /* non-JSON error page */ }
  if (!response.ok) {
    const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
    if (response.status === 419) throw new Error('Your session expired. Please refresh the page and try again.');
    if (response.status === 429) throw new Error('Too many attempts. Please wait a minute and try again.');
    throw new Error(firstError || data.message || 'Something went wrong. Please try again or contact us on WhatsApp.');
  }
  return data;
}

function setCartCount(count) {
  const counter = $('#count');
  if (counter) counter.textContent = count;
  const link = $('[data-cart-link]');
  if (link) link.setAttribute('aria-label', `Cart, ${count} ${count === 1 ? 'item' : 'items'}`);
}

// ---------------------------------------------------------------- Dialogs
function resetEnquiryDialog(dialog) {
  const form = $('form', dialog);
  form.hidden = false;
  $('.form-error', form).hidden = true;
  $('.enquiry-done', dialog).hidden = true;
}

function openRepair(service) {
  const dialog = $('#repairDialog');
  resetEnquiryDialog(dialog);
  if (service) $('#service', dialog).value = service;
  dialog.showModal();
}

function openEnquiry({ type, productId = '', subject }) {
  const dialog = $('#enquiryDialog');
  const form = $('form', dialog);
  resetEnquiryDialog(dialog);
  form.elements.type.value = type;
  form.elements.product_id.value = productId;
  $('#enquiry-title', dialog).textContent = type === 'part' ? 'Tell us what you are looking for.' : 'Ask us about this item.';
  $('.enquiry-subject', dialog).textContent = subject;
  dialog.showModal();
}

document.addEventListener('click', event => {
  if (!(event.target instanceof Element)) return;

  const close = event.target.closest('[data-close]');
  if (close) close.closest('dialog').close();

  const repair = event.target.closest('[data-repair], [data-service]');
  if (repair) openRepair(repair.dataset.service);

  const enquire = event.target.closest('[data-enquire]');
  if (enquire) {
    openEnquiry({
      type: 'product',
      productId: enquire.dataset.enquire,
      subject: `${enquire.dataset.enquireName}. We'll confirm the price and availability.`,
    });
  }
});

$$('dialog').forEach(dialog => {
  dialog.addEventListener('click', event => {
    if (event.target !== dialog) return;
    const b = dialog.getBoundingClientRect();
    if (event.clientX < b.left || event.clientX > b.right || event.clientY < b.top || event.clientY > b.bottom) dialog.close();
  });
});

// Repair / enquiry forms are saved to the shop's database, then offer a WhatsApp follow-up.
$$('form.js-enquiry').forEach(form => {
  form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = $('button[type="submit"]', form);
    const error = $('.form-error', form);
    button.disabled = true;
    error.hidden = true;
    try {
      const data = await send(form);
      const done = $('.enquiry-done', form.closest('dialog, [data-enquiry-wrap]'));
      done.innerHTML = '';
      const heading = Object.assign(document.createElement('h3'), { textContent: `Reference ${data.reference}` });
      const text = Object.assign(document.createElement('p'), { textContent: data.message });
      const link = Object.assign(document.createElement('a'), { className: 'button whatsapp-link', href: data.whatsapp, target: '_blank', rel: 'noopener', textContent: 'Follow up on WhatsApp' });
      done.append(heading, text, link);
      form.hidden = true;
      done.hidden = false;
      form.reset();
    } catch (e) {
      error.textContent = e.message;
      error.hidden = false;
    } finally {
      button.disabled = false;
    }
  });
});

// ---------------------------------------------------------------- Cart
$$('form.js-add-to-cart').forEach(form => {
  form.addEventListener('submit', async event => {
    event.preventDefault();
    const button = $('button[type="submit"]', form);
    button.disabled = true;
    try {
      const data = await send(form);
      setCartCount(data.cart.count);
      showToast(data.message);
      if (data.ok) {
        button.classList.add('is-added');
        const label = $('span', button);
        if (label) label.textContent = 'Added — view cart';
        button.type = 'button';
        button.addEventListener('click', () => { location.href = $('[data-cart-link]').href; }, { once: true });
      }
    } catch (e) {
      showToast(e.message);
    } finally {
      button.disabled = false;
    }
  });
});

$$('[data-autosubmit]').forEach(control => control.addEventListener('change', () => control.form.requestSubmit ? control.form.requestSubmit() : control.form.submit()));

// ---------------------------------------------------------------- Checkout
const checkout = $('#checkoutForm');
if (checkout) {
  const subtotal = Number(checkout.dataset.subtotal);
  const fee = Number(checkout.dataset.deliveryFee);
  const currency = checkout.dataset.currency;
  const format = amount => `${currency} ${amount.toLocaleString('en-US', { maximumFractionDigits: 2 })}`;

  function syncCheckout() {
    const delivery = checkout.elements.fulfilment.value === 'delivery';
    const fields = $('[data-delivery-fields]', checkout);
    fields.hidden = !delivery;
    $$('textarea, input', fields).forEach(input => { input.required = delivery; });
    $('[data-delivery-output]', checkout).textContent = delivery ? (fee > 0 ? format(fee) : 'Free') : 'Free';
    $('[data-total-output]', checkout).textContent = format(subtotal + (delivery ? fee : 0));

    // Show only the payment methods that work with this delivery option
    // (e.g. cash on delivery for delivery, pay at the store for pickup).
    const fulfilment = delivery ? 'delivery' : 'pickup';
    const options = $$('[data-payment]', checkout);
    options.forEach(option => {
      option.hidden = !option.dataset.for.split(' ').includes(fulfilment);
      if (option.hidden) $('input', option).checked = false;
    });
    if (!options.some(option => !option.hidden && $('input', option).checked)) {
      // Default: cash on delivery for delivery, pay at the store for pickup.
      const preferred = { delivery: 'cod', pickup: 'pay_at_store' }[fulfilment];
      const pick = options.find(option => !option.hidden && option.dataset.payment === preferred)
        || options.find(option => !option.hidden);
      if (pick) $('input', pick).checked = true;
    }

    // Bank account details only when paying by bank transfer.
    const bank = $('[data-bank-details]', checkout);
    if (bank) bank.hidden = checkout.elements.payment_method.value !== 'bank_transfer';

    // Installation is only offered with delivery, not when collecting from the office.
    const addon = $('[data-installation-addon]', checkout);
    if (addon) {
      addon.hidden = !delivery;
      if (!delivery) checkout.elements.installation_requested.checked = false;
    }

    const installation = $('[data-installation-fields]', checkout);
    if (installation) {
      const requested = checkout.elements.installation_requested.checked;
      installation.hidden = !requested;
      $('textarea', installation).required = requested;
    }
  }

  checkout.addEventListener('change', syncCheckout);
  checkout.addEventListener('submit', () => { $('button[type="submit"]', checkout).disabled = true; });
  syncCheckout();
}

// About / Contact hero photos: crossfade like the home hero.
document.querySelectorAll('[data-page-hero]').forEach((media) => {
  const slides = media.querySelectorAll('[data-slide]');
  if (slides.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  let i = 0;
  setInterval(() => {
    if (document.hidden) return;
    slides[i].classList.remove('is-active');
    i = (i + 1) % slides.length;
    slides[i].classList.add('is-active');
  }, 6000);
});

// Contact map: load the Google embed straight away on larger screens, on tap on phones.
document.querySelectorAll('[data-map]').forEach((card) => {
  const load = () => {
    const frame = Object.assign(document.createElement('iframe'), {
      src: card.dataset.map, title: 'Map showing Last Solution in Nugawela, Kandy', loading: 'lazy', referrerPolicy: 'no-referrer-when-downgrade', allowFullscreen: true,
    });
    card.replaceChildren(frame);
  };
  const button = card.querySelector('.map-load');
  if (window.matchMedia('(min-width: 701px)').matches || !button) return load();
  button.hidden = false;
  button.addEventListener('click', load, { once: true });
});

// Home reviews carousel: arrows, swipe, progress bar and gentle autoplay
// (pauses on hover, touch, keyboard focus, off-screen or hidden tab; off for reduced motion).
document.querySelectorAll('[data-review-carousel]').forEach((root) => {
  const track = root.querySelector('[data-review-track]');
  const section = root.closest('section');
  const prev = section.querySelector('[data-review-prev]');
  const next = section.querySelector('[data-review-next]');
  const bar = root.querySelector('[data-review-progress]');
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const step = () => {
    const card = track.firstElementChild;
    return card ? card.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 14) : 300;
  };
  const max = () => track.scrollWidth - track.clientWidth;
  const update = () => {
    const m = max();
    if (prev) prev.disabled = track.scrollLeft <= 4;
    if (next) next.disabled = track.scrollLeft >= m - 4;
    if (bar) {
      bar.style.width = Math.max((m > 0 ? track.clientWidth / track.scrollWidth : 1) * 100, 8) + '%';
      bar.style.marginLeft = (m > 0 ? (track.scrollLeft / track.scrollWidth) * 100 : 0) + '%';
    }
  };
  const go = (dir) => track.scrollBy({ left: dir * step(), behavior: reduce ? 'auto' : 'smooth' });

  let timer = null, hovering = false, focused = false, inView = false, touchedAt = 0;
  const tick = () => {
    if (reduce || hovering || focused || !inView || document.hidden || Date.now() - touchedAt < 8000) return;
    if (max() <= 4) return;
    if (track.scrollLeft >= max() - 4) track.scrollTo({ left: 0, behavior: 'smooth' });
    else go(1);
  };
  const restart = () => { clearInterval(timer); if (!reduce) timer = setInterval(tick, 5000); };

  prev?.addEventListener('click', () => { go(-1); restart(); });
  next?.addEventListener('click', () => { go(1); restart(); });
  track.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
    if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
  });
  track.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
  track.addEventListener('touchstart', () => { touchedAt = Date.now(); }, { passive: true });
  section.addEventListener('mouseenter', () => { hovering = true; });
  section.addEventListener('mouseleave', () => { hovering = false; });
  // Pause for keyboard users only; a mouse click on an arrow shouldn't stop autoplay for good.
  section.addEventListener('focusin', (e) => { focused = e.target.matches(':focus-visible'); });
  section.addEventListener('focusout', (e) => { if (!section.contains(e.relatedTarget)) focused = false; });
  window.addEventListener('resize', update);
  if ('IntersectionObserver' in window) {
    new IntersectionObserver((entries) => { inView = entries[0].isIntersecting; }, { threshold: 0.3 }).observe(track);
  } else {
    inView = true;
  }
  update();
  restart();
});

// Shop sub-category chips: bring the selected chip into view (sideways only, never moves the page).
document.querySelectorAll('.sub-chips').forEach((row) => {
  const active = row.querySelector('a.active');
  if (active && active.offsetLeft + active.offsetWidth > row.clientWidth) {
    row.scrollLeft = active.offsetLeft - (row.clientWidth - active.offsetWidth) / 2;
  }
});
