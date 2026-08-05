<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard utama setelah login. View dipilih dinamis sesuai role user
     * agar redirect per-role (Definition of Done Fase 0) berjalan.
     */
    public function index(): View
    {
        $user = Auth::user();
        $roleSlug = $user->roleSlug();

        // Label tampilan dari slug role.
        $roleLabel = str_replace('-', ' ', ucwords($roleSlug));

        return view('dashboard.index', [
            'roleSlug' => $roleSlug,
            'roleLabel' => $roleLabel,
            'user' => $user,
        ]);
    }
}