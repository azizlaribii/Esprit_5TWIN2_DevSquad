@php
    $tones = [
        'pending_analysis' => 'bg-amber-100 text-amber-800',
        'needs_review'     => 'bg-orange-100 text-orange-800',
        'matched'          => 'bg-emerald-100 text-emerald-800',
        'no_match'         => 'bg-stone-200 text-stone-700',
        'requested'        => 'bg-blue-100 text-blue-800',
        'accepted'         => 'bg-green-100 text-green-800',
        'completed'        => 'bg-teal-100 text-teal-800',
        'cancelled'        => 'bg-stone-200 text-stone-600',
        'suggested'        => 'bg-stone-100 text-stone-700',
        'rejected'         => 'bg-red-100 text-red-800',
    ];
@endphp
<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tones[$status] ?? 'bg-stone-100 text-stone-700' }}">
    {{ $label }}
</span>
