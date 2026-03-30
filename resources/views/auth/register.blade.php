<x-guest-layout>
    <div class="flex flex-col md:flex-row h-full min-h-[600px]">
        <!-- Left Side: Illustration -->
        <div class="hidden md:block md:w-5 bg-[#2563EB] h-full">
            <!-- Decorative colored strip -->
        </div>
        <div class="hidden md:flex md:w-1/2 bg-white items-center justify-center p-8 relative overflow-hidden">
            <!-- Use the previously generated friendly vector -->
            <img src="/brain/562d9100-dde1-470e-a591-d7d39bc89f95/register_illustration_v3_transparent.png"
                alt="Register Illustration"
                class="max-w-md w-full relative z-10 object-contain transform -translate-x-12">
        </div>

        <!-- Right Side: Registration Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-8 md:p-16 bg-white">
            <div class="w-full max-w-sm space-y-6">
                <div class="text-left">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                        Create an account
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Start your journey with us today.
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label for="name"
                                class="block text-sm font-medium leading-6 text-gray-900 mb-1">Name</label>
                            <input id="name" name="name" type="text" autocomplete="name" required autofocus
                                class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#2563EB] sm:text-sm sm:leading-6"
                                placeholder="Enter your name" value="{{ old('name') }}">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="email"
                                class="block text-sm font-medium leading-6 text-gray-900 mb-1">Email</label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#2563EB] sm:text-sm sm:leading-6"
                                placeholder="Enter your email" value="{{ old('email') }}">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password"
                                class="block text-sm font-medium leading-6 text-gray-900 mb-1">Password</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#2563EB] sm:text-sm sm:leading-6"
                                placeholder="********">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation"
                                class="block text-sm font-medium leading-6 text-gray-900 mb-1">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                autocomplete="new-password" required
                                class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-[#2563EB] sm:text-sm sm:leading-6"
                                placeholder="********">
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <button type="submit"
                            class="flex w-full justify-center rounded-md bg-[#2563EB] px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-[#1D4ED8] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2563EB] transition-all duration-200">
                            Sign up
                        </button>
                    </div>
                </form>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-500">
                        Already have an account? <a href="{{ route('login') }}"
                            class="font-semibold text-[#2563EB] hover:underline">Log in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>