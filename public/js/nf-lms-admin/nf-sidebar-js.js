// resources/js/filament/admin/sidebar.js
// Chế độ: 'always' | 'auto' | 'hidden'
(function () {
  const MODE_KEY = 'nf_sidebar_mode';
  const MODES = ['always', 'auto', 'hidden'];
  const OPEN_NEAR_PX = 8;          // chạm mép trái <= 8px sẽ mở
  const CLOSE_DELAY_MS = 120;       // đóng nhanh sau 120ms
  const CLOSE_FAR_PX = 220;         // nếu rời xa >220px và không ở trong sidebar => đóng
  let insideSidebar = false;
  let closeTimer = null;
  let lastOpenAt = 0;

  const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;
  const getMode = () => localStorage.getItem(MODE_KEY) || 'always';
  const setMode = (m) => localStorage.setItem(MODE_KEY, m);
  const store = () => window.Alpine?.store('sidebar');

  const labels = {
    always: 'Luôn hiện',
    auto: 'Tự động ẩn (đưa chuột sát mép trái)',
    hidden: 'Ẩn',
  };

  function openSidebarNow() {
    const s = store();
    if (s && !s.isOpen) {
      s.isOpen = true;
      lastOpenAt = performance.now();
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
    else s.isOpen = false; // auto: mặc định đóng, hover mép trái sẽ mở
  }

  function onPointerMove(e) {
    if (!isDesktop() || getMode() !== 'auto') return;

    // Mở ngay khi trỏ nằm sát mép trái
    if (e.clientX <= OPEN_NEAR_PX) {
      // chống rung: nếu vừa mở <60ms thì bỏ qua
      if (performance.now() - lastOpenAt > 60) openSidebarNow();
      return;
    }

    // Rời xa hoàn toàn (không ở trong sidebar) -> đóng nhanh
    if (!insideSidebar && e.clientX > CLOSE_FAR_PX) {
      closeSidebarSoon();
    }
  }

  function setupEdge() {
    if (!isDesktop()) return;

    // Tạo hoặc dùng lại vùng mép trái
    let edge = document.getElementById('nf-sidebar-edge');
    if (!edge) {
      edge = document.createElement('div');
      edge.id = 'nf-sidebar-edge';
      edge.className = 'nf-sidebar-edge';
      edge.setAttribute('aria-hidden', 'true');
      document.body.appendChild(edge);
    }

    // MỞ NGAY khi pointer chạm vùng mép trái
    edge.onpointerenter = () => {
      if (getMode() === 'auto') openSidebarNow();
    };

    // Theo dõi vào/ra trong chính sidebar để không đóng nhầm
    const sidebarEl = document.querySelector('.fi-sidebar');
    if (sidebarEl) {
      sidebarEl.addEventListener('pointerenter', () => { insideSidebar = true; }, { passive: true });
      sidebarEl.addEventListener('pointerleave', () => {
        insideSidebar = false;
        if (getMode() === 'auto') closeSidebarSoon();
      }, { passive: true });
    }

    // Fallback mở theo vị trí trỏ sát mép trái
    window.removeEventListener('pointermove', onPointerMove, true);
    window.addEventListener('pointermove', onPointerMove, { capture: true, passive: true });
  }

  // Alpine component cho NÚT ICON ở topbar (xoay vòng 3 chế độ)
  window.sidebarModeToggle = function () {
    return {
      mode: getMode(),
      showHint: false,
      get label() { return labels[this.mode] || ''; },
      init() { this.$nextTick(() => applyMode()); },
      nextMode() {
        const i = MODES.indexOf(getMode());
        const next = MODES[(i + 1) % MODES.length];
        setMode(next);
        this.mode = next;
        applyMode();
      },
    };
  };

  // Lifecycle
  document.addEventListener('alpine:init', () => { applyMode(); setupEdge(); });
  document.addEventListener('livewire:navigated', () => { applyMode(); setupEdge(); });
  document.addEventListener('DOMContentLoaded', () => { applyMode(); setupEdge(); });
})();
