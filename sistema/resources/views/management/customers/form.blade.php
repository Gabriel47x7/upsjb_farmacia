<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <label for="nombre" class="form-label">Nombre completo</label>
        <input type="text" name="nombre" id="nombre" maxlength="150"
            class="form-control @error('nombre') is-invalid @enderror"
            value="{{ old('nombre', $customer->nombre ?? '') }}">
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="dni" class="form-label">DNI (8 dígitos)</label>
        <input type="text" name="dni" id="dni" maxlength="8"
            class="form-control @error('dni') is-invalid @enderror"
            value="{{ old('dni', $customer->dni ?? '') }}">
        @error('dni')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="telefono" class="form-label">Teléfono <small class="text-muted">(opcional)</small></label>
        <input type="text" name="telefono" id="telefono" maxlength="15"
            class="form-control @error('telefono') is-invalid @enderror"
            value="{{ old('telefono', $customer->telefono ?? '') }}">
        @error('telefono')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="edad" class="form-label">Edad <small class="text-muted">(opcional)</small></label>
        <input type="number" name="edad" id="edad" min="0" max="120"
            class="form-control @error('edad') is-invalid @enderror"
            value="{{ old('edad', $customer->edad ?? '') }}">
        @error('edad')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<button type="submit" class="btn btn-primary text-white">Guardar</button>
<a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancelar</a>