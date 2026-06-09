@php
    $selectedType = $selectedType ?? \App\Models\Employee::CARD_TYPE_NORMAL;
@endphp

@once
    @push('styles')
    <style>
        .card-type-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }
        .card-type-option {
            position: relative;
            display: block;
        }
        .card-type-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .card-type-choice {
            height: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            background: #fff;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        .card-type-choice:hover {
            border-color: #93c5fd;
            transform: translateY(-1px);
        }
        .card-type-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .65rem;
            background: #eff6ff;
            color: #2563eb;
        }
        .card-type-option input:checked + .card-type-choice {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }
        .card-type-option input[value="credit"]:checked + .card-type-choice {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .14);
        }
        .card-type-option input[value="credit"]:checked + .card-type-choice .card-type-icon {
            background: #fef3c7;
            color: #b45309;
        }
        .card-type-option input[value="corporate"]:checked + .card-type-choice {
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, .12);
        }
        .card-type-option input[value="corporate"]:checked + .card-type-choice .card-type-icon {
            background: #e0f2fe;
            color: #0369a1;
        }
        @media (max-width: 768px) {
            .card-type-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @endpush
@endonce

<div class="mt-4">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
        <label class="form-label fw-semibold mb-0">Tipo de tarjeta <span class="text-danger">*</span></label>
        <span class="text-muted" style="font-size:.75rem;">Define banner y paleta publica</span>
    </div>
    <div class="card-type-grid">
        @foreach(\App\Models\Employee::cardTypes() as $value => $type)
            <label class="card-type-option">
                <input type="radio" name="card_type" value="{{ $value }}" {{ $selectedType === $value ? 'checked' : '' }}>
                <span class="card-type-choice d-block">
                    <span class="card-type-icon"><i class="fas {{ $type['icon'] }}"></i></span>
                    <span class="d-block fw-semibold text-dark" style="font-size:.88rem;">{{ $type['label'] }}</span>
                    <span class="d-block text-muted mt-1" style="font-size:.74rem;line-height:1.35;">{{ $type['description'] }}</span>
                </span>
            </label>
        @endforeach
    </div>
    @error('card_type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div>
