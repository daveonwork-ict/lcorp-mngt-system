<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hr\StorePositionRequest;
use App\Http\Requests\Hr\UpdatePositionRequest;
use App\Models\Position;
use App\Services\PositionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function __construct(private readonly PositionService $positionService)
    {
    }

    public function index(Request $request): View
    {
        return view('hr.positions.index', [
            'positions' => $this->positionService->paginate($request->only(['search', 'status'])),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('hr.positions.form', [
            'position' => new Position(),
            'mode' => 'create',
        ]);
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        $position = $this->positionService->create($request->validated());

        return redirect()->route('hr.positions.edit', $position)->with('status', 'Position created successfully.');
    }

    public function edit(Position $position): View
    {
        return view('hr.positions.form', [
            'position' => $position,
            'mode' => 'edit',
        ]);
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $this->positionService->update($position, $request->validated());

        return back()->with('status', 'Position updated successfully.');
    }
}
