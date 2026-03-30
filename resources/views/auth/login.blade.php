<x-guest-layout>
    <!-- Toast Notification (Fixed Top-Right) -->
    @if (session('status'))
        <div id="toast-notification" class="fixed top-5 right-5 z-[9999] animate-bounce"
            style="position: fixed; top: 20px; right: 20px;">
            <div class="bg-white border-l-4 border-[#2563EB] shadow-2xl p-4 rounded-r-md flex items-center gap-3 pr-6">
                <i class="fas fa-check-circle text-[#2563EB] text-xl"></i>
                <div>
                    <h3 class="font-bold text-gray-800">Warantos!</h3>
                    <p class="text-sm text-gray-600">{{ session('status') }}</p>
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()"
                    class="ml-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <script>
                setTimeout(function () {
                    const toast = document.getElementById('toast-notification');
                    if (toast) {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.5s ease-out';
                        setTimeout(() => toast.remove(), 500);
                    }
                }, 3000); // Auto close after 3 seconds
            </script>
        </div>
    @endif
    <!-- Custom Float Animation -->
    <style>
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>

    <div class="flex flex-col md:flex-row h-full min-h-[600px]">
        <!-- Left Side: Illustration -->
        <div class="hidden md:block md:w-5 bg-[#2563EB] h-full">
            <!-- Decorative colored strip -->
        </div>
        <div class="hidden md:flex md:w-1/2 bg-white items-center justify-center p-8 relative overflow-hidden">
            <!-- Use the previously generated friendly vector -->
            <div class="relative z-10 flex flex-col items-center">
                <img src="/brain/562d9100-dde1-470e-a591-d7d39bc89f95/login_illustration_v5.png"
                    alt="Login Illustration" class="max-w-md w-full object-contain mix-blend-multiply animate-float">

            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-8 md:p-16 bg-white">
            <div class="w-full max-w-sm space-y-6">
                <div class="text-left">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                        Wilujeng Sumping!
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Mangga lebetkeun detilna di handap ieu.
                    </p>
                </div>



                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-4">
                        <!-- Email Address -->
                        <div>
                            <label for="email"
                                class="block text-sm font-medium leading-6 text-gray-900 mb-1">Email</label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#2563EB] sm:text-sm sm:leading-6"
                                placeholder="Lebetkeun email" value="{{ old('email') }}">
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password"
                                class="block text-sm font-medium leading-6 text-gray-900 mb-1">Password</label>
                            <input id="password" name="password" type="password" autocomplete="current-password"
                                required
                                class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#2563EB] sm:text-sm sm:leading-6"
                                placeholder="********">
                        </div>
                        <div class="space-y-4">
                            <!-- Captcha -->
                            <div>
                                <label for="captcha" class="block text-sm font-medium leading-6 text-gray-900 mb-1">Kode
                                    Kaamanan</label>
                                <div class="flex gap-4">
                                    <div class="w-1/2">
                                        <input id="captcha" name="captcha" type="text" required
                                            class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#2563EB] sm:text-sm sm:leading-6"
                                            placeholder="Lebetkeun kode">
                                    </div>
                                    <div class="w-1/2 flex items-center">
                                        <img src="{{ route('captcha.generate') }}" alt="Captcha"
                                            class="h-10 border rounded cursor-pointer"
                                            onclick="this.src='{{ route('captcha.generate') }}?'+Math.random()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember" type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-700">Émutan dugi ka 30
                        dinten</label>
                </div>
            </div>

            <div class="space-y-3">
                <button type="submit"
                    class="flex w-full justify-center rounded-md bg-[#2563EB] px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-[#1D4ED8] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2563EB] transition-all duration-200">
                    Hayu lebet
                </button>


            </div>
            </form>


        </div>
    </div>
    </div>
    <!-- Generic Error Modal (Email, Password, Captcha) -->
    @if ($errors->any())
        <div id="errorModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
            role="dialog" aria-modal="true">
            <div class="relative w-full max-w-md p-4 animate-float">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden p-8 text-center relative">
                    <!-- Close Button -->
                    <button onclick="document.getElementById('errorModal').remove()"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <!-- Icon -->
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                    </div>

                    <!-- Title -->
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        Login Gagal
                    </h2>

                    <!-- Message -->
                    <p class="text-gray-500 mb-8 leading-relaxed text-sm">
                        {{ $errors->first() }}
                    </p>

                    <!-- Button -->
                    <button onclick="document.getElementById('errorModal').remove()"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-red-200 transition-all transform hover:-translate-y-0.5">
                        Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Inactive Account Modal -->
    @if (session('account_inactive'))
        <div id="inactiveModal"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" role="dialog"
            aria-modal="true">
            <div class="relative w-full max-w-md p-4 animate-float">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden p-8 text-center relative">
                    <!-- Close Button -->
                    <button onclick="document.getElementById('inactiveModal').remove()"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>

                    <!-- Icon -->
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6">
                        <i class="fas fa-user-times text-3xl text-red-500"></i>
                    </div>

                    <!-- Title -->
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        Akun Tidak Aktif
                    </h2>

                    <!-- Message -->
                    <p class="text-gray-500 mb-8 leading-relaxed text-sm">
                        Maaf, akun Anda saat ini sedang dinonaktifkan. <br>
                        Silakan hubungi administrator sistem untuk informasi lebih lanjut.
                    </p>

                    <!-- Button -->
                    <button onclick="document.getElementById('inactiveModal').remove()"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-red-200 transition-all transform hover:-translate-y-0.5">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-guest-layout>