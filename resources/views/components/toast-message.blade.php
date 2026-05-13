@props(['type' => 'info', 'message' => ''])

@php
    $typeConfig = [
        'success' => [
            'bgClass' => 'alert-success',
            'icon' => 'fas fa-check-circle',
            'title' => 'Berhasil!'
        ],
        'error' => [
            'bgClass' => 'alert-danger',
            'icon' => 'fas fa-exclamation-circle',
            'title' => 'Error!'
        ],
        'info' => [
            'bgClass' => 'alert-info',
            'icon' => 'fas fa-info-circle',
            'title' => 'Info!'
        ],
        'warning' => [
            'bgClass' => 'alert-warning',
            'icon' => 'fas fa-exclamation-triangle',
            'title' => 'Peringatan!'
        ]
    ];

    $config = $typeConfig[$type] ?? $typeConfig['info'];
@endphp

@if($message)
<div class="toast-message alert {{ $config['bgClass'] }} alert-dismissible d-flex align-items-center p-3 shadow-lg position-fixed" style="bottom: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; animation: slideIn 0.3s ease-out;" role="alert">
    <i class="{{ $config['icon'] }} me-2"></i>
    <div class="flex-grow-1">
        <strong>{{ $config['title'] }}</strong><br>
        <small>{{ $message }}</small>
    </div>
    </div>
@endif
