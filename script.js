// Sidebar navigation
const sidebarLinks = document.querySelectorAll('#sidebar a');
sidebarLinks.forEach(link => {
  link.addEventListener('click', function () {
    sidebarLinks.forEach(link => link.classList.remove('active'));
    this.classList.add('active');
  });
});

// Account dropdown toggle
const account = document.querySelector('.user-controls .account');
const accountMenu = document.querySelector('.user-controls .account-menu');
account.addEventListener('click', () => {
  accountMenu.style.display = accountMenu.style.display === 'block' ? 'none' : 'block';
});

// Search functionality
const searchInput = document.querySelector('.search-bar input');
searchInput.addEventListener('input', function () {
  console.log(`Searching for: ${this.value}`);
});

// Recruitment status change
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('status-pending')) {
    const row = e.target.closest('tr');
    const statusCell = row.querySelector('.status');
    statusCell.innerHTML = '<span class="status-approved">Approved</span>';
    statusCell.querySelector('.status-approved').style.color = 'var(--success)';
  }
});


