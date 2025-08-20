<div x-data="{ showTaskModal:false, showObservers:false }"
     x-on:open-task-modal.window="showTaskModal=true"
     x-on:close-task-modal.window="showTaskModal=false"
     x-on:open-observers-modal.window="showObservers=true">

    {{-- Toolbar filtrów --}}
    <div class="flex items-center gap-3 mb-4">
        <select wire:model.live="scope" class="border rounded px-2 py-1">
            <option value="all">Wszystkie (moje + obserwowane)</option>
            <option value="owned">Tylko moje</option>
            <option value="observed">Obserwowane</option>
        </select>

        <select wire:model.live="status" class="border rounded px-2 py-1">
            <option value="">Status: dowolny</option>
            @foreach($statuses as $st)
                <option value="{{ $st->value }}">{{ str($st->value)->replace('_',' ')->title() }}</option>
            @endforeach
        </select>

        <select wire:model.live="perPage" class="border rounded px-2 py-1">
            @foreach([5,10,15,25] as $pp)
                <option value="{{ $pp }}">{{ $pp }}/stronę</option>
            @endforeach
        </select>

        <div class="flex items-center gap-2 ml-2">
            <input type="search"
                   placeholder="Szukaj w tytule/opisie…"
                   class="border rounded px-2 py-1"
                   wire:model.live.debounce.500ms="q">
            @if($q !== '')
                <button class="text-sm px-2 py-1 border rounded"
                        wire:click="$set('q','')">
                    Wyczyść
                </button>
            @endif
        </div>

        <button class="ml-auto bg-blue-600 text-white rounded px-3 py-1"
                wire:click="startCreate">
            + Nowe zadanie
        </button>
    </div>

    {{-- Tabela zadań --}}
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
            <tr>
                <th class="text-left p-2">Tytuł</th>
                <th class="text-left p-2">Status</th>
                <th class="text-left p-2">Termin</th>
                <th class="text-right p-2">Akcje</th>
            </tr>
            </thead>
            <tbody>
            @forelse($tasks as $task)
                <tr class="border-t">
                    <td class="p-2">
                        <div class="font-semibold">{{ $task->title }}</div>
                        <div class="text-xs text-gray-500">
                            owner #{{ $task->ownerId ?? $task->owner_id }}
                        </div>
                    </td>
                    <td class="p-2">
                        + @php($st = $task->status instanceof \BackedEnum ? $task->status->value : $task->status)
                        + <span class="text-sm">{{ str($st)->replace('_',' ')->title() }}</span>
                    </td>
                    <td class="p-2">
                        {{ ($task->dueAt ?? $task->due_at)?->format('Y-m-d H:i') ?? '—' }}
                    </td>
                    <td class="p-2 text-right">
                        @can('update', \App\Infrastructure\Tasks\Models\Task::find($task->id))
                            <button class="text-sm px-2 py-1 border rounded mr-1" wire:click="startEdit({{ $task->id }})">Edytuj</button>
                            <button class="text-sm px-2 py-1 border rounded mr-1" wire:click="openObservers({{ $task->id }})">Obserwatorzy</button>
                        @endcan
                        @can('delete', \App\Infrastructure\Tasks\Models\Task::find($task->id))
                            <button class="text-sm px-2 py-1 border rounded text-red-600"
                                    wire:click="delete({{ $task->id }})"
                                    x-on:click.prevent="confirm('Usunąć zadanie?') || $event.stopImmediatePropagation()">
                                Usuń
                            </button>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-4 text-center text-gray-500">Brak zadań</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $tasks->links() }}
    </div>

    {{-- Modal: Create/Edit --}}
    <div x-show="showTaskModal" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">
        <div class="bg-white w-full max-w-lg rounded shadow p-4"
             @click.outside="showTaskModal=false">
            <h2 class="text-lg font-semibold mb-3">{{ $editingId ? 'Edytuj zadanie' : 'Nowe zadanie' }}</h2>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm mb-1">Tytuł *</label>
                    <input type="text" wire:model.defer="title" class="w-full border rounded px-3 py-2">
                    @error('title') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm mb-1">Opis</label>
                    <textarea wire:model.defer="description" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                </div>

                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="block text-sm mb-1">Status</label>
                        <select wire:model.defer="statusForm" class="w-full border rounded px-3 py-2">
                            @foreach($statuses as $st)
                                <option value="{{ $st->value }}">{{ str($st->value)->replace('_',' ')->title() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm mb-1">Termin</label>
                        <input type="datetime-local"
                               wire:model.defer="due_at"
                               class="w-full border rounded px-3 py-2"
                               required> {{-- HTML5 wymagane (opcjonalne, dla UX) --}}
                        @error('due_at') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button class="px-3 py-2 border rounded" @click="showTaskModal=false">Anuluj</button>
                <button class="px-3 py-2 bg-blue-600 text-white rounded" wire:click="save">Zapisz</button>
            </div>
        </div>
    </div>

    {{-- Modal: Obserwatorzy --}}
    <div x-show="showObservers" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">
        <div class="bg-white w-full max-w-xl rounded shadow p-4" @click.outside="showObservers=false">
            <h2 class="text-lg font-semibold mb-3">Obserwatorzy</h2>

            <input type="text" placeholder="Szukaj użytkownika po imieniu/emailu..."
                   wire:model.live="observerSearch"
                   class="w-full border rounded px-3 py-2 mb-3">

            <div class="max-h-60 overflow-y-auto border rounded">
                @foreach($this->observerCandidates as $u)
                    <label class="flex items-center gap-3 border-b px-3 py-2">
                        <input type="checkbox"
                               @checked(in_array($u->id, $selectedObserverIds))
                               wire:click="toggleObserver({{ $u->id }})">
                        <span class="text-sm">{{ $u->name }} <span class="text-gray-500">({{ $u->email }})</span></span>
                    </label>
                @endforeach
                @if (count($this->observerCandidates) === 0)
                    <div class="p-3 text-sm text-gray-500">Brak kandydatów</div>
                @endif
            </div>

            <div class="mt-4 flex justify-end">
                <button class="px-3 py-2 border rounded" @click="showObservers=false">Zamknij</button>
            </div>
        </div>
    </div>
</div>
