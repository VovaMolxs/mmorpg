<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDialogRequest;
use App\Http\Requests\Admin\UpdateDialogRequest;
use App\Models\Dialog;
use App\Models\DialogAnswer;
use App\Models\Item;
use App\Models\Npc;
use App\Models\Quest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DialogController extends Controller
{
    /**
     * Display a listing of dialogs.
     */
    public function index(Request $request): View
    {
        $query = Dialog::with(['npc', 'parentDialog', 'requiredQuest'])->latest();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('text', 'like', "%{$search}%")
                    ->orWhereHas('npc', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('npc_id')) {
            $query->where('npc_id', $request->get('npc_id'));
        }

        if ($request->has('is_initial')) {
            $query->where('is_initial', $request->boolean('is_initial'));
        }

        $dialogs = $query->paginate(20);
        $npcs = Npc::orderBy('name')->get();

        return view('admin.dialogs.index', [
            'dialogs' => $dialogs,
            'npcs' => $npcs,
        ]);
    }

    /**
     * Show the form for creating a new dialog.
     */
    public function create(): View
    {
        $npcs = Npc::orderBy('name')->get();
        $dialogs = Dialog::orderBy('text')->get();
        $quests = Quest::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();

        $questStatuses = [
            'active' => 'Активный',
            'completed' => 'Завершен',
            'failed' => 'Провален',
            'abandoned' => 'Отменен',
        ];

        return view('admin.dialogs.create', [
            'npcs' => $npcs,
            'dialogs' => $dialogs,
            'quests' => $quests,
            'items' => $items,
            'skills' => $skills,
            'questStatuses' => $questStatuses,
        ]);
    }

    /**
     * Store a newly created dialog.
     */
    public function store(StoreDialogRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Создаем диалог
                $dialog = Dialog::create([
                    'npc_id' => $validated['npc_id'],
                    'parent_dialog_id' => $validated['parent_dialog_id'] ?? null,
                    'text' => $validated['text'],
                    'is_initial' => $validated['is_initial'] ?? false,
                    'min_level' => $validated['min_level'] ?? null,
                    'required_quest_id' => $validated['required_quest_id'] ?? null,
                    'required_quest_status' => $validated['required_quest_status'] ?? null,
                ]);

                // Создаем ответы
                if (isset($validated['answers']) && is_array($validated['answers'])) {
                    foreach ($validated['answers'] as $answerData) {
                        if (! empty($answerData['text'])) {
                            DialogAnswer::create([
                                'dialog_id' => $dialog->id,
                                'text' => $answerData['text'],
                                'next_dialog_id' => $answerData['next_dialog_id'] ?? null,
                                'quest_trigger_id' => $answerData['quest_trigger_id'] ?? null,
                                'item_required_id' => $answerData['item_required_id'] ?? null,
                                'skill_required' => $answerData['skill_required'] ?? null,
                                'skill_level_required' => $answerData['skill_level_required'] ?? null,
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('admin.dialogs.index')
                ->with('success', 'Диалог успешно создан!');
        } catch (\Exception $e) {
            Log::error('Dialog creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании диалога: '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified dialog.
     */
    public function edit(Dialog $dialog): View
    {
        $dialog->load(['answers', 'npc', 'parentDialog', 'requiredQuest']);

        $npcs = Npc::orderBy('name')->get();
        $dialogs = Dialog::where('id', '!=', $dialog->id)->orderBy('text')->get();
        $quests = Quest::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $skills = Skill::orderBy('name')->get();

        $questStatuses = [
            'active' => 'Активный',
            'completed' => 'Завершен',
            'failed' => 'Провален',
            'abandoned' => 'Отменен',
        ];

        return view('admin.dialogs.edit', [
            'dialog' => $dialog,
            'npcs' => $npcs,
            'dialogs' => $dialogs,
            'quests' => $quests,
            'items' => $items,
            'skills' => $skills,
            'questStatuses' => $questStatuses,
        ]);
    }

    /**
     * Update the specified dialog.
     */
    public function update(UpdateDialogRequest $request, Dialog $dialog): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $dialog) {
                $validated = $request->validated();

                // Обновляем диалог
                $dialog->update([
                    'npc_id' => $validated['npc_id'] ?? $dialog->npc_id,
                    'parent_dialog_id' => $validated['parent_dialog_id'] ?? $dialog->parent_dialog_id,
                    'text' => $validated['text'] ?? $dialog->text,
                    'is_initial' => $validated['is_initial'] ?? $dialog->is_initial,
                    'min_level' => $validated['min_level'] ?? $dialog->min_level,
                    'required_quest_id' => $validated['required_quest_id'] ?? $dialog->required_quest_id,
                    'required_quest_status' => $validated['required_quest_status'] ?? $dialog->required_quest_status,
                ]);

                // Обновляем ответы (удаляем старые и создаем новые)
                if (isset($validated['answers'])) {
                    $dialog->answers()->delete();
                    if (is_array($validated['answers'])) {
                        foreach ($validated['answers'] as $answerData) {
                            if (! empty($answerData['text'])) {
                                DialogAnswer::create([
                                    'dialog_id' => $dialog->id,
                                    'text' => $answerData['text'],
                                    'next_dialog_id' => $answerData['next_dialog_id'] ?? null,
                                    'quest_trigger_id' => $answerData['quest_trigger_id'] ?? null,
                                    'item_required_id' => $answerData['item_required_id'] ?? null,
                                    'skill_required' => $answerData['skill_required'] ?? null,
                                    'skill_level_required' => $answerData['skill_level_required'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            });

            return redirect()->route('admin.dialogs.index')
                ->with('success', 'Диалог успешно обновлен!');
        } catch (\Exception $e) {
            Log::error('Dialog update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dialog_id' => $dialog->id,
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при обновлении диалога: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified dialog.
     */
    public function destroy(Dialog $dialog): RedirectResponse
    {
        try {
            $dialog->delete();

            return redirect()->route('admin.dialogs.index')
                ->with('success', 'Диалог успешно удален!');
        } catch (\Exception $e) {
            Log::error('Dialog deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dialog_id' => $dialog->id,
            ]);

            return back()
                ->withErrors(['error' => 'Произошла ошибка при удалении диалога: '.$e->getMessage()]);
        }
    }
}
