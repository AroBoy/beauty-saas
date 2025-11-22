<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkerRequest;
use App\Models\Worker;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function index(): View
    {
        $workers = Worker::orderBy('name')->get();

        return view('workers.index', compact('workers'));
    }

    public function create(): View
    {
        return view('workers.form', ['worker' => new Worker()]);
    }

    public function store(WorkerRequest $request): RedirectResponse
    {
        Worker::create($request->validated());

        return redirect()->route('workers.index')->with('status', 'Pracownik utworzony.');
    }

    public function edit(Worker $worker): View
    {
        return view('workers.form', compact('worker'));
    }

    public function update(WorkerRequest $request, Worker $worker): RedirectResponse
    {
        $worker->update($request->validated());

        return redirect()->route('workers.index')->with('status', 'Pracownik zaktualizowany.');
    }

    public function destroy(Worker $worker): RedirectResponse
    {
        $worker->delete();

        return redirect()->route('workers.index')->with('status', 'Pracownik usunięty.');
    }
}
