import { cn } from "@/lib/utils";

export default function CurrencyDisplay({
    amount,
    currency = 'USD',
    locale = 'en-US',
    className,
    symbolClassName,
    amountClassName,
    autoCompact = true, // Automatically compact numbers >= 1M
    compactThreshold = 1000000
}) {
    const value = Number(amount) || 0;
    const shouldCompact = autoCompact && Math.abs(value) >= compactThreshold;

    const formatter = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency,
        notation: shouldCompact ? 'compact' : 'standard',
        // For compact, we rarely want many decimals (e.g. $1.5M is better than $1.52M usually, or maybe 2 significant digits)
        // Default compact usually does 0-1 fraction digits. Let's stick to default or slightly refined.
        maximumFractionDigits: shouldCompact ? 1 : 2,
        minimumFractionDigits: shouldCompact ? 0 : 2,
    });

    const parts = formatter.formatToParts(value);

    // Extract the symbol and the rest of the string
    const symbolPart = parts.find(p => p.type === 'currency');
    const symbol = symbolPart ? symbolPart.value : '$';

    // We want to reconstruct the string WITHOUT the currency symbol, to style it separately.
    // However, we want to keep the "M" or "K" suffix attached to the number.
    const amountParts = parts.filter(p => p.type !== 'currency' && p.type !== 'literal' || (p.type === 'literal' && p.value.trim() !== ''));
    // Note: 'literal' might be the space between symbol and number in some locales.
    // A simpler way: just formatting without currency style, but adding 'compact' might require 'currency' style in some implementations?
    // Actually, simple Decimal formatting with compact notation works for the number part.

    const numberFormatter = new Intl.NumberFormat(locale, {
        notation: shouldCompact ? 'compact' : 'standard',
        maximumFractionDigits: shouldCompact ? 1 : 2,
        minimumFractionDigits: shouldCompact ? 0 : 2,
    });

    const formattedNumber = numberFormatter.format(value);

    return (
        <span className={cn("inline-flex items-baseline", className)}>
            <span className={cn("text-[0.6em] opacity-70 mr-[0.1em] font-sans font-medium mb-auto self-start bg-black/5 rounded px-1 py-0.5 leading-none", symbolClassName)}>
                {symbol}
            </span>
            <span className={cn("tracking-tight leading-none", amountClassName)}>
                {formattedNumber}
            </span>
        </span>
    );
}
