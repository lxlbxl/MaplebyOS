import { Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-react';

// Helper to decode HTML entities like &laquo; and &raquo;
const decodeHtml = (html) => {
    const txt = document.createElement('textarea');
    txt.innerHTML = html;
    return txt.value;
};

export default function Pagination({ links }) {
    // if (links.length === 3) return null; // Only Previous, 1 page, Next

    return (
        <div className="flex items-center justify-end space-x-2 py-4">
            {links.map((link, key) => (
                <div key={key}>
                    {link.url === null ? (
                        <Button
                            variant="outline"
                            size="sm"
                            className="text-gray-400 cursor-not-allowed"
                            disabled
                        >
                            {decodeHtml(link.label)}
                        </Button>
                    ) : (
                        <Button
                            variant={link.active ? "default" : "outline"}
                            size="sm"
                            asChild
                        >
                            <Link href={link.url}>{decodeHtml(link.label)}</Link>
                        </Button>
                    )}
                </div>
            ))}
        </div>
    );
}

