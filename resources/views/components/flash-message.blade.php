{{--
    Flash Message Component
    Reads session('success') and session('error') internally.
    No props required.
--}}

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="ph ph-check-circle flex-shrink-0 mt-1"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="ph ph-warning-circle flex-shrink-0 mt-1"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="ph ph-warning flex-shrink-0 mt-1"></i>
        <div>{{ session('warning') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="ph ph-info flex-shrink-0 mt-1"></i>
        <div>{{ session('info') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
