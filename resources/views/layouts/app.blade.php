<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased overflow-x-hidden">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow relative z-10">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="relative max-w-7xl mx-auto">

                <!-- 100% Fixed Notification (Moved 2px higher to top: 4px and made slimmer) -->
                <div id="global-toast-wrapper" class="absolute right-4 sm:right-6 lg:right-8 flex flex-col gap-2" style="top: 4px; z-index: 99999; min-width: 300px; max-width: 440px;">

                    @if(session('success'))
                        <div id="global-toast-success" class="flex items-center justify-between bg-white border border-gray-200 border-l-4 border-l-green-500 rounded-lg shadow-lg transition-all duration-300" style="padding: 6px 14px;">
                            <div class="flex items-center" style="gap: 12px;">
                                <div class="bg-green-100 text-green-600 rounded-full" style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div class="text-sm font-bold text-gray-800 leading-tight" style="word-break: break-word;">{{ session('success') }}</div>
                            </div>
                            <button type="button"
                                onclick="var el = document.getElementById('global-toast-success'); if(el){ el.style.opacity='0'; el.style.transform='translateY(-10px)'; setTimeout(function(){ el.remove(); }, 250); }"
                                onmouseover="this.style.backgroundColor='#fee2e2'; this.style.color='#dc2626';"
                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='#9ca3af';"
                                style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; margin-left: 12px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: #9ca3af; background: transparent; border: none; cursor: pointer; flex-shrink: 0; transition: all 0.15s ease;"
                                title="Close">
                                <svg style="width: 14px; height: 14px; pointer-events: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div id="global-toast-error" class="flex items-center justify-between bg-white border border-gray-200 border-l-4 border-l-red-500 rounded-lg shadow-lg transition-all duration-300" style="padding: 6px 14px;">
                            <div class="flex items-center" style="gap: 12px;">
                                <div class="bg-red-100 text-red-600 rounded-full" style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                                <div class="text-sm font-bold text-gray-800 leading-tight" style="word-break: break-word;">{{ session('error') }}</div>
                            </div>
                            <button type="button"
                                onclick="var el = document.getElementById('global-toast-error'); if(el){ el.style.opacity='0'; el.style.transform='translateY(-10px)'; setTimeout(function(){ el.remove(); }, 250); }"
                                onmouseover="this.style.backgroundColor='#fee2e2'; this.style.color='#dc2626';"
                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='#9ca3af';"
                                style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; margin-left: 12px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: #9ca3af; background: transparent; border: none; cursor: pointer; flex-shrink: 0; transition: all 0.15s ease;"
                                title="Close">
                                <svg style="width: 14px; height: 14px; pointer-events: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endif

                </div>

                <!-- Page Content Slot -->
                <div class="w-full">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Global Scripts: Auto Dismiss Toast & Smart Title-Case Capitalization Everywhere -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Auto-hide notifications after 5 seconds
                ['global-toast-success', 'global-toast-error'].forEach(function(id) {
                    const el = document.getElementById(id);
                    if (el) {
                        setTimeout(function() {
                            if (document.body.contains(el)) {
                                el.style.opacity = '0';
                                el.style.transform = 'translateY(-10px)';
                                setTimeout(() => el.remove(), 300);
                            }
                        }, 5000);
                    }
                });

                // 2. Global Smart Title Case (Capitalize First Letter of Every Word)
                function toSmartTitleCase(str) {
                    return str.toLowerCase().replace(/(?:^|\s|[-/(\[{])\S/g, function(match) {
                        return match.toUpperCase();
                    });
                }

                function shouldCapitalizeField(el) {
                    if (!el) return false;
                    const tag = el.tagName.toLowerCase();
                    if (tag !== 'input' && tag !== 'textarea') return false;
                    if (el.readOnly || el.disabled) return false;

                    const type = (el.getAttribute('type') || 'text').toLowerCase();
                    if (tag === 'input' && type !== 'text' && type !== 'search') return false;

                    const name = (el.getAttribute('name') || '').toLowerCase();
                    const id = (el.getAttribute('id') || '').toLowerCase();
                    const excluded = ['password', 'device_password', 'imei', 'transaction_id', 'email', 'tracking_code', 'rack_number', 'drawer_number'];

                    for (let i = 0; i < excluded.length; i++) {
                        if (name.includes(excluded[i]) || id.includes(excluded[i])) {
                            return false;
                        }
                    }
                    return true;
                }

                document.addEventListener('input', function(e) {
                    const el = e.target;
                    if (shouldCapitalizeField(el)) {
                        const start = el.selectionStart;
                        const end = el.selectionEnd;
                        const formatted = toSmartTitleCase(el.value);
                        if (el.value !== formatted) {
                            el.value = formatted;
                            if (start !== null && end !== null) {
                                el.setSelectionRange(start, end);
                            }
                        }
                    }
                });
            });
        </script>
    </body>
</html>
