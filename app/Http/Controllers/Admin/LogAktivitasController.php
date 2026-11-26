<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        $query = LogAktivitas::with('user.karyawan')->orderByDesc('waktu');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('entitas')) {
            $query->where('entitas', $request->string('entitas'));
        }

        if ($request->filled('aksi')) {
            $query->where('aksi', 'like', '%' . $request->string('aksi') . '%');
        }

        $logs = $query->paginate(25)->withQueryString();
        $users = User::orderBy('username')->get(['id', 'username']);

        return view('admin.log.index', compact('logs', 'users'));
    }
}
