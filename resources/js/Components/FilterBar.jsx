import { useState, useEffect } from 'react';
import { Search, Filter, X } from 'lucide-react';
import { router } from '@inertiajs/react';
import { Input } from '@/Components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Button } from '@/Components/ui/button';


export default function FilterBar({
    search: initialSearch = '',
    status: initialStatus = 'all',
    onFilterChange, // Optional callback if not using direct routing
    statusOptions = [
        { value: 'all', label: 'All Statuses' },
        { value: 'pending', label: 'Pending' },
        { value: 'active', label: 'Active' },
        { value: 'completed', label: 'Completed' },
    ],
    className = ''
}) {
    const [search, setSearch] = useState(initialSearch);
    const [status, setStatus] = useState(initialStatus);

    // Debounce search update
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== initialSearch) {
                handleFilterUpdate({ search, status });
            }
        }, 500);
        return () => clearTimeout(timer);
    }, [search]);

    const handleStatusChange = (newStatus) => {
        setStatus(newStatus);
        handleFilterUpdate({ search, status: newStatus });
    };

    const handleFilterUpdate = (newFilters) => {
        // If specific callback provided, use it
        if (onFilterChange) {
            onFilterChange(newFilters);
            return;
        }

        // Default to Inertia get request to current URL
        router.get(
            window.location.pathname,
            { ...newFilters },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('all');
        handleFilterUpdate({ search: '', status: 'all' });
    };

    const hasActiveFilters = search || (status && status !== 'all');

    return (
        <div className={`flex flex-col md:flex-row gap-4 items-center bg-white p-4 rounded-xl border border-linen shadow-sm ${className}`}>
            <div className="relative flex-1 w-full">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-pebble w-4 h-4" />
                <Input
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Search..."
                    className="pl-10 bg-cream border-linen focus:bg-white text-charcoal placeholder:text-stone transition-all rounded-lg"
                />
            </div>

            <div className="flex items-center gap-2 w-full md:w-auto">
                <Select value={status} onValueChange={handleStatusChange}>
                    <SelectTrigger className="w-full md:w-[200px] bg-white border-linen text-charcoal rounded-lg">
                        <div className="flex items-center gap-2">
                            <Filter className="w-4 h-4 text-pebble" />
                            <SelectValue placeholder="Filter by status" />
                        </div>
                    </SelectTrigger>
                    <SelectContent className="border-linen shadow-float rounded-xl">
                        {statusOptions.map((option) => (
                            <SelectItem key={option.value} value={option.value} className="focus:bg-cream cursor-pointer">
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                {hasActiveFilters && (
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={clearFilters}
                        className="text-pebble hover:text-error hover:bg-error-soft rounded-full"
                        title="Clear filters"
                    >
                        <X className="w-4 h-4" />
                    </Button>
                )}
            </div>
        </div>
    );
}
