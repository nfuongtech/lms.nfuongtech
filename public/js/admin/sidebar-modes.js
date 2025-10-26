/* Sidebar modes for Filament v3
   Modes:
   - 'always': Sidebar luôn mở
   - 'hidden': Sidebar luôn đóng
   - 'auto'  : Sidebar đóng, rê chuột vào mép trái (edge) sẽ mở; rời sidebar sẽ tự đóng

   Lưu mode vào localStorage('nf_sidebar_mode').
   Yêu cầu: Filament v3 có Alpine store 'sidebar' (isOpen). Docs show $store.sidebar.isOpen. */

(function () {
  const MODE_KEY = 'nf_sidebar_mode';
  const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

  function getMode() {
    return localStorage.getItem(MODE_KEY) || 'always';
  }

  function setMode(m) {
    localStorage.setItem(MODE_KEY, m);
  }

  function store() {
    return window.Alpine?.store('sidebar');
  }

  function applyMode() {
    if (!isDesktop()) return;
    const s = store();
    if (!s) return;

    const m = getMode();
    if (m === 'always') {
      s.isOpen = true;
    } else if (m === 'hidden') {
      s.isOpen = false;
    } else {
      // auto
      s.isOpen = false;
    }
  }

  function setupEdge() {
    if (!isDesktop()) return;

    // tạo edge trigger nếu chưa có
    let edge = document.getElementById('nf-sidebar-edge');
    if (!edge) {
      // nếu Blade chưa render, không tạo trùng. Thử tạo dự phòng:
      edge = document.createElement('div');
      edge.id = 'nf-sidebar-edge';
      edge.className = 'fixed inset-y-0 left-0 w-2 z-[60] hidden lg:block';
      edge.setAttribute('aria-hidden', 'true');
      document.body.appendChild(edge);
    }

    // Hover vào mép trái => mở (chỉ khi auto)
    edge.addEventListener('mouseenter', () => {
      if (getMode() !== 'auto') return;
      const s = store();
      if (s && !s.isOpen) s.isOpen = true;
    });

    // Rời khỏi sidebar => đóng nhẹ (chỉ khi auto)
    const sidebarEl = document.querySelector('.fi-sidebar');
    if (sidebarEl) {
      let timer;
      sidebarEl.addEventListener('mouseleave', () => {
        if (getMode() !== 'auto') return;
        const s = store();
        if (!s) return;
        clearTimeout(timer);
        timer = setTimeout(() => {
          s.isOpen = false;
        }, 250);
      });
    }
  }

  // Alpine lifecycle
  document.addEventListener('alpine:init', () => {
    applyMode();
    setupEdge();
  });

  // Livewire navigations (SPA)
  document.addEventListener('livewire:navigated', () => {
    applyMode();
    setupEdge();
  });

  // Global helper for Alpine component in blade
  window.sidebarModeToggle = function () {
    return {
      mode: getMode(),
      init() {
        // đồng bộ khi vào trang
        this.$nextTick(() => applyMode());
      },
      applyMode() {
        setMode(this.mode);
        applyMode();
      },
    };
  };

  // Fallback on initial DOM load
  document.addEventListener('DOMContentLoaded', () => {
    applyMode();
    setupEdge();
  });
})();
