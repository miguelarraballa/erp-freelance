<x-filament-panels::page>
    <div class="space-y-3">
        @foreach ($pluginsMeta as $plugin)
            @php
                $key  = str_replace('-', '_', $plugin['id']);
                $prop = 'states.' . $key;
            @endphp

            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center justify-between gap-6 px-6 py-4">

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-950 dark:text-white">
                            {{ $plugin['name'] }}
                        </p>
                        @if (!empty($plugin['description']))
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                {{ $plugin['description'] }}
                            </p>
                        @endif
                    </div>

                    <div
                        x-data="{ on: @entangle($prop).live }"
                        class="flex flex-shrink-0 items-center gap-3"
                    >
                        <span
                            class="text-sm font-medium"
                            x-text="on ? 'Activo' : 'Desactivado'"
                            :class="on ? 'text-success-600 dark:text-success-400' : 'text-gray-400 dark:text-gray-500'"
                        ></span>

                        <button
                            type="button"
                            @click="on = !on"
                            :class="on ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                            :aria-checked="on.toString()"
                            role="switch"
                        >
                            <span
                                :class="on ? 'translate-x-5' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            ></span>
                        </button>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
