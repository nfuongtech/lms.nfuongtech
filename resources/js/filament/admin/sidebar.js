// resources/js/filament/admin/sidebar.js
// Chế độ: 'always' | 'auto' | 'hidden'
(function () {
  const MODE_KEY = 'nf_sidebar_mode';
  const MODES = ['always', 'auto', 'hidden'];

  // Tham số mượt
  const OPEN_NEAR_PX   = 8;
  const CLOSE_DELAY_MS = 160;
  const CLOSE_FAR_PX   = 220;

  let insideSidebar = false;
  let closeTimer = null;
  let lastOpenAt = 0;

  const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;
  const getMode   = () => localStorage.getItem(MODE_KEY) || 'always';
  const setMode   = (m) => localStorage.setItem(MODE_KEY, m);
  const store     = () => window.Alpine?.store('sidebar');

  // Nhãn trạng thái bên trái icon
  const labels = {
    always: 'Khóa Slidebar',
    auto:   'Tự động ẩn Slidebar',
    hidden: 'Ẩn Slidebar',
  };

  /* ================== Hiệu ứng mở sidebar (trái -> phải) ================== */
  function addOpenAnim() {
    const el = document.querySelector('.fi-sidebar');
    if (!el) return;
    el.classList.remove('nf-anim-opening');
    void el.offsetWidth; // restart
    el.classList.add('nf-anim-opening');
    el.addEventListener('animationend', () => {
      el.classList.remove('nf-anim-opening');
    }, { once: true });
  }

  function openSidebarNow() {
    const s = store();
    if (s && !s.isOpen) {
      s.isOpen = true;
      lastOpenAt = performance.now();
      addOpenAnim();
    }
  }

  function closeSidebarSoon() {
    clearTimeout(closeTimer);
    closeTimer = setTimeout(() => {
      if (getMode() === 'auto' && !insideSidebar) {
        const s = store();
        if (s && s.isOpen) s.isOpen = false;
      }
    }, CLOSE_DELAY_MS);
  }

  function applyMode() {
    if (!isDesktop()) return;
    const s = store();
    if (!s) return;
    const m = getMode();
    if (m === 'always') s.isOpen = true;
    else if (m === 'hidden') s.isOpen = false;
    else s.isOpen = false; // auto
  }

  /* ================== Đặt vị trí: TRƯỚC user menu (bên trái profile) ================== */
  function placeLeftOfProfile(myGroup) {
    const userMenu = document.querySelector('.fi-user-menu, [data-fi-user-menu]');
    if (!myGroup) myGroup = document.querySelector('.nf-toolbar-group');
    if (userMenu && myGroup && userMenu.parentNode) {
      if (myGroup.nextElementSibling !== userMenu) {
        userMenu.parentNode.insertBefore(myGroup, userMenu);
      }
    }
  }

  /* ================== KHỬ TRÙNG LẶP toolbar (chỉ còn 1) ================== */
  function ensureSingleToolbar() {
    const groups = Array.from(document.querySelectorAll('.nf-toolbar-group'));
    if (groups.length <= 1) return groups[0] || null;

    // Giữ bản MỚI NHẤT (cuối danh sách), xóa các bản cũ
    const keep = groups[groups.length - 1];
    groups.forEach((el) => { if (el !== keep) el.remove(); });
    return keep;
  }

  function settleToolbar() {
    // chạy sau khi DOM đã render xong
    requestAnimationFrame(() => {
      const el = ensureSingleToolbar();
      placeLeftOfProfile(el);
    });
  }

  /* ================== Kích hoạt vùng mép trái + theo dõi pointer ================== */
  function onPointerMove(e) {
    if (!isDesktop() || getMode() !== 'auto') return;
    if (e.clientX <= OPEN_NEAR_PX) {
      if (performance.now() - lastOpenAt > 60) openSidebarNow();
      return;
    }
    if (!insideSidebar && e.clientX > CLOSE_FAR_PX) {
      closeSidebarSoon();
    }
  }

  function setupEdge() {
    if (!isDesktop()) return;

    let edge = document.getElementById('nf-sidebar-edge');
    if (!edge) {
      edge = document.createElement('div');
      edge.id = 'nf-sidebar-edge';
      edge.className = 'nf-sidebar-edge';
      edge.setAttribute('aria-hidden', 'true');
      document.body.appendChild(edge);
    }
    edge.onpointerenter = () => { if (getMode() === 'auto') openSidebarNow(); };

    const sidebarEl = document.querySelector('.fi-sidebar');
    if (sidebarEl) {
      sidebarEl.addEventListener('pointerenter', () => { insideSidebar = true; }, { passive: true });
      sidebarEl.addEventListener('pointerleave',  () => { insideSidebar = false; if (getMode() === 'auto') closeSidebarSoon(); }, { passive: true });
    }

    window.removeEventListener('pointermove', onPointerMove, true);
    window.addEventListener('pointermove', onPointerMove, { capture: true, passive: true });
  }

  /* ================== Alpine component của nút ================== */
  window.sidebarModeToggle = function () {
    return {
      mode: getMode(),
      get label() { return labels[this.mode] || ''; },
      init() {
        this.$nextTick(() => {
          applyMode();
          settleToolbar(); // đảm bảo 1 bản & đặt trái profile
        });
      },
      nextMode() {
        const i = MODES.indexOf(getMode());
        const next = MODES[(i + 1) % MODES.length];
        setMode(next);
        this.mode = next;
        applyMode();
      },
    };
  };

  /* ================== Lifecycle ================== */
  document.addEventListener('alpine:init',        () => { applyMode(); setupEdge(); settleToolbar(); });
  document.addEventListener('livewire:navigated', () => { applyMode(); setupEdge(); settleToolbar(); });
  document.addEventListener('DOMContentLoaded',   () => { applyMode(); setupEdge(); settleToolbar(); });
})();
