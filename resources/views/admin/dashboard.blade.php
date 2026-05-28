<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CivicPulse</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS v4 Browser CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }
        .glass {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.3);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.5);
        }
    </style>
</head>
<body class="h-full flex overflow-hidden text-slate-100 bg-slate-950">
    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col transition-all duration-300 z-30 shrink-0">
        <!-- Sidebar Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-heart-pulse text-white text-base"></i>
                </div>
                <span class="text-lg font-bold tracking-tight text-white sidebar-text">Civic<span class="text-blue-400">Pulse</span></span>
            </div>
            <button id="toggle-sidebar" class="text-slate-400 hover:text-white transition-colors cursor-pointer md:block hidden">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto custom-scrollbar">
            <button onclick="switchTab('overview')" id="tab-overview" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all font-medium text-sm text-left group bg-blue-600 text-white cursor-pointer">
                <i class="fa-solid fa-chart-pie text-base shrink-0 group-hover:scale-110 transition-transform"></i>
                <span class="sidebar-text">Ringkasan Sistem</span>
            </button>

            <button onclick="switchTab('users')" id="tab-users" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all font-medium text-sm text-left group text-slate-400 hover:bg-slate-800 hover:text-white cursor-pointer">
                <i class="fa-solid fa-user-group text-base shrink-0 group-hover:scale-110 transition-transform"></i>
                <span class="sidebar-text">Manajemen Pengguna</span>
            </button>

            <button onclick="switchTab('content')" id="tab-content" class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all font-medium text-sm text-left group text-slate-400 hover:bg-slate-800 hover:text-white cursor-pointer">
                <i class="fa-solid fa-book-open text-base shrink-0 group-hover:scale-110 transition-transform"></i>
                <span class="sidebar-text">Manajemen Konten</span>
            </button>
        </nav>

        <!-- Sidebar User Profile & Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/50">
            <div class="flex items-center space-x-3 p-2 rounded-xl mb-3">
                <div id="admin-avatar" class="w-9 h-9 rounded-full bg-blue-600/10 border border-blue-500/20 flex items-center justify-center font-bold text-blue-400">
                    A
                </div>
                <div class="sidebar-text overflow-hidden">
                    <p id="admin-name" class="text-sm font-semibold text-white truncate">Administrator</p>
                    <p id="admin-role" class="text-xs text-slate-400">System Admin</p>
                </div>
            </div>
            <button onclick="logout()" class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 bg-slate-800 hover:bg-red-950 hover:text-red-300 border border-slate-700/60 rounded-xl text-sm font-medium text-slate-300 transition-all cursor-pointer">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
                <span class="sidebar-text">Keluar</span>
            </button>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden bg-slate-950">
        <!-- Top Navbar -->
        <header class="h-16 border-b border-slate-800 flex items-center justify-between px-6 bg-slate-900/40 backdrop-blur shrink-0">
            <div class="flex items-center space-x-4">
                <button id="toggle-mobile-sidebar" class="text-slate-400 hover:text-white md:hidden cursor-pointer">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h2 id="page-title" class="text-lg font-bold text-white tracking-wide">Ringkasan Sistem</h2>
            </div>
            <!-- Notification & Quick Actions -->
            <div class="flex items-center space-x-3">
                <a href="/api/v1/docs" target="_blank" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-700/50 rounded-xl text-xs font-semibold text-blue-400 flex items-center space-x-1.5 transition-colors">
                    <i class="fa-solid fa-code"></i>
                    <span>Dokumentasi API</span>
                </a>
            </div>
        </header>

        <!-- Main Display Pane -->
        <main class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-[radial-gradient(ellipse_at_bottom_left,_var(--tw-gradient-stops))] from-blue-950/10 via-slate-950 to-slate-950">
            <!-- Alert Notification Toast Wrapper -->
            <div id="toast" class="fixed top-6 right-6 z-50 transform translate-x-full opacity-0 transition-all duration-300 p-4 rounded-xl flex items-center space-x-3 shadow-xl"></div>

            <!-- Tab: System Overview (Stats Dashboard) -->
            <section id="tab-pane-overview" class="tab-pane space-y-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Stat Card 1 -->
                    <div class="glass p-6 rounded-2xl flex items-center justify-between shadow-lg relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-blue-500/5 group-hover:scale-110 transition-transform duration-300">
                            <i class="fa-solid fa-user-tie text-8xl"></i>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Guru</p>
                            <h3 id="stat-teachers" class="text-3xl font-extrabold text-white">0</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-user-tie text-xl"></i>
                        </div>
                    </div>
                    <!-- Stat Card 2 -->
                    <div class="glass p-6 rounded-2xl flex items-center justify-between shadow-lg relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-emerald-500/5 group-hover:scale-110 transition-transform duration-300">
                            <i class="fa-solid fa-user-graduate text-8xl"></i>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Siswa Aktif</p>
                            <h3 id="stat-students-active" class="text-3xl font-extrabold text-white">0</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <i class="fa-solid fa-user-graduate text-xl"></i>
                        </div>
                    </div>
                    <!-- Stat Card 3 -->
                    <div class="glass p-6 rounded-2xl flex items-center justify-between shadow-lg relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-amber-500/5 group-hover:scale-110 transition-transform duration-300">
                            <i class="fa-solid fa-user-lock text-8xl"></i>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Siswa Nonaktif</p>
                            <h3 id="stat-students-inactive" class="text-3xl font-extrabold text-white">0</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                            <i class="fa-solid fa-user-lock text-xl"></i>
                        </div>
                    </div>
                    <!-- Stat Card 4 -->
                    <div class="glass p-6 rounded-2xl flex items-center justify-between shadow-lg relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-purple-500/5 group-hover:scale-110 transition-transform duration-300">
                            <i class="fa-solid fa-file-pdf text-8xl"></i>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Materi</p>
                            <h3 id="stat-materials" class="text-3xl font-extrabold text-white">0</h3>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                            <i class="fa-solid fa-file-pdf text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="glass p-6 rounded-2xl lg:col-span-2 space-y-4">
                        <h4 class="text-lg font-bold text-white flex items-center space-x-2">
                            <i class="fa-solid fa-circle-info text-blue-400"></i>
                            <span>Panduan Administrator</span>
                        </h4>
                        <div class="text-slate-300 text-sm space-y-3 leading-relaxed">
                            <p>Sebagai Administrator CivicPulse, Anda memiliki wewenang operasional penuh:</p>
                            <ul class="list-disc pl-5 space-y-2 text-slate-400">
                                <li><strong>Manajemen Akun</strong>: Menambahkan guru baru, mengelola status siswa, mengatur/mereset kata sandi jika lupa, serta memverifikasi email pengguna secara manual.</li>
                                <li><strong>Manajemen Konten E-Learning</strong>: Menyusun modul berdasarkan tingkatan kelas (7-12) dan melengkapinya dengan kuis (Pre/Post-Test) serta instrumen asesmen karakter (PULSE).</li>
                                <li><strong>Keamanan Sistem</strong>: Memonitor jumlah aktivitas pengguna terdaftar di platform untuk menjaga integrasi database CivicPulse.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="glass p-6 rounded-2xl space-y-4 flex flex-col justify-between">
                        <div>
                            <h4 class="text-lg font-bold text-white mb-2">Pintasan Aksi</h4>
                            <p class="text-xs text-slate-400 leading-relaxed mb-4">Mulai kelola sistem dengan aksi cepat di bawah ini.</p>
                        </div>
                        <div class="space-y-3">
                            <button onclick="switchTab('users'); openAddUserModal();" class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-xl transition-colors cursor-pointer flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-user-plus"></i>
                                <span>Tambah Pengguna Baru</span>
                            </button>
                            <button onclick="switchTab('content')" class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 border border-slate-700/60 text-slate-200 font-semibold text-sm rounded-xl transition-colors cursor-pointer flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Unggah Materi Baru</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tab: User Management -->
            <section id="tab-pane-users" class="tab-pane hidden space-y-6">
                <!-- Filters & Actions Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3 flex-1 max-w-2xl">
                        <!-- Search Bar -->
                        <div class="relative flex-1 min-w-[200px]">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                                <i class="fa-solid fa-magnifying-glass text-sm"></i>
                            </span>
                            <input type="text" id="user-search-input" placeholder="Cari nama, email, telepon..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <!-- Role Filter -->
                        <select id="user-filter-role" class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Semua Peran</option>
                            <option value="admin">Administrator</option>
                            <option value="teacher">Guru</option>
                            <option value="student">Siswa</option>
                        </select>
                        <!-- Status Filter -->
                        <select id="user-filter-status" class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-sm text-slate-300 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                            <option value="locked">Terkunci</option>
                        </select>
                    </div>
                    <button onclick="openAddUserModal()" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-blue-600/15 flex items-center justify-center space-x-2 shrink-0 cursor-pointer">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Tambah Akun</span>
                    </button>
                </div>

                <!-- Users Table Card -->
                <div class="glass rounded-2xl overflow-hidden shadow-lg border border-slate-800">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-900/60 text-slate-400 border-b border-slate-800">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">Pengguna</th>
                                    <th class="px-6 py-4 font-semibold">Peran</th>
                                    <th class="px-6 py-4 font-semibold">Kontak</th>
                                    <th class="px-6 py-4 font-semibold">Status Email</th>
                                    <th class="px-6 py-4 font-semibold">Status Akun</th>
                                    <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body" class="divide-y divide-slate-800/50">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Footer -->
                    <div class="px-6 py-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400 bg-slate-900/20">
                        <span id="users-table-info">Menampilkan 0 dari 0 data</span>
                        <div class="flex space-x-2">
                            <button id="users-prev-btn" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 disabled:opacity-50 disabled:hover:bg-slate-800 rounded-lg font-medium cursor-pointer">Sebelumnya</button>
                            <button id="users-next-btn" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 disabled:opacity-50 disabled:hover:bg-slate-800 rounded-lg font-medium cursor-pointer">Berikutnya</button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tab: Content Management (CMS) -->
            <section id="tab-pane-content" class="tab-pane hidden space-y-6">
                <!-- Grade Select & Add Material Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <span class="text-sm font-medium text-slate-400">Tingkat Kelas:</span>
                        <div id="grade-selection-tabs" class="flex p-1 bg-slate-900 border border-slate-800 rounded-xl space-x-1">
                            <button onclick="selectGrade(7)" id="grade-tab-7" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-blue-600 text-white cursor-pointer">Kelas 7</button>
                            <button onclick="selectGrade(8)" id="grade-tab-8" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-400 hover:text-white cursor-pointer">Kelas 8</button>
                            <button onclick="selectGrade(9)" id="grade-tab-9" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-400 hover:text-white cursor-pointer">Kelas 9</button>
                            <button onclick="selectGrade(10)" id="grade-tab-10" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-400 hover:text-white cursor-pointer">Kelas 10</button>
                            <button onclick="selectGrade(11)" id="grade-tab-11" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-400 hover:text-white cursor-pointer">Kelas 11</button>
                            <button onclick="selectGrade(12)" id="grade-tab-12" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-400 hover:text-white cursor-pointer">Kelas 12</button>
                        </div>
                    </div>
                    <button onclick="openAddMaterialModal()" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-xl flex items-center justify-center space-x-2 cursor-pointer shadow-lg shadow-blue-600/10">
                        <i class="fa-solid fa-plus"></i>
                        <span>Unggah Materi</span>
                    </button>
                </div>

                <!-- Materials List Grid -->
                <div id="materials-list-container" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Loaded dynamically -->
                </div>
            </section>
        </main>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="user-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl border border-slate-800 flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 id="user-modal-title" class="text-lg font-bold text-white">Tambah Akun Baru</h3>
                <button onclick="closeUserModal()" class="text-slate-400 hover:text-white transition-colors cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form id="user-form" class="overflow-y-auto p-6 space-y-4 custom-scrollbar flex-1">
                <input type="hidden" id="user-id-input">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Nama Lengkap</label>
                        <input type="text" id="user-name" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Email</label>
                        <input type="email" id="user-email" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div id="user-password-container">
                        <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Kata Sandi</label>
                        <input type="password" id="user-password" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Peran (Role)</label>
                        <select id="user-role" required onchange="handleRoleChange()" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="student">Siswa</option>
                            <option value="teacher">Guru</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">No. Telepon</label>
                        <input type="text" id="user-phone" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Tanggal Lahir</label>
                        <input type="date" id="user-dob" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Jenis Kelamin</label>
                        <select id="user-gender" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Pilih Gender</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Status</label>
                        <select id="user-status" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                            <option value="locked">Terkunci</option>
                        </select>
                    </div>
                </div>

                <!-- Address (Full row) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Alamat</label>
                    <textarea id="user-address" rows="2" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <!-- Student specific fields -->
                <div id="student-only-fields" class="border-t border-slate-800 pt-4 space-y-4">
                    <h4 class="text-sm font-bold text-white">Informasi Orang Tua (Khusus Siswa)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Nama Orang Tua</label>
                            <input type="text" id="user-parent-name" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">No. Telepon Orang Tua</label>
                            <input type="text" id="user-parent-phone" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Modal footer inside form -->
                <div class="border-t border-slate-800 pt-4 flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeUserModal()" class="py-2.5 px-4 bg-slate-850 hover:bg-slate-800 border border-slate-700/60 rounded-xl text-sm font-semibold text-slate-300 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-xl shadow-lg shadow-blue-500/10 cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Learning Material Modal -->
    <div id="material-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl border border-slate-800">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 id="material-modal-title" class="text-lg font-bold text-white">Unggah Materi E-Learning</h3>
                <button onclick="closeMaterialModal()" class="text-slate-400 hover:text-white transition-colors cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form id="material-form" class="p-6 space-y-4">
                <input type="hidden" id="material-id-input">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Judul Materi</label>
                    <input type="text" id="material-title" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Deskripsi</label>
                    <textarea id="material-description" required rows="3" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Grade (Tingkatan Kelas)</label>
                    <select id="material-grade" required class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="7">Kelas 7</option>
                        <option value="8">Kelas 8</option>
                        <option value="9">Kelas 9</option>
                        <option value="10">Kelas 10</option>
                        <option value="11">Kelas 11</option>
                        <option value="12">Kelas 12</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">File PDF Materi (Maksimal 50 MB)</label>
                    <input type="file" id="material-file" accept="application/pdf" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Harap pilih berkas PDF untuk diunggah.</p>
                </div>
                <div class="border-t border-slate-800 pt-4 flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeMaterialModal()" class="py-2.5 px-4 bg-slate-850 hover:bg-slate-800 border border-slate-700/60 text-slate-300 font-semibold text-sm rounded-xl cursor-pointer">Batal</button>
                    <button type="submit" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-sm rounded-xl cursor-pointer shadow-lg shadow-blue-500/10">Unggah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage Questions / Instruments Builder Modal -->
    <div id="builder-modal" class="hidden fixed inset-0 z-40 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-4xl rounded-3xl overflow-hidden shadow-2xl border border-slate-800 flex flex-col h-[85vh]">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between shrink-0">
                <div>
                    <h3 id="builder-modal-title" class="text-lg font-bold text-white">Penyusun Evaluasi Pembelajaran</h3>
                    <p id="builder-modal-subtitle" class="text-xs text-slate-400 mt-1">Materi: Toleransi Antar Umat Beragama</p>
                </div>
                <button onclick="closeBuilderModal()" class="text-slate-400 hover:text-white transition-colors cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <!-- Builder Layout Tab Header -->
            <div class="flex border-b border-slate-800 bg-slate-900/30 px-6 shrink-0">
                <button onclick="switchBuilderSubTab('pre-test')" id="subtab-pre-test" class="px-4 py-3 border-b-2 border-blue-500 text-blue-400 font-semibold text-sm cursor-pointer">Pre-Test</button>
                <button onclick="switchBuilderSubTab('post-test')" id="subtab-post-test" class="px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-white font-semibold text-sm cursor-pointer">Post-Test</button>
                <button onclick="switchBuilderSubTab('pulse')" id="subtab-pulse" class="px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-white font-semibold text-sm cursor-pointer">Instrumen PULSE</button>
            </div>

            <!-- Scrollable Content Builder Area -->
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">
                <!-- Add items button -->
                <div class="flex justify-between items-center bg-slate-900/40 p-4 rounded-2xl border border-slate-800/80">
                    <div>
                        <h4 id="builder-content-title" class="text-sm font-bold text-white">Soal Pre-Test</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Pertanyaan pilihan ganda untuk mengevaluasi kemampuan awal kognitif siswa.</p>
                    </div>
                    <div class="flex items-center space-x-3 shrink-0">
                        <a id="download-template-link" href="/templates/pre_test_template.xlsx" download class="py-2 px-3.5 bg-slate-800 hover:bg-slate-700 border border-slate-750 rounded-xl text-xs font-semibold text-slate-300 flex items-center space-x-1.5 transition-colors">
                            <i class="fa-solid fa-file-excel text-emerald-400"></i>
                            <span>Unduh Template Excel</span>
                        </a>
                        <button onclick="triggerExcelImport()" class="py-2 px-3.5 bg-slate-800 hover:bg-slate-700 border border-slate-750 rounded-xl text-xs font-semibold text-slate-300 flex items-center space-x-1.5 transition-colors cursor-pointer">
                            <i class="fa-solid fa-file-import text-blue-400"></i>
                            <span>Impor Excel</span>
                        </button>
                        <input type="file" id="excel-import-file" accept=".xlsx" class="hidden" onchange="handleExcelImport(event)">
                        <button id="add-builder-item-btn" class="py-2 px-3.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl flex items-center justify-center space-x-1.5 transition-colors cursor-pointer shadow-lg shadow-blue-500/10">
                            <i class="fa-solid fa-circle-plus"></i>
                            <span>Tambah Butir</span>
                        </button>
                    </div>
                </div>

                <!-- Dynamic Item Builder Form (Hidden by default, shown when adding/editing) -->
                <form id="builder-form" class="hidden glass p-5 rounded-2xl border border-slate-700/50 space-y-4">
                    <input type="hidden" id="builder-item-id">
                    <div id="form-question-block" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Pernyataan Soal</label>
                            <input type="text" id="builder-question-text" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Opsi A</label>
                                <input type="text" id="builder-opt-a" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Opsi B</label>
                                <input type="text" id="builder-opt-b" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Opsi C</label>
                                <input type="text" id="builder-opt-c" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Opsi D</label>
                                <input type="text" id="builder-opt-d" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Kunci Jawaban Benar</label>
                            <select id="builder-correct-answer" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">Pilih Jawaban Benar</option>
                                <option value="A">Opsi A</option>
                                <option value="B">Opsi B</option>
                                <option value="C">Opsi C</option>
                                <option value="D">Opsi D</option>
                            </select>
                        </div>
                    </div>

                    <div id="form-pulse-block" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Pernyataan Sikap (Skala Likert 1-5)</label>
                            <input type="text" id="builder-statement" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Dimensi Karakter</label>
                            <select id="builder-dimension" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="P">P - Participation</option>
                                <option value="U">U - Understanding</option>
                                <option value="L">L - Learning</option>
                                <option value="SE">SE - Social Engagement</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="button" onclick="hideBuilderForm()" class="py-1.5 px-3 border border-slate-700/60 rounded-xl text-xs font-medium text-slate-300 hover:bg-slate-800 cursor-pointer">Batal</button>
                        <button type="submit" class="py-1.5 px-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl shadow shadow-blue-500/10 cursor-pointer">Simpan Butir</button>
                    </div>
                </form>

                <!-- List of items currently in builder -->
                <div id="builder-items-list" class="space-y-4">
                    <!-- Loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="password-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-sm rounded-3xl overflow-hidden shadow-2xl border border-slate-800">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Ubah Password Pengguna</h3>
                <button onclick="closePasswordModal()" class="text-slate-400 hover:text-white transition-colors cursor-pointer"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            <form id="password-form" class="p-6 space-y-4">
                <input type="hidden" id="password-user-id">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Password Baru</label>
                    <input type="password" id="new-password" required minlength="8" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closePasswordModal()" class="py-2 px-4 bg-slate-850 hover:bg-slate-800 text-slate-300 font-semibold text-xs rounded-xl cursor-pointer">Batal</button>
                    <button type="submit" class="py-2 px-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl cursor-pointer shadow-lg shadow-blue-500/10">Ubah</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // CLIENT-SIDE AUTH GUARD
        const token = localStorage.getItem('admin_token');
        if (!token) {
            window.location.href = '/admin/login';
        }

        const adminUser = JSON.parse(localStorage.getItem('admin_user') || '{}');
        document.getElementById('admin-name').textContent = adminUser.name || 'Admin';
        document.getElementById('admin-avatar').textContent = (adminUser.name || 'A')[0].toUpperCase();

        // GLOBALS
        let currentTab = 'overview';
        let currentGrade = 7;
        let builderSubTab = 'pre-test'; // 'pre-test', 'post-test', 'pulse'
        let selectedMaterialIdForBuilder = null;

        // PAGINATED USERS STATE
        let usersPage = 1;
        let usersTotalPages = 1;
        let userSearch = '';
        let userFilterRole = '';
        let userFilterStatus = '';

        // RUN ON LOAD
        window.addEventListener('DOMContentLoaded', () => {
            fetchStats();
            setupSidebarToggle();
            setupFilters();
        });

        // TOAST NOTIFICATIONS
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            
            // Set styles based on type
            if (type === 'success') {
                toast.className = 'fixed top-6 right-6 z-50 p-4 rounded-xl flex items-center space-x-3 shadow-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold transform translate-x-0 opacity-100 transition-all duration-300';
            } else {
                toast.className = 'fixed top-6 right-6 z-50 p-4 rounded-xl flex items-center space-x-3 shadow-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-semibold transform translate-x-0 opacity-100 transition-all duration-300';
            }

            // Hide after 3 seconds
            setTimeout(() => {
                toast.className = 'fixed top-6 right-6 z-50 p-4 rounded-xl flex items-center space-x-3 shadow-xl transform translate-x-full opacity-0 transition-all duration-300';
            }, 3000);
        }

        // SIDEBAR TOGGLING
        function setupSidebarToggle() {
            const sidebar = document.getElementById('sidebar');
            const toggleSidebar = document.getElementById('toggle-sidebar');
            const toggleMobileSidebar = document.getElementById('toggle-mobile-sidebar');

            toggleSidebar.addEventListener('click', () => {
                sidebar.classList.toggle('w-64');
                sidebar.classList.toggle('w-20');
                document.querySelectorAll('.sidebar-text').forEach(el => {
                    el.classList.toggle('hidden');
                });
            });

            toggleMobileSidebar.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebar.classList.toggle('translate-x-0');
            });
        }

        // SWITCH TAB
        function switchTab(tab) {
            currentTab = tab;
            
            // Toggle side buttons classes
            document.querySelectorAll('aside nav button').forEach(btn => {
                btn.className = "w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all font-medium text-sm text-left group text-slate-400 hover:bg-slate-800 hover:text-white cursor-pointer";
            });
            document.getElementById(`tab-${tab}`).className = "w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all font-medium text-sm text-left group bg-blue-600 text-white cursor-pointer";

            // Hide and show panes
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.add('hidden'));
            document.getElementById(`tab-pane-${tab}`).classList.remove('hidden');

            // Update title
            const titles = {
                overview: 'Ringkasan Sistem',
                users: 'Manajemen Pengguna',
                content: 'Manajemen Konten E-Learning'
            };
            document.getElementById('page-title').textContent = titles[tab];

            // Refresh data
            if (tab === 'overview') fetchStats();
            if (tab === 'users') fetchUsers();
            if (tab === 'content') fetchMaterials();
        }

        // LOGOUT
        async function logout() {
            try {
                await fetch('/api/v1/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
            } catch (err) {}
            localStorage.clear();
            window.location.href = '/admin/login';
        }

        // API CALLS: STATS
        async function fetchStats() {
            try {
                const res = await fetch('/api/v1/admin/dashboard/stats', {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('stat-teachers').textContent = data.data.total_teachers;
                    document.getElementById('stat-students-active').textContent = data.data.total_students_active;
                    document.getElementById('stat-students-inactive').textContent = data.data.total_students_inactive;
                    document.getElementById('stat-materials').textContent = data.data.total_materials;
                }
            } catch (err) {
                showToast('Gagal memuat statistik dasbor', 'danger');
            }
        }

        // USER MANAGEMENT
        function setupFilters() {
            // Search
            document.getElementById('user-search-input').addEventListener('input', (e) => {
                userSearch = e.target.value;
                usersPage = 1;
                fetchUsers();
            });
            // Role Filter
            document.getElementById('user-filter-role').addEventListener('change', (e) => {
                userFilterRole = e.target.value;
                usersPage = 1;
                fetchUsers();
            });
            // Status Filter
            document.getElementById('user-filter-status').addEventListener('change', (e) => {
                userFilterStatus = e.target.value;
                usersPage = 1;
                fetchUsers();
            });

            // Pagination button listeners
            document.getElementById('users-prev-btn').addEventListener('click', () => {
                if (usersPage > 1) {
                    usersPage--;
                    fetchUsers();
                }
            });
            document.getElementById('users-next-btn').addEventListener('click', () => {
                if (usersPage < usersTotalPages) {
                    usersPage++;
                    fetchUsers();
                }
            });
        }

        async function fetchUsers() {
            try {
                let url = `/api/v1/admin/users?page=${usersPage}&per_page=10&search=${encodeURIComponent(userSearch)}`;
                if (userFilterRole) url += `&filter[role]=${userFilterRole}`;
                if (userFilterStatus) url += `&filter[status]=${userFilterStatus}`;

                const res = await fetch(url, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                
                if (data.success) {
                    renderUsersTable(data.data);
                    usersTotalPages = data.meta.last_page;
                    
                    // Update buttons
                    document.getElementById('users-prev-btn').disabled = (usersPage === 1);
                    document.getElementById('users-next-btn').disabled = (usersPage === usersTotalPages || usersTotalPages === 0);

                    // Update info text
                    document.getElementById('users-table-info').textContent = `Menampilkan ${data.data.length} dari ${data.meta.total} data`;
                }
            } catch (err) {
                showToast('Gagal memuat daftar pengguna', 'danger');
            }
        }

        function renderUsersTable(usersList) {
            const tbody = document.getElementById('users-table-body');
            tbody.innerHTML = '';

            if (usersList.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-slate-500">Tidak ada pengguna ditemukan.</td></tr>`;
                return;
            }

            usersList.forEach(user => {
                const tr = document.createElement('tr');
                tr.className = "hover:bg-slate-900/20 transition-all";

                const roleBadgeColor = {
                    admin: 'bg-red-500/10 border-red-500/20 text-red-400',
                    teacher: 'bg-blue-500/10 border-blue-500/20 text-blue-400',
                    student: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400'
                }[user.role] || 'bg-slate-800 text-slate-400';

                const statusColor = {
                    active: 'bg-emerald-500/10 text-emerald-400',
                    inactive: 'bg-slate-800 text-slate-400',
                    locked: 'bg-red-500/10 text-red-400'
                }[user.status] || 'bg-slate-800 text-slate-400';

                const emailVerifiedBadge = user.email_verified_at 
                    ? `<span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded-md border border-emerald-500/20 text-xs flex items-center space-x-1 w-max"><i class="fa-solid fa-circle-check text-[10px]"></i> <span>Terverifikasi</span></span>`
                    : `<button onclick="verifyEmail(${user.id})" class="px-2 py-0.5 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 border border-amber-500/20 rounded-md text-xs cursor-pointer flex items-center space-x-1 w-max"><i class="fa-solid fa-envelope-open text-[10px]"></i> <span>Verifikasi</span></button>`;

                tr.innerHTML = `
                    <td class="px-6 py-4 flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-full bg-slate-800/80 flex items-center justify-center font-bold text-slate-300">
                            ${(user.name || 'U')[0].toUpperCase()}
                        </div>
                        <div>
                            <p class="font-semibold text-white">${user.name}</p>
                            <p class="text-xs text-slate-400">${user.email}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 border text-xs font-semibold rounded-lg ${roleBadgeColor}">${user.role.toUpperCase()}</span>
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-300">
                        <p>${user.phone || '-'}</p>
                        <p class="text-slate-400 truncate max-w-[150px]">${user.address || '-'}</p>
                    </td>
                    <td class="px-6 py-4">${emailVerifiedBadge}</td>
                    <td class="px-6 py-4">
                        <select onchange="updateUserStatus(${user.id}, this.value)" class="bg-slate-900 border border-slate-800 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer ${statusColor}">
                            <option value="active" ${user.status === 'active' ? 'selected' : ''}>Aktif</option>
                            <option value="inactive" ${user.status === 'inactive' ? 'selected' : ''}>Nonaktif</option>
                            <option value="locked" ${user.status === 'locked' ? 'selected' : ''}>Terkunci</option>
                        </select>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                        <button onclick="openChangePasswordModal(${user.id})" title="Ubah Password" class="text-slate-400 hover:text-blue-400 cursor-pointer transition-colors text-base"><i class="fa-solid fa-key"></i></button>
                        <button onclick="editUser(${JSON.stringify(user).replace(/"/g, '&quot;')})" title="Edit Profil" class="text-slate-400 hover:text-white cursor-pointer transition-colors text-base"><i class="fa-regular fa-pen-to-square"></i></button>
                        <button onclick="deleteUser(${user.id})" title="Hapus Pengguna" class="text-slate-400 hover:text-red-400 cursor-pointer transition-colors text-base"><i class="fa-regular fa-trash-can"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // ADD / EDIT USER MODAL CONTROL
        function openAddUserModal() {
            document.getElementById('user-modal-title').textContent = 'Tambah Akun Baru';
            document.getElementById('user-id-input').value = '';
            document.getElementById('user-form').reset();
            document.getElementById('user-password-container').classList.remove('hidden');
            document.getElementById('user-password').required = true;
            handleRoleChange();
            document.getElementById('user-modal').classList.remove('hidden');
        }

        function closeUserModal() {
            document.getElementById('user-modal').classList.add('hidden');
        }

        function editUser(user) {
            document.getElementById('user-modal-title').textContent = 'Edit Profil Pengguna';
            document.getElementById('user-id-input').value = user.id;
            
            document.getElementById('user-name').value = user.name || '';
            document.getElementById('user-email').value = user.email || '';
            document.getElementById('user-password-container').classList.add('hidden');
            document.getElementById('user-password').required = false;
            document.getElementById('user-role').value = user.role;
            document.getElementById('user-phone').value = user.phone || '';
            document.getElementById('user-dob').value = user.date_of_birth || '';
            document.getElementById('user-gender').value = user.gender || '';
            document.getElementById('user-status').value = user.status || 'active';
            document.getElementById('user-address').value = user.address || '';
            
            document.getElementById('user-parent-name').value = user.parent_name || '';
            document.getElementById('user-parent-phone').value = user.parent_phone || '';

            handleRoleChange();
            document.getElementById('user-modal').classList.remove('hidden');
        }

        function handleRoleChange() {
            const role = document.getElementById('user-role').value;
            const studentFields = document.getElementById('student-only-fields');
            if (role === 'student') {
                studentFields.classList.remove('hidden');
            } else {
                studentFields.classList.add('hidden');
            }
        }

        // SAVE USER
        document.getElementById('user-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('user-id-input').value;
            
            const payload = {
                name: document.getElementById('user-name').value,
                email: document.getElementById('user-email').value,
                role: document.getElementById('user-role').value,
                phone: document.getElementById('user-phone').value || null,
                date_of_birth: document.getElementById('user-dob').value || null,
                gender: document.getElementById('user-gender').value || null,
                status: document.getElementById('user-status').value || 'active',
                address: document.getElementById('user-address').value || null
            };

            if (payload.role === 'student') {
                payload.parent_name = document.getElementById('user-parent-name').value || null;
                payload.parent_phone = document.getElementById('user-parent-phone').value || null;
            }

            const method = id ? 'PATCH' : 'POST';
            const url = id ? `/api/v1/admin/users/${id}` : '/api/v1/admin/users';

            if (!id) {
                payload.password = document.getElementById('user-password').value;
            }

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                
                if (data.success) {
                    showToast(id ? 'Pengguna berhasil diperbarui' : 'Pengguna berhasil ditambahkan', 'success');
                    closeUserModal();
                    fetchUsers();
                } else {
                    showToast(data.message || 'Gagal menyimpan data', 'danger');
                }
            } catch (err) {
                showToast('Terjadi kesalahan jaringan', 'danger');
            }
        });

        // VERIFY EMAIL
        async function verifyEmail(id) {
            try {
                const res = await fetch(`/api/v1/admin/users/${id}/verify-email`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Email berhasil diverifikasi', 'success');
                    fetchUsers();
                }
            } catch (err) {
                showToast('Gagal memverifikasi email', 'danger');
            }
        }

        // TOGGLE STATUS
        async function updateUserStatus(id, newStatus) {
            try {
                const res = await fetch(`/api/v1/admin/users/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Status berhasil diperbarui', 'success');
                    fetchUsers();
                }
            } catch (err) {
                showToast('Gagal memperbarui status', 'danger');
            }
        }

        // CHANGE PASSWORD MODAL
        function openChangePasswordModal(userId) {
            document.getElementById('password-user-id').value = userId;
            document.getElementById('new-password').value = '';
            document.getElementById('password-modal').classList.remove('hidden');
        }
        function closePasswordModal() {
            document.getElementById('password-modal').classList.add('hidden');
        }

        document.getElementById('password-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('password-user-id').value;
            const newPassword = document.getElementById('new-password').value;

            try {
                const res = await fetch(`/api/v1/admin/users/${id}`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ password: newPassword })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Kata sandi berhasil diperbarui', 'success');
                    closePasswordModal();
                } else {
                    showToast(data.message || 'Gagal mengubah kata sandi', 'danger');
                }
            } catch (err) {
                showToast('Gagal memproses kata sandi', 'danger');
            }
        });

        // DELETE USER
        async function deleteUser(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus akun ini?')) return;
            const force = confirm('Apakah ingin menghapus secara permanen dari database (Force Delete)?');
            
            try {
                const url = `/api/v1/admin/users/${id}` + (force ? '?force=true' : '');
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    showToast(force ? 'Pengguna berhasil dihapus permanen' : 'Pengguna berhasil didelete (soft delete)', 'success');
                    fetchUsers();
                } else {
                    showToast(data.message || 'Gagal menghapus pengguna', 'danger');
                }
            } catch (err) {
                showToast('Gagal memproses penghapusan', 'danger');
            }
        }

        // CONTENT MANAGEMENT (CMS)
        function selectGrade(grade) {
            currentGrade = grade;
            document.querySelectorAll('#grade-selection-tabs button').forEach(btn => {
                btn.className = "px-3 py-1.5 rounded-lg text-xs font-semibold transition-all text-slate-400 hover:text-white cursor-pointer";
            });
            document.getElementById(`grade-tab-${grade}`).className = "px-3 py-1.5 rounded-lg text-xs font-semibold transition-all bg-blue-600 text-white cursor-pointer";
            fetchMaterials();
        }

        async function fetchMaterials() {
            try {
                const res = await fetch(`/api/v1/materials?filter[grade]=${currentGrade}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    renderMaterials(data.data);
                }
            } catch (err) {
                showToast('Gagal memuat daftar materi', 'danger');
            }
        }

        function renderMaterials(materials) {
            const grid = document.getElementById('materials-list-container');
            grid.innerHTML = '';

            if (materials.length === 0) {
                grid.innerHTML = `<div class="col-span-full py-16 text-center text-slate-500 font-medium bg-slate-900/10 rounded-2xl border border-slate-800 border-dashed">Belum ada materi untuk kelas ${currentGrade}.</div>`;
                return;
            }

            materials.forEach(mat => {
                const card = document.createElement('div');
                card.className = "glass rounded-2xl p-6 shadow-lg border border-slate-800/80 flex flex-col justify-between space-y-4 hover:border-slate-700/60 transition-all";
                card.innerHTML = `
                    <div class="space-y-2">
                        <div class="flex items-start justify-between">
                            <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 rounded-md border border-blue-500/25 text-xs font-bold uppercase tracking-wider">Kelas ${mat.grade}</span>
                            <span class="text-xs text-slate-500 flex items-center space-x-1"><i class="fa-regular fa-clock"></i> <span>Oleh: ${mat.creator_name || 'Admin'}</span></span>
                        </div>
                        <h4 class="text-lg font-bold text-white tracking-wide">${mat.title}</h4>
                        <p class="text-sm text-slate-400 line-clamp-3 leading-relaxed">${mat.description}</p>
                    </div>

                    <div class="border-t border-slate-800/50 pt-4 flex items-center justify-between gap-2 flex-wrap">
                        <a href="${mat.file_url}" target="_blank" class="text-xs text-blue-400 hover:underline flex items-center space-x-1.5"><i class="fa-solid fa-link"></i> <span>E-Book PDF</span></a>
                        <div class="flex items-center space-x-2">
                            <button onclick="openBuilderModal(${mat.id}, '${mat.title.replace(/'/g, "\\'")}')" class="py-1.5 px-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl flex items-center space-x-1 transition-colors cursor-pointer"><i class="fa-solid fa-layer-group"></i> <span>Penyusun Kuis</span></button>
                            <button onclick="editMaterial(${JSON.stringify(mat).replace(/"/g, '&quot;')})" class="py-1.5 px-3 bg-slate-850 hover:bg-slate-800 border border-slate-750 text-slate-300 font-semibold text-xs rounded-xl transition-colors cursor-pointer"><i class="fa-solid fa-pen"></i></button>
                            <button onclick="deleteMaterial(${mat.id})" class="py-1.5 px-3 bg-slate-850 hover:bg-red-950 hover:text-red-400 border border-slate-750 text-slate-400 font-semibold text-xs rounded-xl transition-colors cursor-pointer"><i class="fa-regular fa-trash-can"></i></button>
                        </div>
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        // MATERIAL MODALS
        function openAddMaterialModal() {
            document.getElementById('material-modal-title').textContent = 'Unggah Materi Baru';
            document.getElementById('material-id-input').value = '';
            document.getElementById('material-form').reset();
            document.getElementById('material-grade').value = currentGrade;
            document.getElementById('material-modal').classList.remove('hidden');
        }

        function closeMaterialModal() {
            document.getElementById('material-modal').classList.add('hidden');
        }

        function editMaterial(mat) {
            document.getElementById('material-modal-title').textContent = 'Edit Detail Materi';
            document.getElementById('material-id-input').value = mat.id;
            document.getElementById('material-title').value = mat.title;
            document.getElementById('material-description').value = mat.description;
            document.getElementById('material-grade').value = mat.grade;
            document.getElementById('material-file').value = '';
            document.getElementById('material-modal').classList.remove('hidden');
        }

        document.getElementById('material-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('material-id-input').value;
            
            const formData = new FormData();
            formData.append('title', document.getElementById('material-title').value);
            formData.append('description', document.getElementById('material-description').value);
            formData.append('grade', parseInt(document.getElementById('material-grade').value));
            
            const fileInput = document.getElementById('material-file');
            if (fileInput.files[0]) {
                const file = fileInput.files[0];
                if (file.size > 50 * 1024 * 1024) {
                    showToast('File tidak boleh melebihi 50 MB', 'danger');
                    return;
                }
                formData.append('file', file);
            } else if (!id) {
                showToast('Harap unggah file PDF materi', 'danger');
                return;
            }

            let url = '/api/v1/materials';
            if (id) {
                url += `/${id}`;
                formData.append('_method', 'PATCH');
            }

            try {
                const res = await fetch(url, {
                    method: 'POST', // Send as POST for multipart files, updating with _method = PATCH
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    showToast(id ? 'Materi berhasil diperbarui' : 'Materi berhasil diunggah', 'success');
                    closeMaterialModal();
                    fetchMaterials();
                } else {
                    showToast(data.message || 'Gagal menyimpan materi', 'danger');
                }
            } catch (err) {
                showToast('Gagal memproses materi', 'danger');
            }
        });

        // DELETE MATERIAL
        async function deleteMaterial(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus materi ini?')) return;
            try {
                const res = await fetch(`/api/v1/materials/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Materi berhasil dihapus', 'success');
                    fetchMaterials();
                } else {
                    showToast(data.message || 'Gagal menghapus materi', 'danger');
                }
            } catch (err) {
                showToast('Gagal memproses penghapusan', 'danger');
            }
        }

        // BUILDER MODAL: PRE-TEST / POST-TEST / PULSE BUILDER
        function openBuilderModal(materialId, materialTitle) {
            selectedMaterialIdForBuilder = materialId;
            document.getElementById('builder-modal-subtitle').textContent = `Materi: ${materialTitle}`;
            
            // Default to Pre-test
            switchBuilderSubTab('pre-test');
            document.getElementById('builder-modal').classList.remove('hidden');
        }

        function closeBuilderModal() {
            document.getElementById('builder-modal').classList.add('hidden');
        }

        function switchBuilderSubTab(tab) {
            builderSubTab = tab;
            
            // Highlight active subtab
            document.querySelectorAll('#builder-modal border-b-2').forEach(btn => {
                btn.className = "px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-white font-semibold text-sm cursor-pointer";
            });
            const subtabs = {
                'pre-test': 'subtab-pre-test',
                'post-test': 'subtab-post-test',
                'pulse': 'subtab-pulse'
            };
            
            // Set styles
            document.getElementById('subtab-pre-test').className = (tab === 'pre-test' ? 'px-4 py-3 border-b-2 border-blue-500 text-blue-400 font-semibold text-sm cursor-pointer' : 'px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-white font-semibold text-sm cursor-pointer');
            document.getElementById('subtab-post-test').className = (tab === 'post-test' ? 'px-4 py-3 border-b-2 border-blue-500 text-blue-400 font-semibold text-sm cursor-pointer' : 'px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-white font-semibold text-sm cursor-pointer');
            document.getElementById('subtab-pulse').className = (tab === 'pulse' ? 'px-4 py-3 border-b-2 border-blue-500 text-blue-400 font-semibold text-sm cursor-pointer' : 'px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-white font-semibold text-sm cursor-pointer');

            // Set titles
            const titles = {
                'pre-test': 'Soal Pre-Test',
                'post-test': 'Soal Post-Test',
                'pulse': 'Pernyataan Evaluasi PULSE (Likert Scale)'
            };
            document.getElementById('builder-content-title').textContent = titles[tab];

            // Set template download link dynamically
            const downloadLink = document.getElementById('download-template-link');
            if (tab === 'pre-test') {
                downloadLink.href = '/templates/pre_test_template.xlsx';
                downloadLink.download = 'pre_test_template.xlsx';
            } else if (tab === 'post-test') {
                downloadLink.href = '/templates/post_test_template.xlsx';
                downloadLink.download = 'post_test_template.xlsx';
            } else {
                downloadLink.href = '/templates/pulse_instrument_template.xlsx';
                downloadLink.download = 'pulse_instrument_template.xlsx';
            }

            // Hide form and update btn behavior
            hideBuilderForm();
            fetchBuilderItems();
        }

        // EXCEL IMPORT FOR BUILDER ITEMS
        function triggerExcelImport() {
            document.getElementById('excel-import-file').click();
        }

        async function handleExcelImport(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Reset input so same file can be uploaded again
            event.target.value = '';

            const confirmMsg = builderSubTab === 'pulse' 
                ? 'Apakah Anda yakin ingin mengimpor instrumen PULSE? Tindakan ini akan menghapus instrumen yang ada untuk materi ini.'
                : `Apakah Anda yakin ingin mengimpor soal ${builderSubTab === 'pre-test' ? 'Pre-Test' : 'Post-Test'}? Tindakan ini akan menghapus soal yang ada untuk materi ini.`;
                
            if (!confirm(confirmMsg)) return;

            const formData = new FormData();
            formData.append('learning_material_id', selectedMaterialIdForBuilder);
            formData.append('file', file);
            
            let url = '';
            if (builderSubTab === 'pulse') {
                url = '/api/v1/pulse-instruments/import';
            } else {
                url = '/api/v1/questions/import';
                formData.append('type', builderSubTab === 'pre-test' ? 'pre_test' : 'post_test');
            }

            showToast('Mengimpor data...', 'info');

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showToast(data.message || 'Data berhasil diimpor', 'success');
                    fetchBuilderItems();
                } else {
                    showToast(data.message || 'Gagal mengimpor data', 'danger');
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal mengimpor data', 'danger');
            }
        }

        // FETCH BUILDER ITEMS
        async function fetchBuilderItems() {
            const listContainer = document.getElementById('builder-items-list');
            listContainer.innerHTML = '<div class="py-10 text-center"><i class="fa-solid fa-spinner animate-spin text-2xl text-blue-500"></i></div>';

            try {
                if (builderSubTab === 'pre-test' || builderSubTab === 'post-test') {
                    // Fetch Questions
                    const dbType = builderSubTab === 'pre-test' ? 'pre_test' : 'post_test';
                    const res = await fetch(`/api/v1/questions?filter[learning_material_id]=${selectedMaterialIdForBuilder}&filter[type]=${dbType}`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        renderBuilderQuestions(data.data);
                    }
                } else {
                    // Fetch Pulse Instruments
                    const res = await fetch(`/api/v1/pulse-instruments?filter[learning_material_id]=${selectedMaterialIdForBuilder}`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        renderBuilderPulse(data.data);
                    }
                }
            } catch (err) {
                showToast('Gagal memuat butir evaluasi', 'danger');
            }
        }

        // RENDER BUILDER QUESTIONS
        function renderBuilderQuestions(questions) {
            const container = document.getElementById('builder-items-list');
            container.innerHTML = '';

            if (questions.length === 0) {
                container.innerHTML = '<div class="py-10 text-center text-slate-500">Belum ada pertanyaan kuis yang dibuat.</div>';
                return;
            }

            questions.forEach((q, idx) => {
                const card = document.createElement('div');
                card.className = 'glass p-4 rounded-xl border border-slate-800 flex items-start justify-between space-x-4';
                
                const optsText = q.options.map((opt, i) => {
                    const char = ['A', 'B', 'C', 'D'][i] || i;
                    const isCorrect = (q.correct_answer && char === q.correct_answer.toUpperCase()) ? 'text-emerald-400 font-bold' : 'text-slate-400';
                    return `<span class="mr-4 ${isCorrect}">${char}. ${opt}</span>`;
                }).join(' ');

                card.innerHTML = `
                    <div class="space-y-1.5 flex-1">
                        <span class="text-xs text-blue-400 font-semibold uppercase">Pertanyaan ${idx + 1}</span>
                        <p class="font-bold text-sm text-white">${q.question_text}</p>
                        <div class="text-xs flex flex-wrap pt-1">${optsText}</div>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <button onclick="editBuilderQuestion(${JSON.stringify(q).replace(/"/g, '&quot;')})" class="text-slate-400 hover:text-white cursor-pointer"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button onclick="deleteBuilderQuestion(${q.id})" class="text-slate-400 hover:text-red-400 cursor-pointer"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // RENDER BUILDER PULSE
        function renderBuilderPulse(pulseList) {
            const container = document.getElementById('builder-items-list');
            container.innerHTML = '';

            if (pulseList.length === 0) {
                container.innerHTML = '<div class="py-10 text-center text-slate-500">Belum ada instrumen perilaku PULSE yang dikonfigurasi.</div>';
                return;
            }

            pulseList.forEach((pulse, idx) => {
                const card = document.createElement('div');
                card.className = 'glass p-4 rounded-xl border border-slate-800 flex items-start justify-between space-x-4';
                card.innerHTML = `
                    <div class="space-y-1.5 flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-amber-400 font-semibold uppercase">Instrumen ${idx + 1}</span>
                            <span class="px-1.5 py-0.5 bg-blue-500/10 text-blue-400 rounded border border-blue-500/20 text-[10px] font-bold">Dimensi: ${pulse.dimension}</span>
                        </div>
                        <p class="font-bold text-sm text-white">${pulse.statement}</p>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <button onclick="editBuilderPulse(${JSON.stringify(pulse).replace(/"/g, '&quot;')})" class="text-slate-400 hover:text-white cursor-pointer"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button onclick="deleteBuilderPulse(${pulse.id})" class="text-slate-400 hover:text-red-400 cursor-pointer"><i class="fa-regular fa-trash-can"></i></button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // BUTTON: SHOW BUILDER FORM FOR ADD
        document.getElementById('add-builder-item-btn').addEventListener('click', () => {
            document.getElementById('builder-form').reset();
            document.getElementById('builder-item-id').value = '';
            
            const form = document.getElementById('builder-form');
            form.classList.remove('hidden');

            if (builderSubTab === 'pulse') {
                document.getElementById('form-question-block').classList.add('hidden');
                document.getElementById('form-pulse-block').classList.remove('hidden');
                document.getElementById('builder-statement').required = true;
            } else {
                document.getElementById('form-question-block').classList.remove('hidden');
                document.getElementById('form-pulse-block').classList.add('hidden');
                document.getElementById('builder-question-text').required = true;
                document.getElementById('builder-opt-a').required = true;
                document.getElementById('builder-opt-b').required = true;
                document.getElementById('builder-opt-c').required = true;
                document.getElementById('builder-opt-d').required = true;
                document.getElementById('builder-correct-answer').required = true;
            }
        });

        function hideBuilderForm() {
            const form = document.getElementById('builder-form');
            form.classList.add('hidden');
            form.reset();
        }

        // EDIT ACTIONS
        function editBuilderQuestion(q) {
            document.getElementById('builder-item-id').value = q.id;
            document.getElementById('builder-question-text').value = q.question_text;
            
            document.getElementById('builder-opt-a').value = q.options[0] || '';
            document.getElementById('builder-opt-b').value = q.options[1] || '';
            document.getElementById('builder-opt-c').value = q.options[2] || '';
            document.getElementById('builder-opt-d').value = q.options[3] || '';

            // Find matching correct option letter (A, B, C, D)
            document.getElementById('builder-correct-answer').value = q.correct_answer || '';

            // Show blocks
            document.getElementById('form-question-block').classList.remove('hidden');
            document.getElementById('form-pulse-block').classList.add('hidden');
            document.getElementById('builder-form').classList.remove('hidden');
        }

        function editBuilderPulse(pulse) {
            document.getElementById('builder-item-id').value = pulse.id;
            document.getElementById('builder-statement').value = pulse.statement;
            document.getElementById('builder-dimension').value = pulse.dimension;

            // Show blocks
            document.getElementById('form-question-block').classList.add('hidden');
            document.getElementById('form-pulse-block').classList.remove('hidden');
            document.getElementById('builder-form').classList.remove('hidden');
        }

        // SUBMIT BUILDER ITEM
        document.getElementById('builder-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('builder-item-id').value;
            const isPulse = (builderSubTab === 'pulse');

            let payload = {
                learning_material_id: selectedMaterialIdForBuilder
            };

            let url = isPulse ? '/api/v1/pulse-instruments' : '/api/v1/questions';
            if (id) url += `/${id}`;

            const method = id ? 'PATCH' : 'POST';

            if (isPulse) {
                payload.statement = document.getElementById('builder-statement').value;
                payload.dimension = document.getElementById('builder-dimension').value;
            } else {
                payload.question_text = document.getElementById('builder-question-text').value;
                payload.type = builderSubTab === 'pre-test' ? 'pre_test' : 'post_test';
                
                const optA = document.getElementById('builder-opt-a').value;
                const optB = document.getElementById('builder-opt-b').value;
                const optC = document.getElementById('builder-opt-c').value;
                const optD = document.getElementById('builder-opt-d').value;
                payload.options = [optA, optB, optC, optD];

                payload.correct_answer = document.getElementById('builder-correct-answer').value;
            }

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Butir kuis/instrumen berhasil disimpan', 'success');
                    hideBuilderForm();
                    fetchBuilderItems();
                } else {
                    showToast(data.message || 'Gagal menyimpan butir', 'danger');
                }
            } catch (err) {
                showToast('Gagal memproses simpan', 'danger');
            }
        });

        // DELETE QUESTIONS / PULSE
        async function deleteBuilderQuestion(id) {
            if (!confirm('Hapus soal pre/post-test ini?')) return;
            try {
                const res = await fetch(`/api/v1/questions/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Soal berhasil dihapus', 'success');
                    fetchBuilderItems();
                }
            } catch (err) {
                showToast('Gagal menghapus soal', 'danger');
            }
        }

        async function deleteBuilderPulse(id) {
            if (!confirm('Hapus instrumen perilaku PULSE ini?')) return;
            try {
                const res = await fetch(`/api/v1/pulse-instruments/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Instrumen berhasil dihapus', 'success');
                    fetchBuilderItems();
                }
            } catch (err) {
                showToast('Gagal menghapus instrumen', 'danger');
            }
        }
    </script>
</body>
</html>
