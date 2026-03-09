<div class="login-card">
    <div class="login-logo-wrap">
        <div class="login-logo-ring">
            <img src="/images/logo.png" alt="GIRAG">
        </div>
        <div class="login-app-name">App Vuelos</div>
        <div class="login-app-sub">Sistema de Gestión · GIRAG</div>
    </div>

    <form wire:submit.prevent="login">
        <div class="form-group">
            <label class="login-label">Usuario o Correo</label>
            <input wire:model="username" type="text"
                   class="login-input {{ $errors->has('username') ? 'is-invalid' : '' }}"
                   placeholder="Ingrese su usuario" autofocus autocomplete="username">
            @error('username')
                <div class="login-error"><i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="login-label">Contraseña</label>
            <input wire:model="password" type="password"
                   class="login-input"
                   placeholder="••••••••" autocomplete="current-password">
        </div>

        <button type="submit" class="login-btn" wire:loading.attr="disabled">
            <span wire:loading.remove>
                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Ingresar
            </span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2"></span>Verificando...
            </span>
        </button>
    </form>
</div>
