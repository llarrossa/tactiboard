@props(['element', 'selected' => false])

<line x1="{{ $element['start']['x'] }}" y1="{{ $element['start']['y'] }}"
      x2="{{ $element['end']['x'] }}" y2="{{ $element['end']['y'] }}"
      stroke="#facc15" stroke-width="4.5" stroke-linecap="round"
      marker-end="url(#tactiboard-arrowhead)" />

@if ($selected)
    {{-- Alcas das pontas. O `.stop` impede que o arrasto da alca vire tambem
         um arrasto da seta inteira, que e o que o <g> em volta escuta. --}}
    @foreach (['start', 'end'] as $end)
        <circle cx="{{ $element[$end]['x'] }}" cy="{{ $element[$end]['y'] }}" r="11"
                fill="#facc15" stroke="#111827" stroke-width="2.5"
                x-on:pointerdown.stop.prevent="startDrag($event, @js($element['id']), @js($end))"
                style="cursor: grab" />
    @endforeach
@endif
