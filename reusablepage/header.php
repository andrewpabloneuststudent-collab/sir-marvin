<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">

    <!-- ☰ Sidebar Toggle (Mobile) -->
    <button class="btn btn-outline-success d-lg-none me-2" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        ☰
    </button>

    <!-- 🏥 Logo / System Name -->
    <a class="navbar-brand fw-bold text-success" href="#">
        MMB'S DRUGSTORE
    </a>

    <!-- Right Side -->
    <div class="ms-auto d-flex align-items-center gap-2">

        <!-- 👤 User Dropdown -->
        <div class="dropdown">
            <button type="button" class="btn btn-success dropdown-toggle" id="userDropdownBtn" onclick="toggleDropdown()">
                <?php echo htmlspecialchars($_SESSION['position']); ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg" id="userDropdownMenu" style="display: none; width: 20%; border-radius: 8px; border: none;">
                <li><a class="dropdown-item text-danger d-flex align-items-center gap-2" href="../login_logout_page/logout.php" style="padding: 12px 16px 12px; font-size: 15px; font-weight: 500;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <script>
            function toggleDropdown() {
                const menu = document.getElementById('userDropdownMenu');
                menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const btn = document.getElementById('userDropdownBtn');
                const menu = document.getElementById('userDropdownMenu');
                if (!btn.contains(event.target) && !menu.contains(event.target)) {
                    menu.style.display = 'none';
                }
            });
        </script>

    </div>
</nav>