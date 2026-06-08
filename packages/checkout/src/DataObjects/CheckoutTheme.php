<?php

namespace Lunar\Checkout\DataObjects;

use InvalidArgumentException;

/**
 * Swappable checkout theme.
 *
 * Carries only *overrides* of the design-system's semantic tokens — the shipped
 * `checkout.css` is the single source of truth for default values, so every
 * property defaults to null ("use the CSS default"). {@see tokens()} emits only
 * the tokens that were explicitly set.
 *
 * Bound in the container ({@see \Lunar\Checkout\CheckoutServiceProvider}); a
 * consumer re-brands by binding their own instance in a service provider. Never
 * driven by config — config is for values, the container is for substitutions.
 */
final class CheckoutTheme
{
    /**
     * token property => [css custom property, value type].
     *
     * The property set IS the allowlist: a token that is not here cannot be
     * emitted, and {@see tokens()} validates each value against its type.
     *
     * @var array<string, array{0: string, 1: 'color'|'length'|'font'}>
     */
    private const TOKENS = [
        'accent' => ['--accent', 'color'],
        'accentHover' => ['--accent-hover', 'color'],
        'accentPress' => ['--accent-press', 'color'],
        'bgPage' => ['--bg-page', 'color'],
        'bgSurface' => ['--bg-surface', 'color'],
        'fgPrimary' => ['--fg-primary', 'color'],
        'fgSecondary' => ['--fg-secondary', 'color'],
        'border' => ['--border', 'color'],
        'radiusMd' => ['--radius-md', 'length'],
        'radiusLg' => ['--radius-lg', 'length'],
        'fontSans' => ['--font-sans', 'font'],
        'fontMono' => ['--font-mono', 'font'],
    ];

    public function __construct(
        public readonly ?string $accent = null,
        public readonly ?string $accentHover = null,
        public readonly ?string $accentPress = null,
        public readonly ?string $bgPage = null,
        public readonly ?string $bgSurface = null,
        public readonly ?string $fgPrimary = null,
        public readonly ?string $fgSecondary = null,
        public readonly ?string $border = null,
        public readonly ?string $radiusMd = null,
        public readonly ?string $radiusLg = null,
        public readonly ?string $fontSans = null,
        public readonly ?string $fontMono = null,
    ) {}

    /**
     * The default Tender brand: all-null, i.e. the values shipped in checkout.css.
     */
    public static function tender(): self
    {
        return new self;
    }

    /**
     * Return a copy with the given tokens replaced. Only non-null arguments
     * override; everything else is carried over.
     */
    public function with(
        ?string $accent = null,
        ?string $accentHover = null,
        ?string $accentPress = null,
        ?string $bgPage = null,
        ?string $bgSurface = null,
        ?string $fgPrimary = null,
        ?string $fgSecondary = null,
        ?string $border = null,
        ?string $radiusMd = null,
        ?string $radiusLg = null,
        ?string $fontSans = null,
        ?string $fontMono = null,
    ): self {
        return new self(
            accent: $accent ?? $this->accent,
            accentHover: $accentHover ?? $this->accentHover,
            accentPress: $accentPress ?? $this->accentPress,
            bgPage: $bgPage ?? $this->bgPage,
            bgSurface: $bgSurface ?? $this->bgSurface,
            fgPrimary: $fgPrimary ?? $this->fgPrimary,
            fgSecondary: $fgSecondary ?? $this->fgSecondary,
            border: $border ?? $this->border,
            radiusMd: $radiusMd ?? $this->radiusMd,
            radiusLg: $radiusLg ?? $this->radiusLg,
            fontSans: $fontSans ?? $this->fontSans,
            fontMono: $fontMono ?? $this->fontMono,
        );
    }

    /**
     * Validated, sanitised map of CSS custom property => value for the tokens
     * that were set. This is the single chokepoint between theme values and the
     * client; an invalid value throws here rather than reaching the CSSOM.
     *
     * @return array<string, string>
     */
    public function tokens(): array
    {
        $out = [];

        foreach (self::TOKENS as $property => [$var, $type]) {
            $value = $this->{$property};

            if ($value === null) {
                continue;
            }

            $out[$var] = $this->sanitise($var, $value, $type);
        }

        return $out;
    }

    /**
     * Reject anything that could break out of a CSS declaration, then validate
     * against the token's value type.
     */
    private function sanitise(string $var, string $value, string $type): string
    {
        $value = trim($value);

        if (preg_match('/[;{}<>"]|url\(/i', $value)) {
            throw new InvalidArgumentException("Illegal character in checkout theme token [{$var}].");
        }

        $valid = match ($type) {
            'color' => $this->isColor($value),
            'length' => $this->isLength($value),
            'font' => $this->isFontStack($value),
        };

        if (! $valid) {
            throw new InvalidArgumentException("Invalid {$type} value for checkout theme token [{$var}].");
        }

        return $value;
    }

    private function isColor(string $value): bool
    {
        return (bool) preg_match(
            '/^(#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})|rgba?\([0-9.,%\s]+\)|hsla?\([0-9.,%\s]+\))$/i',
            $value,
        );
    }

    private function isLength(string $value): bool
    {
        return (bool) preg_match('/^(0|-?[0-9]*\.?[0-9]+(px|rem|em|%|vh|vw))$/', $value);
    }

    private function isFontStack(string $value): bool
    {
        // Letters, digits, spaces, commas, hyphens and single quotes only —
        // double quotes / semicolons are already rejected above.
        return (bool) preg_match("/^[a-z0-9 ,'\-]+$/i", $value);
    }
}
