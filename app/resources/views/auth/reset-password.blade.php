<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-[#7700F0] rounded-full flex items-center justify-center">
                    <span class="text-3xl text-white">🔑</span>
                </div>
            </div>
            
            <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Nueva contraseña</h1>
            <p class="text-center text-gray-500 text-sm mb-6">Ingresa tu nueva contraseña</p>
            
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7700F0] focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Nueva contraseña
                    </label>
                    <input id="password" type="password" name="password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7700F0] focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar contraseña
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#7700F0] focus:border-transparent">
                </div>
                
                <button type="submit" class="w-full bg-[#7700F0] hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                    Restablecer contraseña
                </button>
            </form>
            
            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm text-[#7700F0] hover:text-purple-700">
                    ← Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>