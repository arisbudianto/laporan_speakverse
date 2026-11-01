<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    /**
     * Tampilkan semua mission
     */
    public function index()
    {
        $missions = Mission::latest()->get();

        return view('admin.missions.mission', [
            'missions' => $missions
        ]);
    }

    /**
     * Simpan mission baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|in:speaking,reading,vocabulary',
            'difficulty' => 'required|in:easy,medium,hard',
            'reward_xp' => 'required|integer|min:0',
            'description' => 'required',
            'deadline' => 'nullable|date',
            'status' => 'required|in:active,draft',
        ]);

        Mission::create($validated);

        return redirect()
            ->route('admin.missions')
            ->with('success', 'Mission berhasil ditambahkan.');
    }

    /**
     * Form edit mission
     */
    public function edit($id)
    {
        $mission = Mission::findOrFail($id);

        return view('admin.missions.edit', [
            'mission' => $mission
        ]);
    }

    /**
     * Update mission
     */
    public function update(Request $request, $id)
    {
        $mission = Mission::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'category' => 'required|in:speaking,reading,vocabulary',
            'difficulty' => 'required|in:easy,medium,hard',
            'reward_xp' => 'required|integer|min:0',
            'description' => 'required',
            'deadline' => 'nullable|date',
            'status' => 'required|in:active,draft',
        ]);

        $mission->update($validated);

        return redirect()
            ->route('admin.missions')
            ->with('success', 'Mission berhasil diperbarui.');
    }

    /**
     * Hapus mission
     */
    public function destroy($id)
    {
        $mission = Mission::findOrFail($id);

        $mission->delete();

        return redirect()
            ->route('admin.missions')
            ->with('success', 'Mission berhasil dihapus.');
    }
}