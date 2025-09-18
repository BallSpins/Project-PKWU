  function handleToggleAbsen() {
    const absen = document.querySelector('input[name="absen"]:checked');
    const alasanBox = document.getElementById('alasanBox');
    const sakitFile = document.getElementById('sakitFile');
    const absenCam = document.getElementById('absenCam');

    if (absen) {
      if (absen.value === 'sakit') {
        absenCam.classList.add('hidden');
        alasanBox.classList.add('hidden');
        sakitFile.classList.remove('hidden');
      } else if (absen.value === 'izin') {
        absenCam.classList.add('hidden');
        sakitFile.classList.add('hidden');
        alasanBox.classList.remove('hidden');
      } else if (absen.value === 'hadir') {
        sakitFile.classList.add('hidden');
        alasanBox.classList.add('hidden');
        absenCam.classList.remove('hidden');
      } else {
        absenCam.classList.add('hidden');
        sakitFile.classList.add('hidden');
        alasanBox.classList.add('hidden');
      }
    }
  }
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
const input = document.getElementById('cameraInput');
const canvas = document.getElementById('preview');
const ctx = canvas.getContext('2d');
const fotoData = document.getElementById('fotoData');

input.addEventListener('change', async (e) => {
  const file = e.target.files[0];
  if (!file) return;

  const img = new Image();
  img.src = URL.createObjectURL(file);

  img.onload = () => {
    // Atur ukuran canvas
    canvas.width = img.width;
    canvas.height = img.height;

    // Gambar foto
    ctx.drawImage(img, 0, 0);

    // Tambahin watermark jam aja
    ctx.fillStyle = "white";
    ctx.font = "30px Arial";
    ctx.fillText(new Date().toLocaleString(), 20, img.height - 20);

    // Simpan hasil ke hidden input (base64)
    fotoData.value = canvas.toDataURL("image/jpeg");
  };
});
