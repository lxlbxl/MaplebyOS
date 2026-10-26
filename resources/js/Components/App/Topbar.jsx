import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Bell, Search, Menu, User, Settings, Loader2 } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';

export default function Topbar({ onMenuClick }) {
    const { auth } = usePage().props;
    const [isLogoutLoading, setIsLogoutLoading] = useState(false);
    const [isProfileLoading, setIsProfileLoading] = useState(false);
    const [isSettingsLoading, setIsSettingsLoading] = useState(false);

    const handleLogout = () => {
        setIsLogoutLoading(true);
        import('@inertiajs/react').then(({ router }) => {
            router.post(route('logout'));
        });
    };

    const handleProfile = () => setIsProfileLoading(true);
    const handleSettings = () => setIsSettingsLoading(true);

    return (
        <header className="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-white/10 bg-white/5 px-6 backdrop-blur-md">
            <div className="flex items-center gap-4">
                <Button variant="ghost" size="icon" className="lg:hidden" onClick={onMenuClick} aria-label="Open menu">
                    <Menu className="h-5 w-5" />
                </Button>
                <div className="relative hidden w-96 md:block">
                    <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500" />
                    <input
                        type="text"
                        placeholder="Search leads, clients, jobs..."
                        className="h-9 w-full rounded-md border border-white/10 bg-black/20 pl-9 text-sm text-white placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                </div>
            </div>

            <div className="flex items-center gap-4">
                <Button variant="ghost" size="icon" className="text-gray-400 hover:text-white" aria-label="Notifications">
                    <Bell className="h-5 w-5" />
                </Button>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                            <Avatar className="h-8 w-8">
                                <AvatarImage src={auth?.user?.avatar} alt={auth?.user?.name} />
                                <AvatarFallback>{auth?.user?.name?.charAt(0) || 'U'}</AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent className="w-56 bg-black/90 border-white/10 backdrop-blur-lg" align="end" forceMount>
                        <DropdownMenuLabel className="font-normal text-white">
                            <div className="flex flex-col space-y-1">
                                <p className="text-sm font-medium leading-none">{auth?.user?.name}</p>
                                <p className="text-xs leading-none text-muted-foreground">{auth?.user?.email}</p>
                            </div>
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator className="bg-white/5" />
                        <DropdownMenuItem asChild>
                            <Link
                                href={route('profile.edit')}
                                onClick={handleProfile}
                                className="flex w-full items-center text-gray-300 hover:text-white cursor-pointer px-2 py-1.5 rounded-md transition-colors"
                            >
                                {isProfileLoading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <User className="mr-2 h-4 w-4" />}
                                <span>Profile</span>
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <Link
                                href={route('settings.index')}
                                onClick={handleSettings}
                                className="flex w-full items-center text-gray-300 hover:text-white cursor-pointer px-2 py-1.5 rounded-md transition-colors"
                            >
                                {isSettingsLoading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Settings className="mr-2 h-4 w-4" />}
                                <span>Settings</span>
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator className="bg-white/5" />
                        <DropdownMenuItem
                            className="text-red-500 cursor-pointer hover:bg-red-500/10 px-2 py-1.5 rounded-md"
                            onSelect={handleLogout}
                        >
                            {isLogoutLoading ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : null}
                            Log out
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
