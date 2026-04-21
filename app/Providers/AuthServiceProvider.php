<?php

namespace App\Providers;

use App\Models\Pegawai;
// use App\Policies\PegawaiPolicy; // Kebijakan otorisasi ditangani manual di Controller
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Pegawai::class => PegawaiPolicy::class, // Ditiadakan, cek otorisasi manual di Controller via helper authorizePegawaiAccess()
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

