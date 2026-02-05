<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash Home - Elite Car Wash</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- React and ReactDOM -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="{{asset('assets/plugins/tabler-icons/tabler-icons.min.css')}}">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
                sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
    
    <!-- Suppress Cloudflare and other 404 errors -->
    <script>
        (function() {
            // Suppress console errors for Cloudflare resources
            const originalError = window.onerror;
            window.onerror = function(msg, url, line, col, error) {
                if (url && (url.includes('cdn-cgi/rum') || url.includes('cdn-cgi/scripts') || url.includes('cloudflare'))) {
                    return true; // Suppress error
                }
                if (originalError) {
                    return originalError.apply(this, arguments);
                }
                return false;
            };
            
            // Suppress fetch errors for Cloudflare
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                const url = args[0];
                if (typeof url === 'string' && (url.includes('cdn-cgi/rum') || url.includes('cloudflare'))) {
                    return Promise.reject(new Error('Suppressed Cloudflare request'));
                }
                return originalFetch.apply(this, args);
            };
            
            // Suppress resource loading errors
            window.addEventListener('error', function(e) {
                if (e.target && e.target.tagName) {
                    const src = e.target.src || e.target.href;
                    if (src && (src.includes('cdn-cgi/rum') || src.includes('cdn-cgi/scripts') || src.includes('cloudflare'))) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                }
            }, true);
        })();
    </script>
</head>
<body>
    <div id="root"></div>
    
    <script type="text/babel">
        const { useState, useEffect } = React;
        
        // API Routes
        const API_ROUTES = {
            jobs: {
                todayStats: '{{ route("car-wash.jobs.today-stats") }}',
                active: '{{ route("car-wash.jobs.active") }}',
                completed: '{{ route("car-wash.jobs.completed") }}',
            },
        };
        
        // Routes
        const ROUTES = {
            carWashHome: '{{ route("employee.home") }}',
            carWash: '{{ route("car.wash") }}',
            attendance: '{{ route("attendance") }}',
            services: '{{ route("car.wash.services") }}',
            staff: '{{ route("car.wash.staff") }}',
            completedJobs: '{{ route("car.wash.completed-jobs") }}',
            dailyReport: '{{ route("car.wash.daily-report") }}',
            attendanceHistory: '{{ route("attendance.history.page") }}',
            shopExpenses: '{{ route("car.wash.all-shop-expenses") }}',
            sale: '{{ route("create.sale") }}',
            profile: '{{ route("employee.profile", auth()->user()->id) }}',
        };
        
        // User and Branch Info
        const branchName = @json($branchName ?? 'No Branch');
        const userName = @json($userName ?? 'Guest');
        
        // Icon Components using Tabler Icons
        const createIcon = (iconClass, props) => {
            const iconSize = props.size || 20;
            const className = props.className || "";
            const styleObj = {};
            styleObj.fontSize = iconSize + 'px';
            return React.createElement('i', { 
                className: iconClass + ' ' + className, 
                style: styleObj
            });
        };
        
        const LayoutDashboard = (props) => createIcon('ti ti-layout-dashboard', props);
        const Package = (props) => createIcon('ti ti-package', props);
        const Users = (props) => createIcon('ti ti-users', props);
        const Settings = (props) => createIcon('ti ti-settings', props);
        const Bell = (props) => createIcon('ti ti-bell', props);
        const Search = (props) => createIcon('ti ti-search', props);
        const Menu = (props) => createIcon('ti ti-menu-2', props);
        const X = (props) => createIcon('ti ti-x', props);
        const ShoppingCart = (props) => createIcon('ti ti-shopping-cart', props);
        const Car = (props) => createIcon('ti ti-car', props);
        const Handshake = (props) => createIcon('ti ti-handshake', props);
        const BellRing = (props) => createIcon('ti ti-bell-ringing', props);
        const BarChart3 = (props) => createIcon('ti ti-chart-bar', props);
        const UserPlus = (props) => createIcon('ti ti-user-plus', props);
        const ChevronRight = (props) => createIcon('ti ti-chevron-right', props);
        const Clock = (props) => createIcon('ti ti-clock', props);
        const Calendar = (props) => createIcon('ti ti-calendar', props);
        const Lock = (props) => createIcon('ti ti-lock', props);
        const User = (props) => createIcon('ti ti-user', props);
        const LogOut = (props) => createIcon('ti ti-logout', props);
        const Fingerprint = (props) => createIcon('ti ti-fingerprint', props);
        const Grid3X3 = (props) => createIcon('ti ti-grid-3x3', props);
        const KeyRound = (props) => createIcon('ti ti-key', props);
        const UserCircle = (props) => createIcon('ti ti-user-circle', props);
        
        const App = () => {
            // No login state needed - user is already authenticated via Laravel middleware

            // Dashboard State
            const [isSidebarOpen, setSidebarOpen] = useState(false);
            const [activeTab, setActiveTab] = useState('Home');
            const [dateTime, setDateTime] = useState(new Date());
            const [stats, setStats] = useState({
                todayRevenue: 0,
                todayJobsCount: 0,
                activeJobsCount: 0,
                todayExpenses: 0,
                netProfit: 0,
                totalWorkers: {{ $totalWorkers ?? 0 }},
                totalServices: {{ $totalServices ?? 0 }}
            });
            const [loading, setLoading] = useState(true);
            
            // Update time every second
            useEffect(() => {
                const timer = setInterval(() => setDateTime(new Date()), 1000);
                return () => clearInterval(timer);
            }, []);
            
            // Load stats
            useEffect(() => {
                const loadStats = async () => {
                    try {
                        const todayStatsRes = await fetch(API_ROUTES.jobs.todayStats);
                        const todayStatsData = await todayStatsRes.json();
                        
                        const activeJobsRes = await fetch(API_ROUTES.jobs.active);
                        const activeJobsData = await activeJobsRes.json();
                        
                        if (todayStatsData.success) {
                            setStats(prev => ({
                                ...prev,
                                todayRevenue: todayStatsData.stats.todayRevenue || 0,
                                todayJobsCount: todayStatsData.stats.todayJobsCount || 0,
                                activeJobsCount: activeJobsData.success ? (activeJobsData.jobs?.length || 0) : 0,
                                todayExpenses: 0,
                                netProfit: (todayStatsData.stats.todayRevenue || 0) - 0
                            }));
                        }
                    } catch (error) {
                        console.error('Error loading stats:', error);
                    } finally {
                        setLoading(false);
                    }
                };
                
                loadStats();
            }, []);
            
            const handleLogout = () => {
                // Submit logout form
                const form = document.getElementById('employee-logout-form');
                if (form) {
                    form.submit();
                } else {
                    // Fallback: redirect to main login (which is now employee login)
                    window.location.href = '{{ route("login") }}';
                }
            };

            const formatTime = (date) => {
                return date.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
            };

            const formatDate = (date) => {
                return date.toLocaleDateString('en-GB', { 
                    day: '2-digit', 
                    month: 'short', 
                    year: 'numeric' 
                });
            };

            const getDayName = (date) => {
                return date.toLocaleDateString('en-GB', { weekday: 'long' });
            };

            // Main Dashboard
            const navItems = [
                { name: 'Home', icon: LayoutDashboard, route: ROUTES.carWashHome },
                { name: 'Services', icon: Package, route: ROUTES.services },
                { name: 'Staff', icon: Users, route: ROUTES.staff },
                { name: 'Settings', icon: Settings, route: '#' },
            ];

            const quickActions = [
                { label: "Elite Wash", icon: Car, color: "bg-cyan-500", shadow: "shadow-cyan-50", desc: "Premium Cleaning", route: ROUTES.carWash },
                { label: "Sale", icon: Handshake, color: "bg-orange-500", shadow: "shadow-orange-50", desc: "Battery Sale", route: ROUTES.sale },
                { label: "Reports", icon: BarChart3, color: "bg-indigo-600", shadow: "shadow-indigo-50", desc: "Accounts", route: ROUTES.dailyReport },
            ];

            return (
                React.createElement('div', { className: 'min-h-screen bg-slate-50 flex flex-col md:flex-row font-sans text-slate-900' },
                    // Mobile Header
                    React.createElement('div', { className: 'md:hidden bg-white border-b px-4 py-3 flex justify-between items-center sticky top-0 z-50' },
                        React.createElement('h1', { className: 'font-bold text-blue-700 text-lg tracking-tight uppercase italic' }, 'Elite Car Wash'),
                        React.createElement('button', { 
                            onClick: () => setSidebarOpen(!isSidebarOpen), 
                            className: 'p-2 bg-slate-100 rounded-xl text-slate-600' 
                        },
                            isSidebarOpen ? React.createElement(X, { size: 20 }) : React.createElement(Menu, { size: 20 })
                        )
                    ),

                    // Sidebar
                    React.createElement('aside', { 
                        className: 'fixed inset-y-0 left-0 z-40 w-64 bg-white border-r transform transition-transform duration-300 ease-in-out ' + 
                                   (isSidebarOpen ? 'translate-x-0' : '-translate-x-full') + 
                                   ' md:translate-x-0 md:static' 
                    },
                        React.createElement('div', { className: 'h-full flex flex-col' },
                            React.createElement('div', { className: 'p-6 hidden md:block text-center border-b mb-4' },
                                React.createElement('h1', { className: 'text-xl font-black text-blue-700 tracking-tighter italic' }, 'Employee')
                            ),
                            React.createElement('nav', { className: 'flex-1 px-3 space-y-1' },
                                navItems.map((item) => 
                                    React.createElement('a', {
                                        key: item.name,
                                        href: item.route,
                                        onClick: () => setSidebarOpen(false),
                                        className: 'w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all ' +
                                                   (activeTab === item.name ? 'bg-blue-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50')
                                    },
                                        React.createElement(item.icon, { size: 18 }),
                                        React.createElement('span', { className: 'font-bold text-sm' }, item.name)
                                    )
                                )
                            ),
                            React.createElement('div', { className: 'p-4 border-t mt-auto space-y-2' },
                                React.createElement('div', { className: 'bg-slate-50 rounded-xl p-3 flex items-center space-x-3 border border-slate-100' },
                                    React.createElement('div', { className: 'w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xs font-bold capitalize' },
                                        userName.charAt(0).toUpperCase()
                                    ),
                                    React.createElement('div', {},
                                        React.createElement('p', { className: 'text-xs font-bold truncate text-slate-800 tracking-tight capitalize' }, userName),
                                        React.createElement('p', { className: 'text-[9px] text-slate-400 font-bold uppercase italic' }, branchName)
                                    )
                                ),
                                React.createElement('a', {
                                    href: ROUTES.profile,
                                    className: 'w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-blue-600 hover:bg-blue-50 transition-all font-bold text-sm'
                                },
                                    React.createElement(UserCircle, { size: 18 }),
                                    React.createElement('span', {}, 'My Profile')
                                ),
                                React.createElement('button', {
                                    onClick: handleLogout,
                                    className: 'w-full flex items-center space-x-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 transition-all font-bold text-sm'
                                },
                                    React.createElement(LogOut, { size: 18 }),
                                    React.createElement('span', {}, 'Logout')
                                )
                            )
                        )
                    ),

                    // Main Content
                    React.createElement('main', { className: 'flex-1 flex flex-col overflow-x-hidden' },
                        // Top Navbar
                        React.createElement('header', { className: 'hidden md:flex bg-white border-b h-16 items-center justify-between px-8 sticky top-0 z-30' },
                            React.createElement('div', { className: 'relative w-72' },
                                React.createElement(Search, { className: 'absolute left-3 top-1/2 -translate-y-1/2 text-slate-400', size: 16 }),
                                React.createElement('input', {
                                    type: 'text',
                                    placeholder: 'Quick search...',
                                    className: 'w-full bg-slate-50 border-slate-100 border rounded-xl py-2 pl-10 pr-4 text-xs focus:ring-2 focus:ring-blue-500 outline-none'
                                })
                            ),
                            React.createElement('div', { className: 'flex items-center space-x-3' },
                                React.createElement('button', { className: 'p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-blue-50 transition-all relative border border-slate-100' },
                                    React.createElement(Bell, { size: 18 }),
                                    React.createElement('span', { className: 'absolute top-2.5 right-2.5 w-1.5 h-1.5 bg-red-500 rounded-full border border-white' })
                                )
                            )
                        ),

                        // Content Body
                        React.createElement('div', { className: 'p-4 md:p-6 max-w-6xl w-full mx-auto flex-1' },
                            // Date and Time Cards
                            React.createElement('div', { className: 'mb-8 flex flex-wrap items-center gap-3 md:gap-4 justify-center md:justify-start' },
                                React.createElement('div', { className: 'bg-white border border-slate-200 px-4 py-2.5 rounded-2xl flex items-center space-x-3 shadow-sm' },
                                    React.createElement('div', { className: 'bg-emerald-50 p-1.5 rounded-lg' },
                                        React.createElement(Calendar, { size: 16, className: 'text-emerald-600' })
                                    ),
                                    React.createElement('div', { className: 'flex flex-col text-left' },
                                        React.createElement('span', { className: 'text-[11px] font-black text-slate-800 uppercase leading-none mb-0.5' }, formatDate(dateTime)),
                                        React.createElement('span', { className: 'text-[9px] text-slate-400 font-bold uppercase leading-none' }, getDayName(dateTime))
                                    )
                                ),
                                React.createElement('div', { className: 'bg-white border border-slate-200 px-4 py-2.5 rounded-2xl flex items-center space-x-3 shadow-sm' },
                                    React.createElement('div', { className: 'bg-blue-50 p-1.5 rounded-lg' },
                                        React.createElement(Clock, { size: 16, className: 'text-blue-600' })
                                    ),
                                    React.createElement('div', { className: 'flex flex-col text-left' },
                                        React.createElement('span', { className: 'text-[11px] font-black text-slate-800 uppercase tabular-nums leading-none mb-0.5' }, formatTime(dateTime)),
                                        React.createElement('span', { className: 'text-[9px] text-slate-400 font-bold uppercase leading-none italic' }, 'System Online')
                                    )
                                )
                            ),

                            // Quick Actions
                            React.createElement('div', { className: 'px-1' },
                                React.createElement('div', { className: 'flex items-center justify-between mb-6' },
                                    React.createElement('p', { className: 'text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] italic' }, 'Operational Dashboard'),
                                    React.createElement('div', { className: 'h-[2px] bg-slate-100 flex-1 ml-4 rounded-full' })
                                ),
                                React.createElement('div', { className: 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4' },
                                    quickActions.map((action, index) =>
                                        React.createElement('a', {
                                            key: index,
                                            href: action.route,
                                            className: 'group relative overflow-hidden flex flex-col items-center justify-center text-center p-4 md:p-5 rounded-3xl transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl active:scale-95 shadow-sm border border-transparent ' + action.color + ' ' + action.shadow
                                        },
                                            React.createElement('div', { className: 'absolute -top-3 -right-3 w-10 h-10 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500' }),
                                            React.createElement('div', { className: 'bg-white/20 p-3 rounded-2xl mb-2 backdrop-blur-sm group-hover:bg-white/30 transition-colors' },
                                                React.createElement(action.icon, { size: 22, className: 'text-white', strokeWidth: 2.5 })
                                            ),
                                            React.createElement('span', { className: 'text-xs font-black text-white uppercase tracking-tight' }, action.label),
                                            React.createElement('span', { className: 'text-white/60 text-[9px] font-bold uppercase mt-0.5 tracking-widest hidden sm:block' }, action.desc)
                                        )
                                    )
                                )
                            )
                        )
                    ),

                    // Backdrop for mobile sidebar
                    isSidebarOpen && React.createElement('div', {
                        className: 'fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-30 md:hidden',
                        onClick: () => setSidebarOpen(false)
                    })
                )
            );
        };
        
        // Render the app
        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(React.createElement(App));
    </script>
    
    <!-- Hidden logout form for employee -->
    <form id="employee-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    
</body>
</html>
