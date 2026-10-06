@php
    use App\Support\Textile;

    $d = $donation ?? null;
    $input = 'mt-1 block w-full rounded-md border border-stone-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
    $label = 'block text-sm font-medium text-stone-700';
    $required = $required ?? false; // true : catégorie et état obligatoires (repli manuel)
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="{{ $label }}" for="title">Titre du don</label>
        <input id="title" name="title" type="text" required maxlength="120" class="{{ $input }}"
               placeholder="Ex. Manteau d'hiver enfant + 2 pulls"
               value="{{ old('title', $d?->title) }}">
        @include('don.partials.field-error', ['name' => 'title'])
    </div>

    <div class="sm:col-span-2">
        <label class="{{ $label }}" for="description">Description <span class="font-normal text-stone-400">(facultatif)</span></label>
        <textarea id="description" name="description" rows="3" maxlength="2000" class="{{ $input }}"
                  placeholder="Marque, matière, défauts éventuels…">{{ old('description', $d?->description) }}</textarea>
        @include('don.partials.field-error', ['name' => 'description'])
    </div>

    <div>
        <label class="{{ $label }}" for="quantity">Nombre de pièces</label>
        <input id="quantity" name="quantity" type="number" min="1" max="200" required class="{{ $input }}"
               value="{{ old('quantity', $d?->quantity ?? 1) }}">
        @include('don.partials.field-error', ['name' => 'quantity'])
    </div>

    <div>
        <label class="{{ $label }}" for="city">Ville</label>
        <input id="city" name="city" type="text" required maxlength="100" class="{{ $input }}"
               value="{{ old('city', $d?->city) }}">
        @include('don.partials.field-error', ['name' => 'city'])
        <input type="hidden" id="lat" name="lat" value="{{ old('lat') }}">
        <input type="hidden" id="lng" name="lng" value="{{ old('lng') }}">
        <button type="button" id="use-location" class="mt-1 text-xs text-emerald-700 hover:underline">
            Utiliser ma position pour une recommandation plus précise
        </button>
        <p class="mt-1 text-xs text-stone-400">Seule la ville est montrée aux associations avant leur acceptation.</p>
    </div>
</div>

<fieldset class="mt-8">
    <legend class="text-sm font-semibold text-stone-800">Caractéristiques</legend>
    <p class="mt-1 text-sm text-stone-500">
        @if ($required)
            La catégorie et l'état sont nécessaires pour trouver une association.
        @else
            Laissez vide pour que l'IA les déduise de vos photos ; vous pourrez les corriger ensuite.
        @endif
    </p>

    <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['category',  'Catégorie',       Textile::CATEGORIES],
            ['condition', 'État',            Textile::CONDITIONS],
            ['age_group', 'Public',          Textile::AGE_GROUPS],
            ['gender',    'Genre',           Textile::GENDERS],
            ['season',    'Saison',          Textile::SEASONS],
        ] as [$field, $text, $options])
            <div>
                <label class="{{ $label }}" for="{{ $field }}">{{ $text }}</label>
                <select id="{{ $field }}" name="{{ $field }}" class="{{ $input }}"
                        @required($required && in_array($field, ['category', 'condition']))>
                    <option value="">{{ $required ? '— Choisir —' : '— Laisser l\'IA décider —' }}</option>
                    @foreach ($options as $key => $optionLabel)
                        <option value="{{ $key }}" @selected(old($field, $d?->{$field}) === $key)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
                @include('don.partials.field-error', ['name' => $field])
            </div>
        @endforeach

        <div>
            <label class="{{ $label }}" for="size">Taille</label>
            <input id="size" name="size" type="text" list="sizes" maxlength="20" class="{{ $input }}"
                   placeholder="M, 5-6a, 42…" value="{{ old('size', $d?->size) }}">
            @include('don.partials.field-error', ['name' => 'size'])
            <datalist id="sizes">
                @foreach (Textile::SIZE_ORDER as $size)
                    <option value="{{ $size }}">
                @endforeach
            </datalist>
        </div>
    </div>
</fieldset>

<script>
    document.getElementById('use-location')?.addEventListener('click', function () {
        if (!navigator.geolocation) { return; }
        this.textContent = 'Localisation en cours…';
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('lat').value = pos.coords.latitude.toFixed(6);
                document.getElementById('lng').value = pos.coords.longitude.toFixed(6);
                this.textContent = 'Position enregistrée ✓';
            },
            () => { this.textContent = 'Position indisponible : la ville sera utilisée.'; }
        );
    });
</script>
