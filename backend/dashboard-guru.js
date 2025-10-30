// Fungsi untuk toggle dropdown
  function toggleDropdown() {
  const menu = document.getElementById('dropdownMenu');
  menu.classList.toggle('hidden');
  }

  // Tutup dropdown kalau klik di luar
  window.addEventListener('click', function (e) {
    const dropdown = document.getElementById('dropdownMenu');
    const button = e.target.closest('button');

    if (!dropdown.contains(e.target) && !button) {
      dropdown.classList.add('hidden');
    }
  });