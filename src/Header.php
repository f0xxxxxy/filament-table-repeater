<?php

namespace Awcodes\TableRepeater;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class Header
{
    use EvaluatesClosures;

    final public function __construct(
        public string $name,
        public string | Htmlable | Closure | null $label = null,
        public string | Closure | Alignment | null $align = null,
        public string | Closure | null $width = null,
        public int | Closure | null $colspan = null,
        public bool | Closure | null $isRequired = null,
    ){}

    public static function make(string $name): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function label(string | Htmlable | Closure $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function align(string | Closure | Alignment $align): static
    {
        $this->align = $align;

        return $this;
    }

    public function width(string | Closure $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function colspan(int | Closure $colspan): static
    {
        $this->colspan = $colspan;

        return $this;
    }

    public function markAsRequired(bool | Closure | null $condition = true): static
    {
        $this->isRequired = $condition;

        return $this;
    }

    public function getLabel(): string | Htmlable
    {
        return $this->evaluate($this->label)
            ?? (string) Str::of($this->name)->title();
    }

    public function getAlignment(): string | Alignment
    {
        return $this->evaluate($this->align)
            ?? Alignment::Start;
    }

    public function getWidth(): string
    {
        return $this->evaluate($this->width)
            ?? 'auto';
    }

    public function getColspan(): int
    {
        return $this->evaluate($this->colspan)
            ?? 1;
    }
    
    public function isRequired(): bool
    {
        return $this->evaluate($this->isRequired)
            ?? false;
    }
}
