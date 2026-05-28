<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - CivicPulse</title>
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
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 relative overflow-hidden bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-900/40 via-slate-950 to-slate-950">
    <!-- Floating Blur Elements -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>

    <div class="w-full max-w-md z-10">
        <!-- Logo and Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-cyan-400 shadow-lg shadow-blue-500/30 mb-4 animate-pulse">
                <i class="fa-solid fa-heart-pulse text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-white">Civic<span class="text-blue-400">Pulse</span></h1>
            <p class="text-sm text-slate-400 mt-2">Integrated Analytics & Admin Dashboard</p>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-3xl shadow-2xl p-8 transition-all duration-300 hover:shadow-blue-500/5">
            <h2 class="text-xl font-semibold text-white mb-6 text-center">Masuk ke Akun Admin</h2>

            <!-- Error Banner -->
            <div id="error-banner" class="hidden mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-start space-x-3">
                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                <span id="error-message">Email atau kata sandi salah.</span>
            </div>

            <form id="login-form" class="space-y-5">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-2 uppercase tracking-wider">Email Administrator</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-regular fa-envelope"></i>
                        </span>
                        <input type="email" id="email" required placeholder="admin@civicpulse.com"
                            class="w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-slate-700/60 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-2 uppercase tracking-wider">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" required placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-3 bg-slate-900/50 border border-slate-700/60 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition-colors">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2 text-sm text-slate-400 cursor-pointer">
                        <input type="checkbox" id="remember_me" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-950">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submit-btn"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-semibold rounded-xl shadow-lg shadow-blue-600/25 hover:shadow-blue-500/35 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center space-x-2 cursor-pointer">
                    <span>Masuk</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>
        </div>

        <div class="text-center mt-8 text-xs text-slate-500">
            &copy; 2026 CivicPulse. All rights reserved.
        </div>
    </div>

    <script>
        // Check if token already exists, redirect to dashboard
        if (localStorage.getItem('admin_token')) {
            window.location.href = '/admin/dashboard';
        }

        // Toggle Password visibility
        const passwordInput = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('toggle-password');
        togglePasswordBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePasswordBtn.innerHTML = isPassword 
                ? '<i class="fa-regular fa-eye-slash"></i>' 
                : '<i class="fa-regular fa-eye"></i>';
        });

        // Form Submit Handler
        const form = document.getElementById('login-form');
        const submitBtn = document.getElementById('submit-btn');
        const errorBanner = document.getElementById('error-banner');
        const errorMessage = document.getElementById('error-message');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // UI Loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin mr-2"></i> Memproses...';
            errorBanner.classList.add('hidden');

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('remember_me').checked;

            try {
                const response = await fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password, remember_me: rememberMe })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal masuk. Cek email dan password Anda.');
                }

                // Check role: must be admin
                if (data.data.user.role !== 'admin') {
                    throw new Error('Akses ditolak. Akun Anda bukan Administrator.');
                }

                // Save token and user info
                localStorage.setItem('admin_token', data.data.token);
                localStorage.setItem('admin_user', JSON.stringify(data.data.user));

                // Redirect to dashboard
                window.location.href = '/admin/dashboard';

            } catch (err) {
                errorMessage.textContent = err.message;
                errorBanner.classList.remove('hidden');
                
                // Reset submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Masuk</span> <i class="fa-solid fa-arrow-right text-xs"></i>';
            }
        });
    </script>
</body>
</html>
