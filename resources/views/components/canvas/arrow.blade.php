@props(['element'])

<line x1="{{ $element['start']['x'] }}" y1="{{ $element['start']['y'] }}"
      x2="{{ $element['end']['x'] }}" y2="{{ $element['end']['y'] }}"
      stroke="#facc15" stroke-width="4.5" stroke-linecap="round"
      marker-end="url(#tactiboard-arrowhead)" />
