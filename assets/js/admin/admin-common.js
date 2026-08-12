/**
 * Shared admin chrome behavior — mobile sidebar toggle + logout.
 * Port of the sidebar-open state and logout() function in AdminLayout.jsx.
 */
(function () {
  const sidebar = document.getElementById('admSidebar');
  const overlay = document.getElementById('admOverlay');
  const menuBtn = document.getElementById('admMenuBtn');
  const logoutBtn = document.getElementById('admLogoutBtn');

  function setOpen(open) {
    sidebar.classList.toggle('open', open);
    overlay.classList.toggle('show', open);
  }

  if (menuBtn) menuBtn.addEventListener('click', () => setOpen(!sidebar.classList.contains('open')));
  if (overlay) overlay.addEventListener('click', () => setOpen(false));

  if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
      await fetch('/admin/logout.php', { method: 'POST', credentials: 'include' });
      window.location.href = '/admin/login.php';
    });
  }
})();
