import { useState } from 'react';
import { Badge } from "@/Components/ui/badge";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Loader2 } from 'lucide-react';

export default function StatusSelect({
    status,
    options = [],
    colorMap = {},
    onStatusChange,
    loading = false
}) {
    const [isOpen, setIsOpen] = useState(false);

    const handleSelect = (value) => {
        if (value !== status && onStatusChange) {
            onStatusChange(value);
        }
        setIsOpen(false);
    };

    const currentColorClass = colorMap[status]
        ? colorMap[status].replace('bg-gray', 'bg-sage').replace('text-gray', 'text-sage')
        : "bg-cream text-charcoal border-linen";

    // Clean up the color class for dynamic usage if needed, or rely on parent passing valid tailwind classes
    // Assuming colorMap values are like 'bg-emerald-500/10 text-emerald-500 ...'

    // Helper to format status label
    const formatLabel = (str) => str?.replace(/_/g, ' ').toUpperCase();

    return (
        <DropdownMenu open={isOpen} onOpenChange={setIsOpen}>
            <DropdownMenuTrigger asChild>
                <div className="cursor-pointer inline-flex">
                    <Badge
                        variant="outline"
                        className={`hover:opacity-80 transition-opacity cursor-pointer flex items-center gap-1 border-transparent ${currentColorClass}`}
                    >
                        {loading && <Loader2 className="h-3 w-3 animate-spin" />}
                        {formatLabel(status)}
                    </Badge>
                </div>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="bg-white border-linen shadow-float rounded-xl">
                {options.map((option) => (
                    <DropdownMenuItem
                        key={option.value}
                        onClick={() => handleSelect(option.value)}
                        className={`cursor-pointer ${status === option.value ? 'bg-cream font-medium' : 'hover:bg-cream'}`}
                    >
                        {option.label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
