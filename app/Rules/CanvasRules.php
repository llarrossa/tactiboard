<?php

namespace App\Rules;

use App\Enums\CanvasElementType;
use App\Enums\PlayerTeam;
use Illuminate\Validation\Rule;

/**
 * Ponto unico de verdade sobre o formato de canvas_data.
 *
 * O salvamento do canvas acontece pelo Livewire, que valida dentro do
 * componente e nao passa por Form Request. Para que isso nao vire regra
 * espalhada, as regras vivem aqui e sao consumidas por quem precisar validar
 * um canvas — hoje o BoardEditor, amanha a visualizacao publica ou uma
 * importacao. Decisao registrada em docs/04 secao 8.3.
 *
 * O schema completo esta documentado em docs/03 secao 6.1.
 */
class CanvasRules
{
    /**
     * Sistema de coordenadas do canvas: o proprio gramado, em decimetros
     * (105 m x 68 m). Independe da resolucao da tela — o SVG escala por CSS.
     */
    public const FIELD_WIDTH = 1050;

    public const FIELD_HEIGHT = 680;

    /**
     * Limite de elementos por prancheta. Existe para evitar que um payload
     * adulterado grave um JSON arbitrariamente grande na coluna.
     */
    public const MAX_ELEMENTS = 100;

    public const MAX_TEXT_LENGTH = 120;

    /**
     * Regras de validacao dos elementos do canvas.
     *
     * As chaves partem de `elements` porque e assim que a propriedade se chama
     * no componente Livewire e tambem como o array e persistido.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        // As chaves especificas de cada tipo sao *excluidas* da validacao
        // quando o tipo nao as usa, em vez de apenas deixarem de ser exigidas.
        // A diferenca importa: um cone que carrega um `team` invalido, sobra de
        // quando o elemento era jogador, deve ser silenciosamente descartado
        // por normalize() — nao reprovar o salvamento inteiro por causa de uma
        // chave que nem sera gravada.
        $exceptArrow = 'exclude_if:elements.*.type,'.CanvasElementType::Arrow->value;
        $onlyArrow = 'exclude_unless:elements.*.type,'.CanvasElementType::Arrow->value;
        $onlyPlayer = 'exclude_unless:elements.*.type,'.CanvasElementType::Player->value;
        $onlyText = 'exclude_unless:elements.*.type,'.CanvasElementType::Text->value;

        return [
            'elements' => ['present', 'array', 'max:'.self::MAX_ELEMENTS],

            // O id identifica o elemento entre uma edicao e outra. Sem ele a
            // remocao no meio da lista faria o Livewire reaproveitar o indice
            // e trocar um elemento por outro na tela.
            'elements.*.id' => ['required', 'string', 'max:36', 'distinct'],
            'elements.*.type' => ['required', Rule::enum(CanvasElementType::class)],

            'elements.*.x' => [$exceptArrow, 'required', 'numeric', 'between:0,'.self::FIELD_WIDTH],
            'elements.*.y' => [$exceptArrow, 'required', 'numeric', 'between:0,'.self::FIELD_HEIGHT],

            'elements.*.team' => [$onlyPlayer, 'required', Rule::enum(PlayerTeam::class)],
            'elements.*.number' => [$onlyPlayer, 'required', 'integer', 'between:1,99'],

            'elements.*.content' => [$onlyText, 'required', 'string', 'max:'.self::MAX_TEXT_LENGTH],

            'elements.*.start' => [$onlyArrow, 'required', 'array'],
            'elements.*.start.x' => [$onlyArrow, 'required', 'numeric', 'between:0,'.self::FIELD_WIDTH],
            'elements.*.start.y' => [$onlyArrow, 'required', 'numeric', 'between:0,'.self::FIELD_HEIGHT],
            'elements.*.end' => [$onlyArrow, 'required', 'array'],
            'elements.*.end.x' => [$onlyArrow, 'required', 'numeric', 'between:0,'.self::FIELD_WIDTH],
            'elements.*.end.y' => [$onlyArrow, 'required', 'numeric', 'between:0,'.self::FIELD_HEIGHT],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'elements.max' => __('A board holds at most :max elements.', ['max' => self::MAX_ELEMENTS]),
            'elements.*.id.distinct' => __('Each element must have its own identifier.'),
            'elements.*.type' => __('This element type does not exist.'),
            'elements.*.x.between' => __('The element is outside the field.'),
            'elements.*.y.between' => __('The element is outside the field.'),
            'elements.*.start.x.between' => __('The element is outside the field.'),
            'elements.*.start.y.between' => __('The element is outside the field.'),
            'elements.*.end.x.between' => __('The element is outside the field.'),
            'elements.*.end.y.between' => __('The element is outside the field.'),
            'elements.*.number.between' => __('A player number goes from 1 to 99.'),
            'elements.*.content.max' => __('A text holds at most :max characters.', ['max' => self::MAX_TEXT_LENGTH]),
        ];
    }

    /**
     * Prende um ponto dentro do campo.
     *
     * Arrastar um elemento para fora da linha lateral nao deve virar erro de
     * validacao: o elemento simplesmente para na borda.
     *
     * @return array{x: float, y: float}
     */
    public static function clamp(float $x, float $y): array
    {
        return [
            'x' => self::round(max(0, min($x, self::FIELD_WIDTH))),
            'y' => self::round(max(0, min($y, self::FIELD_HEIGHT))),
        ];
    }

    /**
     * Filtra os elementos que podem ser desenhados com seguranca.
     *
     * `elements` e propriedade publica do componente Livewire, entao o
     * navegador pode devolver qualquer coisa nela. Sem esse filtro, um
     * elemento malformado derruba a renderizacao do editor inteiro — o usuario
     * receberia um erro de servidor no lugar da mensagem de validacao.
     *
     * A checagem reaproveita rules() em vez de repetir o schema: uma unica
     * passada do validator diz quais indices estao quebrados.
     *
     * @param  array<int, mixed>  $elements
     * @return array<int, array<string, mixed>>
     */
    public static function drawable(array $elements): array
    {
        $elements = array_values($elements);

        $broken = [];

        foreach (validator(['elements' => $elements], self::rules())->errors()->keys() as $key) {
            $index = explode('.', $key)[1] ?? null;

            if (is_numeric($index)) {
                $broken[(int) $index] = true;
            }
        }

        return array_values(array_filter(
            $elements,
            fn (int $index): bool => ! isset($broken[$index]),
            ARRAY_FILTER_USE_KEY
        ));
    }

    /**
     * Devolve os elementos com apenas as chaves que cada tipo usa.
     *
     * O canvas chega do navegador, entao pode vir com chaves a mais — seja por
     * adulteracao, seja por sobra de uma edicao anterior (um jogador que virou
     * cone carregaria `number` para sempre). Normalizar antes de gravar mantem
     * o JSON limpo, como pede docs/06 secao 12.
     *
     * @param  array<int, array<string, mixed>>  $elements
     * @return array<int, array<string, mixed>>
     */
    public static function normalize(array $elements): array
    {
        return array_values(array_map(function (array $element): array {
            $type = CanvasElementType::from($element['type']);

            $normalized = [
                'id' => (string) $element['id'],
                'type' => $type->value,
            ];

            if ($type->isPositional()) {
                $normalized['x'] = self::round($element['x']);
                $normalized['y'] = self::round($element['y']);
            }

            return [...$normalized, ...match ($type) {
                CanvasElementType::Player => [
                    'team' => $element['team'],
                    'number' => (int) $element['number'],
                ],
                CanvasElementType::Text => [
                    'content' => (string) $element['content'],
                ],
                CanvasElementType::Arrow => [
                    'start' => [
                        'x' => self::round($element['start']['x']),
                        'y' => self::round($element['start']['y']),
                    ],
                    'end' => [
                        'x' => self::round($element['end']['x']),
                        'y' => self::round($element['end']['y']),
                    ],
                ],
                default => [],
            }];
        }, $elements));
    }

    /**
     * O arrastar produz fracoes longas de pixel convertido. Uma casa decimal
     * ja e mais fina que o olho no campo e evita inflar o JSON.
     */
    private static function round(mixed $value): float
    {
        return round((float) $value, 1);
    }
}
