{{-- Campos compartilhados por criar e editar: a regra de validação é a mesma
     (CreateBoardRequest e UpdateBoardRequest), então o formulário também é. --}}
<div>
    <x-input-label for="title" :value="__('Title')" />
    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                  :value="old('title', $board->title ?? '')" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('title')" />
</div>

<div class="mt-4">
    <x-input-label for="category" :value="__('Category')" />
    <select id="category" name="category" required
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        @foreach ($categories as $category)
            <option value="{{ $category->value }}"
                @selected(old('category', $board->category->value ?? '') === $category->value)>
                {{ $category->label() }}
            </option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('category')" />
</div>

<div class="mt-4">
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="4"
              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $board->description ?? '') }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>
